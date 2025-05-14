<?php

namespace Modera\ServerCrudBundle\Tests\Functional\Validation;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\ServerCrudBundle\Validation\DefaultEntityValidator;
use Modera\ServerCrudBundle\Validation\ValidationResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DummyEntityToValidate
{
    #[Assert\NotBlank]
    public ?int $id = null;
}

class DummyEntityToValidationWithMethod
{
    #[Assert\NotBlank]
    public ?int $id = null;

    public ValidationResult $givenValidationResult;
    public ContainerInterface $givenContainer;

    public function validateIt(ValidationResult $validationResult, ContainerInterface $container): void
    {
        $this->givenValidationResult = $validationResult;
        $this->givenContainer = $container;

        $validationResult->addGeneralError('an error');
    }
}

class DefaultEntityValidatorTest extends FunctionalTestCase
{
    private DefaultEntityValidator $validator;

    // override
    public function doSetUp(): void
    {
        $this->validator = self::getContainer()->get(DefaultEntityValidator::class);
    }

    public function testIfServiceExists(): void
    {
        $this->assertInstanceOf(DefaultEntityValidator::class, $this->validator);
    }

    public function testValidateBySymfonyServices(): void
    {
        $entity = new DummyEntityToValidate();

        $config = [
            'entity_validation_method' => 'validateIt',
            'ignore_standard_validator' => false,
        ];

        $result = $this->validator->validate($entity, $config);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->hasErrors());

        $fieldErrors = $result->getFieldErrors('id');

        $this->assertTrue(\is_array($fieldErrors));
        $this->assertEquals(1, \count($fieldErrors));
        $this->assertEquals('This value should not be blank.', $fieldErrors[0]);
    }

    public function testValidateWithEntityMethodOnly(): void
    {
        $entity = new DummyEntityToValidationWithMethod();

        $config = [
            'entity_validation_method' => 'validateIt',
            'ignore_standard_validator' => true,
        ];

        $result = $this->validator->validate($entity, $config);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->hasErrors());
        $this->assertTrue(\in_array('an error', $result->getGeneralErrors()));
        $this->assertInstanceOf(ValidationResult::class, $entity->givenValidationResult);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $entity->givenContainer);
    }

    public function testValidateBoth(): void
    {
        $entity = new DummyEntityToValidationWithMethod();

        $config = [
            'entity_validation_method' => 'validateIt',
            'ignore_standard_validator' => false,
        ];

        $result = $this->validator->validate($entity, $config);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->hasErrors());
        $this->assertTrue(\in_array('an error', $result->getGeneralErrors()));
        $this->assertInstanceOf(ValidationResult::class, $entity->givenValidationResult);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $entity->givenContainer);

        $this->assertEquals(1, \count($result->getFieldErrors('id')));
    }
}
