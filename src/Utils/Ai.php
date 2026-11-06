<?php

declare(strict_types=1);

namespace Core\Utils;

use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use LLPhant\{GeminiOpenAIConfig, OpenAIConfig};
use LLPhant\AnthropicConfig;
use LLPhant\Chat\{AnthropicChat, ChatInterface, GeminiAIChat, LmStudioChat, MistralAIChat, OllamaChat, OpenAIChat};
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\Embeddings\EmbeddingGenerator\LmStudio\LmStudioEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\Mistral\MistralEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\{OpenAI3LargeEmbeddingGenerator,
    OpenAI3SmallEmbeddingGenerator,
    OpenAIADA002EmbeddingGenerator
};
use LLPhant\Embeddings\EmbeddingGenerator\VoyageAI\{Voyage3EmbeddingGenerator,
    Voyage3LargeEmbeddingGenerator,
    Voyage3LiteEmbeddingGenerator,
    VoyageCode2EmbeddingGenerator,
    VoyageCode3EmbeddingGenerator,
    VoyageFinance2EmbeddingGenerator,
    VoyageLaw2EmbeddingGenerator
};
use RuntimeException;

final class Ai
{
    public const ANTHROPIC_MAX_TOKENS = 16000;

    private const INHERITED_BY_EMBEDDINGS = ['provider', 'api_key', 'url', 'timeout'];

    private const OPENAI_EMBEDDINGS = [
        'text-embedding-3-small' => OpenAI3SmallEmbeddingGenerator::class,
        'text-embedding-3-large' => OpenAI3LargeEmbeddingGenerator::class,
        'text-embedding-ada-002' => OpenAIADA002EmbeddingGenerator::class,
    ];

    public static function resolveAlias(array $config, ?string $module = null): string
    {
        if ($module !== null && is_array($config[$module] ?? null)) {
            return $module;
        }

        $default = $config['default'] ?? null;
        if (is_string($default) && $default !== '') {
            return $default;
        }

        foreach ($config as $alias => $settings) {
            if ($alias !== 'default' && is_array($settings)) {
                return (string)$alias;
            }
        }
        throw new RuntimeException('Nenhum provedor de IA configurado (config/ai.php).');
    }

    public static function chat(array $settings): ChatInterface
    {
        self::assertInstalled();

        $provider = self::provider($settings);
        $chat = match ($provider) {
            'openai' => new OpenAIChat(self::openAiConfig($settings)),
            'anthropic' => new AnthropicChat(self::anthropicConfig($settings)),
            'gemini' => new GeminiAIChat(self::geminiConfig($settings)),
            default => throw new InvalidArgumentException(
                "IA: provedor '$provider' desconhecido (use openai, anthropic, mistral, gemini, ollama ou lmstudio)."
            ),
        };

        $system = self::string($settings, 'system');
        if ($system !== null) {
            $chat->setSystemMessage($system);
        }

        return $chat;
    }

    public static function embeddings(array $settings): EmbeddingGeneratorInterface
    {
        self::assertInstalled();

        $provider = self::provider($settings);
        $model = self::string($settings, 'model');

        return match ($provider) {
            'openai' => new (self::embeddingClass(self::OPENAI_EMBEDDINGS, $model ?? 'text-embedding-3-small', $provider))(
                self::openAiConfig(self::withoutModel($settings))
            ),
            default => throw new InvalidArgumentException(
                "IA: o provedor '$provider' não gera embeddings."
            ),
        };
    }

    public static function embeddingSettings(array $settings): ?array
    {
        $embeddings = $settings['embeddings'] ?? null;
        if (!is_array($embeddings)) {
            return null;
        }

        if (isset($embeddings['provider']) && $embeddings['provider'] !== ($settings['provider'] ?? null)) {
            return $embeddings;
        }

        return array_merge(Arr::only($settings, self::INHERITED_BY_EMBEDDINGS), $embeddings);
    }

    private static function openAiConfig(array $settings): OpenAIConfig
    {
        $config = new OpenAIConfig(
            apiKey: self::string($settings, 'api_key'),
            url: self::string($settings, 'url'),
            model: self::string($settings, 'model'),
            modelOptions: self::options($settings),
        );
        $config->timeout = self::timeout($settings);
        return $config;
    }


    private static function geminiConfig(array $settings): GeminiOpenAIConfig
    {
        $config = new GeminiOpenAIConfig(...self::filled([
            'apiKey' => self::string($settings, 'api_key'),
            'url' => self::string($settings, 'url'),
        ]));
        $config->model = self::required($settings, 'model', 'gemini');
        $config->modelOptions = self::options($settings);
        $config->timeout = self::timeout($settings);

        return $config;
    }

    private static function anthropicConfig(array $settings): AnthropicConfig
    {
        if (self::string($settings, 'url') !== null) {
            throw new InvalidArgumentException('IA: o AnthropicChat do LLPhant não aceita url (o endpoint é fixo).');
        }

        $timeout = self::timeout($settings);
        return new AnthropicConfig(...self::filled([
            'model' => self::string($settings, 'model'),
            'maxTokens' => (int)($settings['max_tokens'] ?? self::ANTHROPIC_MAX_TOKENS),
            'modelOptions' => self::options($settings),
            'apiKey' => self::string($settings, 'api_key'),
            'client' => $timeout !== null
                ? new GuzzleClient(['timeout' => $timeout, 'connect_timeout' => $timeout])
                : null,
        ]));
    }


    /**
     * @param array<string, class-string<EmbeddingGeneratorInterface>> $classes
     * @return class-string<EmbeddingGeneratorInterface>
     */
    private static function embeddingClass(array $classes, string $model, string $provider): string
    {
        return $classes[$model] ?? throw new InvalidArgumentException(
            "IA: modelo de embeddings '$model' não suportado para '$provider' (use "
            . implode(', ', array_keys($classes)) . ').'
        );
    }

    private static function provider(array $settings): string
    {
        $provider = strtolower(trim((string)($settings['provider'] ?? '')));
        if ($provider === '') {
            throw new InvalidArgumentException("IA: informe a chave 'provider' na config.");
        }

        return $provider;
    }

    private static function required(array $settings, string $key, string $provider): string
    {
        return self::string($settings, $key)
            ?? throw new InvalidArgumentException("IA: o provedor '$provider' exige a chave '$key'.");
    }

    private static function string(array $settings, string $key): ?string
    {
        $value = $settings[$key] ?? null;

        return is_scalar($value) && trim((string)$value) !== '' ? (string)$value : null;
    }

    private static function timeout(array $settings): ?float
    {
        $value = $settings['timeout'] ?? null;

        return is_numeric($value) ? (float)$value : null;
    }

    private static function options(array $settings): array
    {
        return is_array($settings['options'] ?? null) ? $settings['options'] : [];
    }

    private static function withoutModel(array $settings): array
    {
        unset($settings['model']);

        return $settings;
    }

    private static function filled(array $arguments): array
    {
        return array_filter($arguments, fn($value) => $value !== null);
    }

    private static function assertInstalled(): void
    {
        if (!interface_exists(ChatInterface::class)) {
            throw new RuntimeException('IA: instale o LLPhant com "composer require theodo-group/llphant".');
        }
    }
}
