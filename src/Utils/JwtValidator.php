<?php

declare(strict_types=1);

namespace Core\Utils;

use Core\Contracts\IJwtValidator;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

class JwtValidator implements IJwtValidator
{
    private Parser $parser;
    private Validator $validator;
    private string $secret;

    public function __construct(?string $secret = null)
    {
        $this->parser = new Parser(new JoseEncoder());
        $this->validator = new Validator();
        $this->secret = $secret ?? env('JWT_SECRET');
    }

    /**
     * @return array{id: string, email: string, role: string, tid: string}|null
     */
    public function validateAndExtract(string $bearerToken): ?array
    {
        if (empty($this->secret)) {
            return null;
        }

        try {
            $token = $this->parser->parse($bearerToken);
            $key = InMemory::plainText($this->secret);

            $constraints = [
                new SignedWith(new Sha256(), $key),
                new LooseValidAt(SystemClock::fromUTC()),
            ];

            foreach ($constraints as $constraint) {
                if (!$this->validator->validate($token, $constraint)) {
                    return null;
                }
            }

            if (!$token instanceof UnencryptedToken) {
                return null;
            }

            /** @var UnencryptedToken $token */
            $claims = $token->claims();
            $id = (string) ($claims->get('sub') ?? $claims->get('id') ?? '');

            if ($id === '') {
                return null;
            }

            return [
                'id' => $id,
                'email' => (string) ($claims->get('email') ?? ''),
                'role' => (string) ($claims->get('role') ?? ''),
                'tid' => (string) ($claims->get('tid') ?? $claims->get('tenant_id') ?? ''),
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
