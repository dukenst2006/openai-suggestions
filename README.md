# OpenAI powered fixes for wordpress Sage/Acorn errors

Use the OpenAI API together with Laravel Ignition's Suggestions to show AI-powered fixes for errors in your Sage/Acorn application.

## Get started

First install OpenAI Suggestions via [Composer](https://getcomposer.org/). You'll probably want to install it as dev dependency:

```bash
composer require --dev dukens/openai-suggestions
```

Next, publish the configuration file:

```bash
wp acorn vendor:publish --provider="Dukens\OpenaiExceptions\OpenAiSolutionServiceProvider" --tag="config"
```

This will create a `config/openai-suggestions.php` configuration file in your project, which you can modify to your needs. All options are documented in the file, but the only one you need is your OpenAI API key, which you'll specify in your `.env` file:

```
OPENAI_API_KEY={YOUR KEY HERE}
```
