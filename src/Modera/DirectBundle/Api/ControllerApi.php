<?php

namespace Modera\DirectBundle\Api;

use Modera\DirectBundle\Attribute\Anonymous;
use Modera\DirectBundle\Attribute\Form;
use Modera\DirectBundle\Attribute\Remote;
use Modera\DirectBundle\Attribute\Secure;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2015 Modera Foundation
 */
class ControllerApi
{
    /**
     * Store the controller reflection object.
     */
    protected \ReflectionClass $reflection;

    protected string $remoteAttribute;

    protected string $formAttribute;

    protected string $safeAttribute;

    protected string $unsafeAttribute;

    /**
     * @var ?array<int, array{'name': string, 'len': int, 'formHandler': bool}>
     */
    protected ?array $api;

    public function __construct(
        protected readonly ContainerInterface $container,
        string $controller,
    ) {
        /** @var class-string $reflectionClass */
        $reflectionClass = $controller;
        $this->reflection = new \ReflectionClass($reflectionClass);

        /** @var string $remoteAttribute */
        $remoteAttribute = $container->getParameter('direct.api.remote_attribute');

        /** @var string $formAttribute */
        $formAttribute = $container->getParameter('direct.api.form_attribute');

        /** @var string $safeAttribute */
        $safeAttribute = $container->getParameter('direct.api.safe_attribute');

        /** @var string $unsafeAttribute */
        $unsafeAttribute = $container->getParameter('direct.api.unsafe_attribute');

        $this->remoteAttribute = $remoteAttribute;
        $this->formAttribute = $formAttribute;
        $this->safeAttribute = $safeAttribute;
        $this->unsafeAttribute = $unsafeAttribute;
        $this->api = $this->createApi();
    }

    /**
     * Check if the controller has any method exposed.
     *
     * @return bool true if it has exposed, otherwise return false
     */
    public function isExposed(): bool
    {
        return null !== $this->api;
    }

    /**
     * Return the api.
     *
     * @return ?array<int, array{'name': string, 'len': int, 'formHandler': bool}>
     */
    public function getApi(): ?array
    {
        return $this->api;
    }

    /**
     * Return the name of exposed direct Action.
     */
    public function getActionName(): string
    {
        return \str_replace('Controller', '', $this->reflection->getShortName());
    }

    /**
     * Check the method access type.
     */
    public function getMethodAccess(string $method): string
    {
        $reflection = $this->reflection->getMethod($method);

        if ($this->hasMarker($reflection, Secure::class, $this->safeAttribute)) {
            return 'secure';
        }
        if ($this->hasMarker($reflection, Anonymous::class, $this->unsafeAttribute)) {
            return 'anonymous';
        }

        // default access type is none
        return 'n';
    }

    /**
     * Try to create the controller api.
     *
     * @return ?array<int, array{'name': string, 'len': int, 'formHandler': bool}>
     */
    protected function createApi(): ?array
    {
        $api = [];

        // get public methods from controller
        $methods = $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $mApi = $this->getMethodApi($method);

            if ($mApi) {
                $api[] = $mApi;
            }
        }

        return \count($api) ? $api : null;
    }

    /**
     * Return the api of method.
     *
     * @return ?array{'name': string, 'len': int, 'formHandler': bool}
     */
    private function getMethodApi(\ReflectionMethod $method): ?array
    {
        if (!$this->hasMarker($method, Remote::class, $this->remoteAttribute)) {
            return null;
        }

        return [
            'name' => \str_replace('Action', '', $method->getName()),
            'len' => $method->getNumberOfParameters(),
            'formHandler' => $this->hasMarker($method, Form::class, $this->formAttribute),
        ];
    }

    /**
     * A method is marked by the PHP attribute or, for backward compatibility, by the docblock tag.
     *
     * @param class-string $attributeClass
     */
    private function hasMarker(\ReflectionMethod $method, string $attributeClass, string $docTag): bool
    {
        if ([] !== $method->getAttributes($attributeClass)) {
            return true;
        }

        $doc = $method->getDocComment();

        return \is_string($doc) && '' !== $doc && 1 === \preg_match('/'.$docTag.'/i', $doc);
    }
}
