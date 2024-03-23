<?php

namespace Core\Contracts;

use Awurth\Validator\Validator;
use finfo;
use Respect\Validation\Rules\Key;
use Respect\Validation\Validatable;

/**
 * @method static Validator age(int $minAge = null, int $maxAge = null)
 * @method static Validator allOf(Validatable ...$rule)
 * @method static Validator alnum(string $additionalChars = null)
 * @method static Validator alpha(string $additionalChars = null)
 * @method static Validator alwaysInvalid()
 * @method static Validator alwaysValid()
 * @method static Validator arrayVal()
 * @method static Validator arrayType()
 * @method static Validator attribute(string $reference, Validatable $validator = null, bool $mandatory = true)
 * @method static Validator bank(string $countryCode)
 * @method static Validator bankAccount(string $countryCode)
 * @method static Validator base()
 * @method static Validator between($min = null, $max = null, bool $inclusive = true)
 * @method static Validator bic(string $countryCode)
 * @method static Validator boolType()
 * @method static Validator boolVal()
 * @method static Validator bsn()
 * @method static Validator call()
 * @method static Validator callableType()
 * @method static Validator callback(callable $callback)
 * @method static Validator charset($charset)
 * @method static Validator cnh()
 * @method static Validator cnpj()
 * @method static Validator consonant(string $additionalChars = null)
 * @method static Validator contains($containsValue, bool $identical = false)
 * @method static Validator countable()
 * @method static Validator countryCode()
 * @method static Validator currencyCode()
 * @method static Validator cpf()
 * @method static Validator creditCard(string $brand = null)
 * @method static Validator date(string $format = null)
 * @method static Validator digit(string $additionalChars = null)
 * @method static Validator directory()
 * @method static Validator domain(bool $tldCheck = true)
 * @method static Validator each(Validatable $itemValidator = null, Validatable $keyValidator = null)
 * @method static Validator email()
 * @method static Validator endsWith($endValue, bool $identical = false)
 * @method static Validator equals($compareTo)
 * @method static Validator even()
 * @method static Validator executable()
 * @method static Validator exists()
 * @method static Validator extension(string $extension)
 * @method static Validator factor(int $dividend)
 * @method static Validator falseVal()
 * @method static Validator fibonacci()
 * @method static Validator file()
 * @method static Validator filterVar(int $filter, $options = null)
 * @method static Validator finite()
 * @method static Validator floatVal()
 * @method static Validator floatType()
 * @method static Validator graph(string $additionalChars = null)
 * @method static Validator hexRgbColor()
 * @method static Validator identical($value)
 * @method static Validator identityCard(string $countryCode)
 * @method static Validator image(finfo $fileInfo = null)
 * @method static Validator imei()
 * @method static Validator in($haystack, bool $compareIdentical = false)
 * @method static Validator infinite()
 * @method static Validator instance(string $instanceName)
 * @method static Validator intVal()
 * @method static Validator intType()
 * @method static Validator ip($ipOptions = null)
 * @method static Validator iterableType()
 * @method static Validator json()
 * @method static Validator key(string $reference, Validatable $referenceValidator = null, bool $mandatory = true)
 * @method static Validator keyNested(string $reference, Validatable $referenceValidator = null, bool $mandatory = true)
 * @method static Validator keySet(Key ...$rule)
 * @method static Validator keyValue(string $comparedKey, string $ruleName, string $baseKey)
 * @method static Validator languageCode(string $set)
 * @method static Validator leapDate(string $format)
 * @method static Validator leapYear()
 * @method static Validator length(int $min = null, int $max = null, bool $inclusive = true)
 * @method static Validator lowercase()
 * @method static Validator macAddress()
 * @method static Validator max($maxValue, bool $inclusive = true)
 * @method static Validator mimetype(string $mimetype)
 * @method static Validator min($minValue, bool $inclusive = true)
 * @method static Validator minimumAge(int $age)
 * @method static Validator multiple(int $multipleOf)
 * @method static Validator negative()
 * @method static Validator no($useLocale = false)
 * @method static Validator noneOf(Validatable ...$rule)
 * @method static Validator not(Validatable $rule)
 * @method static Validator notBlank()
 * @method static Validator notEmpty()
 * @method static Validator notOptional()
 * @method static Validator noWhitespace()
 * @method static Validator nullType()
 * @method static Validator numeric()
 * @method static Validator objectType()
 * @method static Validator odd()
 * @method static Validator oneOf(Validatable ...$rule)
 * @method static Validator optional(Validatable $rule)
 * @method static Validator perfectSquare()
 * @method static Validator pesel()
 * @method static Validator phone()
 * @method static Validator phpLabel()
 * @method static Validator positive()
 * @method static Validator postalCode(string $countryCode)
 * @method static Validator primeNumber()
 * @method static Validator prnt(string $additionalChars = null)
 * @method static Validator punct(string $additionalChars = null)
 * @method static Validator readable()
 * @method static Validator regex(string $regex)
 * @method static Validator resourceType()
 * @method static Validator roman()
 * @method static Validator scalarVal()
 * @method static Validator sf(string $name, array $params = null)
 * @method static Validator size(string $minSize = null, string $maxSize = null)
 * @method static Validator slug()
 * @method static Validator space(string $additionalChars = null)
 * @method static Validator startsWith($startValue, bool $identical = false)
 * @method static Validator stringType()
 * @method static Validator subdivisionCode(string $countryCode)
 * @method static Validator symbolicLink()
 * @method static Validator tld()
 * @method static Validator trueVal()
 * @method static Validator type(string $type)
 * @method static Validator uploaded()
 * @method static Validator uppercase()
 * @method static Validator url()
 * @method static Validator version()
 * @method static Validator videoUrl(string $service = null)
 * @method static Validator vowel()
 * @method static Validator when(Validatable $if, Validatable $then, Validatable $when = null)
 * @method static Validator writable()
 * @method static Validator xdigit(string $additionalChars = null)
 * @method static Validator yes($useLocale = false)
 * @method static Validator zend($validator, array $params = null)
 */
interface IValidate
{
    /**
     * @param $value
     * @param array $rules
     * @param string $key
     * @param string|null $group
     * @param array $messages
     */
    public static function value($value, array $rules, string $key, string $group = null, array $messages = []);

    /**
     * @param array $data
     * @param array $values
     * @param string|null $group
     * @param array $messages
     * @param $default
     */
    public static function request(array $data, array $values, string $group = null, array $messages = [], $default = null);

}