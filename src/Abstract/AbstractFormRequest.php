<?php

declare(strict_types=1);

namespace Core\Abstract;

use Core\Contracts\IValidate;
use Core\Exceptions\ValidatorException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Classe abstrata base para Form Requests
 */
abstract class AbstractFormRequest
{
    protected IValidate $validator;
    protected array $data = [];
    protected array $errors = [];

    public function __construct(IValidate $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validar request
     */
    public function validate(ServerRequestInterface $request): bool
    {
        $this->data = $this->extractData($request);
        
        $rules = $this->rules();
        $messages = $this->messages();
        
        $result = $this->validator->validate($this->data, $rules, $messages);
        
        if (!$result) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return true;
    }

    /**
     * Obter dados validados
     */
    public function validated(): array
    {
        return $this->data;
    }

    /**
     * Obter erros de validação
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Verificar se há erros
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Lançar exceção se houver erros
     */
    public function validateOrFail(ServerRequestInterface $request): array
    {
        if (!$this->validate($request)) {
            throw new ValidatorException('Validation failed', $this->errors);
        }

        return $this->validated();
    }

    /**
     * Extrair dados do request
     */
    protected function extractData(ServerRequestInterface $request): array
    {
        $parsedBody = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams();
        $attributes = $request->getAttributes();

        return array_merge($parsedBody, $queryParams, $attributes);
    }

    /**
     * Regras de validação
     */
    abstract protected function rules(): array;

    /**
     * Mensagens de erro customizadas
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Autorizar request (opcional)
     */
    protected function authorize(): bool
    {
        return true;
    }
} 