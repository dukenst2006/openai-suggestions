<?php

namespace Dukens\OpenaiSuggestions;

use Illuminate\Support\Facades\Cache;
use OpenAI;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\Ignition\Contracts\Solution;
use Throwable;

class OpenAiSolution implements Solution
{
    private string $prompt;

    public function __construct(
        private readonly Throwable $throwable,
    ) {
        $this->prompt = $this->preparePrompt();
    }

    /**
     * The Solution title. This will be shown on the error page
     */
    public function getSolutionTitle(): string
    {
        return 'OpenAI suggestion';
    }

    /**
     * Get the Solution text. Use caching both because OpenAi is slow,
     * and because we want to save cost
     */
    public function getSolutionDescription(): string
    {
        if (config('openai-suggestions.cache') <= 0) {
            return $this->sendPrompt();
        }
        return Cache::remember(
            'open-ai-solution-' . md5($this->prompt),
            config('openai-suggestions.cache'),
            fn () => $this->sendPrompt(),
        );
    }
    public function getDocumentationLinks(): array
    {
        return [];
    }

    /**
     * Get the prompt that we'll send to OpenAi
     */
    private function preparePrompt(): string
    {
        $finalApplicationFrame = $this->finalApplicationFrame($this->throwable);

        return (string)view('openai-suggestions::prompt', [
            'message' => $this->throwable->getMessage(),
            'line' => $finalApplicationFrame->lineNumber,
            'file' => $finalApplicationFrame->file,
            'snippet' => collect($finalApplicationFrame->getSnippet(10))
                ->map(fn ($line, $number) => $number . ' ' . $line)
                ->join(PHP_EOL),
        ]);
    }

    /**
     * If possible, get the final application frame before the error was thrown.
     */
    private function finalApplicationFrame(Throwable $throwable): Frame
    {
        $backtrace = Backtrace::createForThrowable($throwable)->applicationPath(base_path());
        $frames = $backtrace->frames();
        return $frames[$backtrace->firstApplicationFrameIndex() ?? 0];
    }

    /**
     * Actually send the prompt o OpenAI and return the response..
     */
    private function sendPrompt(): string
    {
		$yourApiKey = config('openai-suggestions.api_key');
        $client = OpenAI::client($yourApiKey);

        return $client->completions()->create([
            'model' => config('openai-suggestions.model'),
            'max_tokens' => config('openai-suggestions.max_tokens'),
            'temperature' => config('openai-suggestions.temperature'),
            'prompt' => $this->prompt,
        ])->choices[0]->text;

    }
}
