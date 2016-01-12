<?php
namespace AppBundle\Security;

use AppBundle\Exception\InvalidJWTException;
use AppBundle\Service\JWTCoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 * (mangled by Matt)
 */
final class JWTAuthenticator extends AbstractGuardAuthenticator
{
    private $jwtCoder;
    public function __construct(JWTCoder $jwtCoder)
    {
        $this->jwtCoder = $jwtCoder;
    }
    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        if (!$request->headers->has('Authorization')) {
            throw new CustomUserMessageAuthenticationException('Missing Authorization Header');
        }
        $headerParts = explode(' ', $request->headers->get('Authorization'));
        if (!(count($headerParts) === 2 && $headerParts[0] === 'Bearer')) {
            throw new CustomUserMessageAuthenticationException('Malformed Authorization Header');
        }

        return $headerParts[1];
    }
    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $payload = $this->jwtCoder->decode($credentials);
        } catch (InvalidJWTException $e) {
            throw new CustomUserMessageAuthenticationException($e->getMessage());
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('Malformed JWT');
        }
        if (!isset($payload['username'])) {
            throw new CustomUserMessageAuthenticationException('Invalid JWT');
        }

        return $userProvider->loadUserByUsername($payload['username']);
    }
    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * NOTE: I chose to throw an HTTP Exception here to let the response be rendered elsewhere -
     *       separation of concerns and all... You could always return a JsonResponse here.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = 'Invalid Credentials';
        if ($exception instanceof CustomUserMessageAuthenticationException) {
            $message = $exception->getMessageKey();
        }
        throw new HttpException(401, $message);
    }
    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        // noop
    }
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // noop
    }
    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
