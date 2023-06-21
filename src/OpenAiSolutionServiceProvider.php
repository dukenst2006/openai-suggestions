<?php

namespace Dukens\OpenaiSuggestions;

use Illuminate\Support\ServiceProvider;
use Spatie\Ignition\Contracts\SolutionProviderRepository;

class OpenAiSolutionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/openai-suggestions.php' => $this->app->configPath('openai-suggestions.php'),
        ], 'config');

        if (config('openai-suggestions.api_key')) {
            $this->app->make(SolutionProviderRepository::class)
                ->registerSolutionProvider(OpenAiSolutionProvider::class);
        }
    }
}
