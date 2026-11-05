<?php

declare(strict_types=1);

namespace Core\Utils;

use Monolog\Formatter\JsonFormatter;

/**
 * Linha de log no formato do framework (timestamp, severity, env, appname, message, extras,
 * trace-id, level), com a normalização do JsonFormatter (exceções, datas, UTF-8).
 *
 * O parâmetro sem tipo mantém a assinatura compatível com Monolog 2 (array) e 3 (LogRecord).
 */
class TraceJsonFormatter extends JsonFormatter
{
    public function format($record): string
    {
        return $this->toJson($this->normalize(Trace::logData($record)), true)
            . ($this->appendNewline ? "\n" : '');
    }
}
