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
                return new SmtpMailer($settings);
            };
        }
    }
}
