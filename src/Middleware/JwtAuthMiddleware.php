<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Contracts\IAuth;
use Core\Exceptions\Exception\AuthenticationException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware de autenticação JWT
 */
class JwtAuthMiddleware implements MiddlewareInterface
{
    private IAuth $auth;
    private Configuration $jwtConfig;
    private string $issuer;
    private string $secret;

    public function __construct(IAuth $auth, string $secret, string $issuer = 'php-slim-modular')
    {
        $this->auth = $auth;
        $this->secret = $secret;
        $this->issuer = $issuer;
        
        $this->jwtConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($secret)
        );
        
        $this->jwtConfig->setValidationConstraints(
            new IssuedBy($issuer),
            new SignedWith(new Sha256(), InMemory::base64Encoded($secret))
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->extractToken($request);
        
        if (!$token) {
            throw new AuthenticationException('No token provided');
        }

        $user = $this->validateToken($token);
        
        if (!$user) {
            throw new AuthenticationException('Invalid token');
        }

        // Adicionar dados do usuário ao request
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('user_id', $user['id'] ?? null);

        // Fazer login no sistema de autenticação
        $this->auth->login("");

        return $handler->handle($request);
    }

    /**
     * Extrair token do request
     */
    private function extractToken(ServerRequestInterface $request): ?string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return null;
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }

    /**
     * Validar token JWT
     */
    private function validateToken(string $token): ?array
    {
        try {
            $parsedToken = $this->jwtConfig->parser()->parse($token);
            
            if (!$this->jwtConfig->validator()->validate($parsedToken, ...$this->jwtConfig->validationConstraints())) {
                return null;
            }

            // Verificar se o token não expirou
            if ($parsedToken->isExpired(new \DateTimeImmutable())) {
                return null;
            }

            // Extrair claims do token
            return [
                'id' => $parsedToken->claims()->get('user_id'),
                'email' => $parsedToken->claims()->get('email'),
                'roles' => $parsedToken->claims()->get('roles', []),
                'permissions' => $parsedToken->claims()->get('permissions', []),
                'jti' => $parsedToken->claims()->get('jti'),
                'iat' => $parsedToken->claims()->get('iat'),
                'exp' => $parsedToken->claims()->get('exp')
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Gerar token JWT
     */
    public function generateToken(array $userData, int $expirationMinutes = 60): string
    {
        $now = new \DateTimeImmutable();
        
        $token = $this->jwtConfig->builder()
            ->issuedBy($this->issuer)
            ->permittedFor($this->issuer)
            ->identifiedBy(uniqid())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$expirationMinutes} minutes"))
            ->withClaim('user_id', $userData['id'])
            ->withClaim('email', $userData['email'])
            ->withClaim('roles', $userData['roles'] ?? [])
            ->withClaim('permissions', $userData['permissions'] ?? [])
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        return $token->toString();
    }

    /**
     * Gerar refresh token
     */
    public function generateRefreshToken(array $userData, int $expirationDays = 30): string
    {
        $now = new \DateTimeImmutable();
        
        $token = $this->jwtConfig->builder()
            ->issuedBy($this->issuer)
            ->permittedFor($this->issuer)
            ->identifiedBy(uniqid())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$expirationDays} days"))
            ->withClaim('user_id', $userData['id'])
            ->withClaim('type', 'refresh')
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());

        return $token->toString();
    }
} 