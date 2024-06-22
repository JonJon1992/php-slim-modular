<?php

namespace Core\Utils;


use Core\Contracts\IValidate;
use Exception;
use Slim\Psr7\UploadedFile;
use Throwable;

class FileValidate
{

    const TYPE_FILE_ISVALID = "The file type \"{{name}}\" is not valid. ";
    const MESSAGE_REQUIRED = "\"{{name}}\" is required.";

    const MESSAGE_SIZE_BETWEEN = "The field size \"{{name}}\" must be between \"{{min}}\" and \"{{max}}\"";


    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $label;
    /**
     * @var bool
     */
    private $required = true;
    /**
     * @var ?int
     */
    private $min = null;
    /**
     * @var ?int
     */
    private $max = null;
    /**
     * @var ?string[]
     */
    private $media_types_supported;

    private $errors;

    public function __construct(private IValidate $validator)
    {

    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return FileValidate
     */
    public function setName(string $name): FileValidate
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return FileValidate
     */
    public function setLabel(string $label): FileValidate
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return FileValidate
     */
    public function setRequired(bool $required): FileValidate
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMin(): ?int
    {
        return $this->min;
    }

    /**
     * @param int|null $min
     * @return FileValidate
     */
    public function setMin(?int $min): FileValidate
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMax(): ?int
    {
        return $this->max;
    }

    /**
     * @param int|null $max
     * @return FileValidate
     */
    public function setMax(?int $max): FileValidate
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getMediaTypesSupported(): ?array
    {
        return $this->media_types_supported;
    }

    /**
     * @param string[]|null $media_types_supported
     * @return FileValidate
     */
    public function setMediaTypesSupported(?array $media_types_supported): FileValidate
    {
        $this->media_types_supported = $media_types_supported;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     * @return FileValidate
     */
    public function setErrors($errors): FileValidate
    {
        $this->errors = $errors;
        return $this;
    }


    /**
     * @param IValidate $validator
     * @param string $name
     * @param string $label
     * @return FileValidate
     */
    public static function create(IValidate $validator, string $name, string $label): FileValidate
    {
        $fileValidate = new FileValidate($validator);
        return $fileValidate->setName($name)->setLabel($label);
    }

    /**
     * @param UploadedFile|null $file
     * @return void
     * @throws Throwable
     */
    public function validate(?UploadedFile $file): void
    {
        if ($this->required) {
            $this->validateByValue($file, $this->validator::notEmpty()
                ->setTemplate(self::MESSAGE_REQUIRED)
                ->setName($this->label));
        }
        if ($file) {
            if (!empty($this->getMediaTypesSupported())) {
                $this->validateByValue($file->getFilePath(), $this->validator::oneOf(...array_map(
                    fn($item) => $this->validator::mimetype($item),
                    $this->getMediaTypesSupported()
                ))->setTemplate(self::TYPE_FILE_ISVALID)
                    ->setName($this->label));
            }

            if ($this->getMin() || $this->getMax()) {
                $this->validateByValue(
                    $file->getFilePath(),
                    $this->validator::size($this->getMin(), $this->getMax())
                        ->setTemplate(self::MESSAGE_SIZE_BETWEEN)
                        ->setName($this->label)
                );
            }
        }
        if (!empty($this->errors)) {
            throw  new Exception(json_encode($this->errors), 412);
        }
    }


    /**
     * @param $value
     * @param $rules
     * @return void
     * @throws Throwable
     */
    private function validateByValue($value, $rules): void
    {
        try {
            $this->validator::value($value, ['rules' => $rules], $this->getName());
        } catch (Throwable $e) {
            $this->process($e);
        }

    }

    /**
     * @param Throwable $throwable
     * @return void
     * @throws Throwable
     */
    private function process(Throwable $throwable): void
    {
        if ($throwable->getCode() != 412) {
            throw  $throwable;
        }
        $error = Arr::get(json_decode($throwable->getMessage(), true), $this->getName(), []);
        $this->errors[$this->getName()] = Arr::merge(Arr::get($this->errors, $this->getName(), []), $error);
    }

}