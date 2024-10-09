<?php

namespace Core\Exceptions;

use Core\Utils\Env;
use Core\Utils\Trace;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

class ErrorHandler extends SlimErrorHandler
{
    const HTTP_STATUS_CODES = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @return ResponseInterface
     */
    public function respond(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();

        if ($this->request->getMethod() === 'OPTIONS' && !$this->exception instanceof HttpNotFoundException) {
            return $response->withStatus(200);
        }
        $data = [
            'trace-id' => Trace::uuid(),
            'message' => 'Falha inesperada.',
            'status' => $this->statusCode
        ];

        if (isset($this->exception) && array_key_exists($this->exception->getCode(), self::HTTP_STATUS_CODES)) {
            $data['status'] = $this->statusCode = $this->exception->getCode();
            $data['message'] = $this->exception->getMessage();
        }

        $extras = $this->extras();

        logger()->log(
            $this->statusCode >= 500 ? 'error' : 'info',
            $this->exception ? $this->exception->getMessage() : $data['message'],
            $extras
        );
        if (Env::Local()) {
            $data['extras'] = $extras;
        }


        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($this->statusCode);
    }

    /**
     * @return array
     */
    protected function extras(): array
    {
        $request = [];
        $request['use-id'] = auth()->id();
        $request['status'] = $this->statusCode;
        $request['method'] = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        $request['agent'] = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
        $request['host'] = filter_input(INPUT_SERVER, 'HTTP_HOST');
        $request['uri'] = filter_input(INPUT_SERVER, 'REQUEST_URI');

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $request['allow'] = $this->exception->getAllowedMethods();
        }

        if ($this->exception instanceof Throwable) {
            $request['code'] = $this->exception->getCode();
            $request['class'] = get_class($this->exception);
            $request['file'] = $this->exception->getFile();
            $request['line'] = $this->exception->getLine();
            $request['stack-trace'] = explode("\n", $this->exception->getTraceAsString());
        }

        return $request;

    }
}