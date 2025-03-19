<?php

namespace Core\ValueObjects;

use Core\Utils\Validate;
use DateTime;

class Date extends ValueObject
{
    public const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';

    private string $date;

    public function __construct($value, $format = self::DEFAULT_DATE_FORMAT)
    {
        $rules = [
            'value' => [
                'rules' => Validate::date($format),
            ]
        ];
        Validate::request(['value' => $value], $rules);

        $this->date = (string)$value;
    }

    /**
     * @return DateTime
     */
    public function getDateTime(): \DateTime
    {
        return DateTime::createFromFormat(self::DEFAULT_DATE_FORMAT, $this->date);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getDateTime()->format(self::DEFAULT_DATE_FORMAT);
    }
}