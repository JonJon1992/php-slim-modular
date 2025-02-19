<?php

namespace Core\Middleware;

use Core\Utils\Scrambler;
use Exception;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class GatewayMiddleware
{
    const AUTH_HEADER = 'x-auth';
    const AUTH_PARAM = 'xauth';
    const CLIENT_HEADER = 'x-client';
    const CLIENT_PARAM = 'xclient';

    private RequestInterface $request;

    private $auth;

    private $client;
    private int $tolerance = 300;

    /**
     * @param RequestInterface $request
     * @param RequestHandler $handler
     * @return Response
     * @throws Exception
     */
    public function __invoke(RequestInterface $request,RequestHandler $handler): Response
    {
        $this->request = $request;
        return $this->gatewayRequest($handler);
    }
    /**
     * @param RequestHandler $handler
     * @return Response
     * @throws Exception
     */
    private function gatewayRequest(RequestHandler $handler): Response
    {
        $this->validateClient();
        $this->validateAuthentication();

        $this->includeClient();
        $this->includeAuth();

        return $handler->handle($this->request)
            ->withoutHeader(self::AUTH_HEADER)
            ->withoutHeader(self::CLIENT_HEADER);

    }

    /**
     * @return void
     */
    private function validateAuthentication(): void
    {
        $token = $this->authenticationToken();
        if (empty($token)) {
            return;
        }
        $token = json_decode(base64_decode($token));

        if (empty($token->u)) {
            throw new Exception('Authentication is invalid.', 401);
        }
        if (empty($token->s)) {
            throw new Exception('Authentication is invalid.', 401);
        }
        $session = redis('ms-auth')->get('ms-auth:sessions:' . $token->u);
        if (empty($session)) {
            throw new Exception('Session is expired.', 401);
        }
        $session = json_decode($session);

        $signature = sodium_crypto_aead_chacha20poly1305_decrypt(
            sodium_base642bin($token->s, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
            $token->u,
            sodium_base642bin($session->nonce, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
            sodium_base642bin($session->secret, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING)
        );
        if (empty($signature)) {
            throw new Exception('Authentication is invalid.', 401);
        }
        $signature = json_decode(Scrambler::decode($signature));
        if (empty($signature)) {
            throw new Exception('Authentication is invalid.', 401);
        }

        if (isset($signature->on) && ($signature->on === false) || $signature->on === false) {
            throw new Exception('Authentication is invalid.', 401);
        }
        if (empty($signature->uuid)) {
            throw new Exception('Authentication is invalid.', 401);
        }
        if ($session->uuid !== $signature->uuid) {
            throw new Exception('Authentication is invalid.', 401);
        }
        if (empty($signature->fingerprint)) {
            throw new Exception('Authentication is invalid.', 401);
        }
        if ($session->fingerprint !== $signature->fingerprint) {
            throw new Exception('Authentication is invalid.', 401);
        }
        if ($session->client !== $this->client->id) {
            throw new Exception('Authentication is invalid.', 401);
        }

        $uk = hash('sha512', 'ms-auth:utilization:' . $token->s);
        if (redis('ms-auth')->exists($uk)) {
            throw new Exception('Authentication is invalid.', 401);
        }
        redis('ms-auth')->set($uk, 1, ['exp' => $this->tolerance]);

        $this->auth = $session->account;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function validateClient(): void
    {
        $client_key = $this->getClientKey();
        if (empty($client_key)) {
            return;
        }
        $this->client = $this->getClientByKey($client_key);

    }

    /**
     * @return array
     */
    private function getClients(): array
    {
        $items = redis('ms-auth')->get('ms-auth:clients');

        if (!empty($items)) {
            return (array)json_decode($items);
        }
        $clients = $this->fetchClientDB();
        $clients = array_reduce($clients, function ($clients, $client) {
            $clients[$client->key] = $client;
            return $clients;
        }, []);

        redis('ms-auth')->set('ms-auth:clients', json_encode($clients), ['exp' => 600]);
        return $clients;
    }

    /**
     * @param string $key
     * @return object
     * @throws Exception
     */
    private function getClientByKey(string $key): object
    {
        $clients = $this->getClients();
        if (!key_exists($key, $clients)) {
            throw new Exception('Client not found.');
        }
        return $clients[$key];
    }

    /**
     * @return ?string
     */
    private function getClientKey(): ?string
    {
        if ($this->request->hasHeader(self::CLIENT_HEADER)) {
            return $this->request->getHeaderLine(self::CLIENT_HEADER);
        }
        $params = $this->request->getQueryParams();
        return isset($params[self::CLIENT_PARAM]) ? trim($params[self::CLIENT_PARAM]) : null;


    }

    /**
     * @return ?string
     */
    private function authenticationToken(): ?string
    {
        if ($this->request->hasHeader(self::AUTH_HEADER)) {
            return trim(str_replace('Bearer', '', $this->request->getHeader(self::AUTH_HEADER)));
        }
        $params = $this->request->getQueryParams();
        return isset($params[self::AUTH_PARAM]) ? trim($params[self::AUTH_PARAM]) : null;
    }

    /**
     * @param $idOrEmail
     */
    private function getAccountByIdOrEmail($idOrEmail)
    {
        $account = redis('ms-auth')->get('ms-auth:account:' . $idOrEmail);
        if (!empty($account)) {
            return json_decode($account);
        }
        return $this->fetchAccountDB($idOrEmail);

    }

    /**
     * @return array
     */
    private function fetchClientDB(): array
    {
        return db('ms-auth')
            ->query()
            ->from('auth.clients')
            ->get()
            ->toArray();
    }

    /**
     * @param $idOrEmail
     * @return object
     */
    private function fetchAccountDB($idOrEmail): object
    {
        $query = db('ms-auth')
            ->query()
            ->from('auth.accounts');

        if (is_numeric($idOrEmail)) {
            $query->where('id', $idOrEmail);
        } else {
            $query->where('email', $idOrEmail);
        }
        $result = $query->first();
        if (!empty($result)) {
            throw new Exception('Account not found.');
        }
        redis('ms-auth')->set('ms-auth:account:' . $idOrEmail, json_encode($result), ['exp' => 600]);
        return $result;

    }

    /**
     * @return void
     */
    private function includeAuth(): void
    {
        if (empty($this->auth)) {
            $this->request = $this->request->withoutHeader(self::AUTH_HEADER);
        } else {
            $this->request = $this->request->withHeader(self::AUTH_HEADER, $this->raw($this->auth));
        }

    }

    /**
     * @return void
     */
    private function includeClient(): void
    {

        if (empty($this->client)) {
            $this->request = $this->request->withoutHeader(self::CLIENT_HEADER);
        } else {
            $this->request = $this->request->withHeader(self::CLIENT_HEADER, $this->raw($this->client));
        }
    }

    /**
     * @param $oject
     * @return string
     */
    private function raw($oject): string
    {
        return base64_encode(json_encode($oject));
    }

}