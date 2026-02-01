<?php

namespace Core\Middleware;

use Core\Exceptions\Exception\InvalidRequestException;
use Core\Utils\Env;
use Core\Utils\Time;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Server\RequestHandlerInterface as HandlerInterface;
use RuntimeException;
use SodiumException;

class  ApiGatewayMiddleware
{
    private RequestInterface $request;
    const CLIENT_HEADER = 'x-client';
    const CLIENT_PARAM = 'xclient';

    const AUTH_HEADER = 'x-auth';
    const AUTH_PARAM = 'xauth';


    private $module;

    private $auth;

    /**
     * @throws Exception
     */
    public function __invoke(RequestInterface $request, HandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        return $this->authRequest($handler);
    }

    /**
     * @param HandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    private function authRequest(HandlerInterface $handler): ResponseInterface
    {
        $this->verifyModuleSystem();
        $this->verifyToken();


        $this->includeClient();
        $this->includeAuth();

        return $handler->handle($this->request)
            ->withoutHeader(self::AUTH_HEADER)
            ->withoutHeader(self::CLIENT_HEADER);
    }

    /**
     * @return void
     */
    private function verifyModuleSystem(): void
    {
        $module_key = $this->getModuleKey();

        if (empty($module_key)) {
            return;
        }
        $this->module = $this->getModuleByKey($module_key);
    }

    /**
     * @return void
     * @throws SodiumException|InvalidRequestException
     */
    private function verifyToken(): void
    {
        $token = $this->getAuthenticationToken();

        if (empty($token)) {
            return;
        }

        $token = json_decode(base64_decode($token));

        if (empty($token->u)) {
            throw new InvalidRequestException('Requisição inválida.');
        }

        if (empty($token->s)) {
            throw new InvalidRequestException('Requisição inválida');
        }

        $session = json_decode(redis()->get('auth:sessions:' . $token->u));

        if (empty($session)) {
            throw new InvalidRequestException('Requisição inválida');
        }

        $signature = sodium_crypto_aead_chacha20poly1305_decrypt(
            sodium_base642bin($token->s, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
            $token->u,
            sodium_base642bin($session->nonce, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
            sodium_base642bin($session->secret, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
        );

        if (empty($signature)) {
            throw new InvalidRequestException('Requisição inválida');
        }

        $signature = json_decode($this->decode($signature));

        if (!Env::prod()) {
            $current_time = time();
            if (empty($signature->time)) {
                throw new InvalidRequestException('Requisição inválida');
            }

            if ($signature->time < ($current_time - Time::_5M)) {
                throw new InvalidRequestException('Requisição inválida. Por favor, ajuste seu relógio!');
            }

            if ($signature->time > ($current_time + Time::_5M)) {
                throw new InvalidRequestException('Requisição inválida. Por favor, ajuste seu relógio!');
            }

            if ($this->isIpAllowed($session->ip, $this->getIp()) !== true) {
                throw new InvalidRequestException('Requisição inválida');
            }
        }
        if (isset($signature->on) && ($signature->on === false || $signature->on === 'false')) {
            throw new InvalidRequestException('Requisição inválida');
        }

        if (empty($signature->uuid)) {
            throw new InvalidRequestException('Requisição inválida');
        }
        if ($session->uuid != $signature->uuid) {
            throw new InvalidRequestException('Requisição inválida');
        }

        if (empty($signature->fingerprint)) {
            throw new InvalidRequestException('Requisição inválida');
        }
        if ($session->fingerprint != $signature->fingerprint) {
            throw new InvalidRequestException('Requisição inválida');
        }

        if ($session->client != $this->module['id']) {
            throw new InvalidRequestException('Requisição inválida');
        }

        if ($session->agent != $this->getAgent()) {
            throw new InvalidRequestException('Requisição inválida');
        }

        $uk = hash('sha512', 'auth:utilization:' . $token->s);

        if (Env::prod() && redis()->exists($uk)) {
            throw new InvalidRequestException('Requisição inválida');
        }

        redis()->set($uk, 1, Time::_1H);

        $this->auth = $session->usuario;
    }

    private function getModuleKey(): ?string
    {
        if ($this->request->hasHeader(self::CLIENT_HEADER)) {
            return $this->request->getHeaderLine(self::CLIENT_HEADER);
        }

        $params = $this->request->getQueryParams();
        return isset($params[self::CLIENT_PARAM]) ? trim((string)$params[self::CLIENT_PARAM]) : null;
    }

    /**
     * @return string|null
     */
    private function getAuthenticationToken(): ?string
    {
        if ($this->request->hasHeader(self::AUTH_HEADER)) {
            return trim(str_replace('Bearer', '', $this->request->getHeaderLine(self::AUTH_HEADER)));
        }

        $params = $this->request->getQueryParams();

        return isset($params[self::AUTH_PARAM]) ? trim($params[self::AUTH_PARAM]) : null;
    }

    /**
     * @return mixed
     */
    private function getModuleSystems(): mixed
    {
        $cacheKey = 'auth:module_client_keys';
        $redis = redis();

        $items = $redis->get($cacheKey);

        if (!empty($items)) {
            return json_decode($items, true);
        }

        $items = db()
            ->query()
            ->select()
            ->from('module_client_keys')
            ->get()
            ->toArray();


        $items = array_reduce($items, function ($modules, $item) {
            $modules[$item->key] = (array)$item;
            return $modules;
        }, []);

        $redis->set($cacheKey, json_encode($items), Time::_24H);

        return $items;
    }

    /**
     * @param string $key
     * @return mixed
     */
    private function getModuleByKey(string $key): mixed
    {
        $modules = $this->getModuleSystems();
        if (!array_key_exists($key, $modules)) {
            throw new InvalidArgumentException('Autenticação inválida');
        }

        return $modules[$key];
    }

    /**
     * @return void
     */
    private function includeClient(): void
    {
        if (empty($this->module)) {
            $this->request = $this->request->withoutHeader(self::CLIENT_HEADER);
        } else {
            $this->request = $this->request->withHeader(self::CLIENT_HEADER, $this->raw($this->module));
        }
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
     * @param $object
     * @return string
     */
    private function raw($object): string
    {
        return base64_encode(json_encode($object));
    }

    /**
     * @param string $str
     * @return string
     */
    private function decode(string $str): string
    {
        $decodedBase64 = base64_decode(strtr($str, '._-', '+/='));
        if ($decodedBase64 === false) {
            throw new InvalidArgumentException("String de entrada inválida para decodificação.");
        }

        $result = '';
        $chars = str_split($decodedBase64, 2);

        foreach ($chars as $chr) {
            if (strlen($chr) < 2) {
                throw new RuntimeException("String decodificada tem um número ímpar de caracteres.");
            }

            $c1 = ord($chr[0]);
            $c2 = ord($chr[1]);

            $r = ($c2 - 33) - ($c1 - (94 * ((int)($c1 / 94))));
            if ($r < 0) {
                $r += 94;
            }

            $result .= chr($r + 33);
        }

        return base64_decode($result);
    }

    /**
     * @return string
     */
    private function getAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    private function isIpAllowed(string $sessionIp, string $currentIp): bool
    {
        return substr($sessionIp, 0, strrpos($sessionIp, '.')) === substr($currentIp, 0, strrpos($currentIp, '.'));
    }

    /**
     * @param string $default
     * @return string
     */
    private function getIp(string $default = 'UNKNOWN'): string
    {
        $trustedProxies = ['127.0.0.1', '::1'];
        $keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (isset($_SERVER[$key]) && $_SERVER[$key]) {
                if ($key === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $_SERVER[$key]);
                    foreach ($ips as $ip) {
                        $ip = trim($ip);
                        if (!in_array($ip, $trustedProxies)) {
                            return $ip;
                        }
                    }
                }
                return $_SERVER[$key];
            }
        }

        return $default;
    }
}
