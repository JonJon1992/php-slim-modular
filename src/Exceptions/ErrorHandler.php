<?php

namespace Core\Exceptions;

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
            'code' => $this->statusCode,
            'message' => 'Falha inesperada.',
            'trace' => Trace::uuid(),
            'debug' => $this->debug()
        ];

        if (isset($this->exception) && array_key_exists($this->exception->getCode(), self::HTTP_STATUS_CODES)) {
            $data['code'] = $this->statusCode = $this->exception->getCode();
            $data['message'] = $this->exception->getMessage();
        }
        logger()->error('ErrorHandler', $data);

        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($this->statusCode);
    }


    /**
     * @return array
     */
    protected function debug(): array
    {
        $debug = [];

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $debug['allow'] = $this->exception->getAllowedMethods();
        }

        if ($this->exception instanceof Throwable) {
            $debug['code'] = $this->exception->getCode();
            $debug['message'] = $this->exception->getMessage();
            $debug['class'] = get_class($this->exception);
            $debug['file'] = $this->exception->getFile();
            $debug['line'] = $this->exception->getLine();
            $debug['trace'] = $this->statusCode == 500 ? explode("\n", $this->exception->getTraceAsString()) : [];

        }
        $debug['request'] = self::metadata();

        return $debug;

    }

    /**
     * @return array
     */
    public static function metadata(): array
    {
        $input = file_get_contents('php://input');

        $request = [
            '$_GET' => $_GET,
            '$_POST' => $_POST,
            '$_INPUT' => $input,
            '$_SERVER' => [
                'APP_ENV' => env('APP_ENV'),
                'APP_NAME' => env('APP_NAME'),
                'APP_SITE' => env('APP_SITE'),
                'REQUEST_METHOD' => filter_input(INPUT_SERVER, 'REQUEST_METHOD'),
                'REQUEST_URI' => filter_input(INPUT_SERVER, 'REQUEST_URI'),
                'QUERY_STRING' => filter_input(INPUT_SERVER, 'QUERY_STRING'),
                'HTTP_HOST' => filter_input(INPUT_SERVER, 'HTTP_HOST'),
                'HTTP_USER_AGENT' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'),
                'HTTP_CONTENT_TYPE' => filter_input(INPUT_SERVER, 'HTTP_CONTENT_TYPE'),
                'CONTENT_TYPE' => filter_input(INPUT_SERVER, 'CONTENT_TYPE')
            ],
        ];

        foreach (array_keys($_SERVER) as $key) {
            if (str_starts_with($key, 'HTTP_X_')) {
                $request['$_SERVER'][$key] = filter_input(INPUT_SERVER, $key);
            }
        }
        return $request;
    }
}