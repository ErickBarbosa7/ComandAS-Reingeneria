<?php
namespace Config\Jwt;

use Config\utils\Utils;

use DateTimeImmutable;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;

class Jwt 
{

    private static $key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9@';

    // Genera un token JWT para la autenticación.
    public static function SignIn($data) 
    {
        $token = (new JwtFacade())->issue(
            new Sha256(),
            InMemory::plainText(base64_encode(Utils::hash(self::$key))),
            static fn (
                Builder $builder,
                DateTimeImmutable $issuedAt,
            ): Builder => $builder
                ->issuedBy('http://localhost')
                ->permittedFor(sha1(Utils::get_ip()))
                ->expiresAt($issuedAt->modify('+69 minutes'))
                ->withClaim('data', $data)
        );
        return $token->toString();
    }

    //Verifica la validez de un token JWT
    public static function Check(String $generated) 
    {
        try {
            $clock = new FrozenClock(new DateTimeImmutable());
            $parser = new Parser(new JoseEncoder()); 
            $config = Configuration::forUnsecuredSigner();
            $constraints = [
                new PermittedFor(sha1(Utils::get_ip())),
                new IssuedBy('http://localhost'),
                new LooseValidAt($clock),
            ];
            return $config->validator()->validate($parser->parse($generated), ...$constraints);
        } catch (\Throwable $th) {
            return false;
        }
    }

    // Obtiene los datos almacenados en un token JWT
    public static function GetData(String $generated) 
    {
        $config = Configuration::forUnsecuredSigner();
        $read = $config->parser()->parse($generated);
        assert($read instanceof Token\Plain);
        return $read->claims()->get('data');
    }
}
?>