<?php

namespace Modera\SecurityBundle\Tests\Functional\Validator\Constraints;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Validator\Constraints\Username;
use Modera\SecurityBundle\Validator\Constraints\UsernameValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

class UsernameValidatorTest extends FunctionalTestCase
{
    public function testValidate(): void
    {
        $context = $this->createContext();
        $validator = new UsernameValidator();
        $validator->initialize($context);

        $validator->validate('john.doe', new Username());
        $this->assertEquals(0, \count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new UsernameValidator();
        $validator->initialize($context);

        $validator->validate('<john.doe>', new Username());
        $this->assertEquals(1, \count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new UsernameValidator();
        $validator->initialize($context);

        $validator->validate('john@doe', new Username());
        $this->assertEquals(0, \count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new UsernameValidator();
        $validator->initialize($context);

        $validator->validate('<john@doe>', new Username());
        $this->assertEquals(1, \count($context->getViolations()));
    }

    private function createContext(): ExecutionContext
    {
        return new ExecutionContext(
            self::getContainer()->get('validator'),
            '',
            self::getContainer()->get('translator'),
        );
    }
}
