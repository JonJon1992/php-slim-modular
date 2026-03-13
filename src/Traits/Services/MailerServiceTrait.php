<?php

declare(strict_types=1);

namespace Core\Traits\Services;

use Nette\Mail\SmtpMailer;

trait MailerServiceTrait
{
    protected function registerMailerServices(array &$bindings, $configs): void
    {
        foreach ($configs->get('mailer', []) as $alias => $settings) {
            if ($alias === 'default') {
                continue;
            }

            $bindings["mailer.$alias"] = function () use ($settings) {
                return new SmtpMailer(...$this->normalizeMailerSettings($settings));
            };
        }
    }

    protected function normalizeMailerSettings(array $settings): array
    {
        $normalized = [];
        $keyMap = [
            'host' => 'host',
            'username' => 'username',
            'password' => 'password',
            'port' => 'port',
            'encryption' => 'encryption',
            'secure' => 'encryption',
            'timeout' => 'timeout',
            'persistent' => 'persistent',
            'clientHost' => 'clientHost',
            'client_host' => 'clientHost',
            'streamOptions' => 'streamOptions',
            'stream_options' => 'streamOptions',
        ];

        foreach ($keyMap as $inputKey => $targetKey) {
            if (!array_key_exists($inputKey, $settings)) {
                continue;
            }

            $value = $settings[$inputKey];
            if ($value === null || $value === '') {
                continue;
            }

            $normalized[$targetKey] = $value;
        }

        return $normalized;
    }
}
