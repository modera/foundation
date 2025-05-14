<?php

namespace Modera\SecurityBundle\Tests\Functional\Validator\Constraints;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Validator\Constraints\Email;
use Modera\SecurityBundle\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

class EmailValidatorTest extends FunctionalTestCase
{
    public function testValidate(): void
    {
        $context = $this->createContext();
        $validator = new EmailValidator();
        $validator->initialize($context);

        $validator->validate('user@email.good', new Email());
        $this->assertEquals(0, \count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new EmailValidator();
        $validator->initialize($context);

        $validator->validate('<user@email.bad>', new Email());
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
