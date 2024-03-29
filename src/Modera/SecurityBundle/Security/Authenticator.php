<?php

namespace Modera\SecurityBundle\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Modera\SecurityBundle\Entity\User;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class Authenticator implements AuthenticationFailureHandlerInterface, AuthenticationSuccessHandlerInterface
{
    /**
     * @var DefaultAuthenticationSuccessHandler
     */
    private $successHandler;

    /**
     * @var DefaultAuthenticationFailureHandler
     */
    private $failureHandler;

    /**
     * @param HttpUtils $httpUtils
     * @param HttpKernelInterface $httpKernel
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        HttpUtils $httpUtils,
        HttpKernelInterface $httpKernel,
        LoggerInterface $logger = null
    )
    {
        $this->successHandler = new DefaultAuthenticationSuccessHandler($httpUtils);
        $this->failureHandler = new DefaultAuthenticationFailureHandler($httpKernel, $httpUtils, array(), $logger);
    }

    /**
     * @param array $options An array of options
     */
    public function setOptions(array $options)
    {
        $this->successHandler->setOptions($options);
        $this->failureHandler->setOptions($options);
    }

    /**
     * @param string $providerKey
     */
    public function setProviderKey($providerKey)
    {
        $this->successHandler->setProviderKey($providerKey);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $result = array(
                'success' => false,
                'message' => $exception->getMessage(),
            );

            return new JsonResponse($result);
        }

        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            $result = static::getAuthenticationResponse($token);

            return new JsonResponse($result);
        }

        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    /**
     * @param TokenInterface $token
     *
     * @return array
     */
    public static function getAuthenticationResponse(TokenInterface $token)
    {
        $response = array('success' => false);
        if ($token && $token->getUser() instanceof User) {
            /* @var User $user */
            $user = $token->getUser();
            $response = array(
                'success' => true,
                'profile' => self::userToArray($user),
            );
        }

        return $response;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public static function userToArray(User $user)
    {
        return array(
            'id' => $user->getId(),
            'name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'meta' => $user->getMeta(),
        );
    }
}
