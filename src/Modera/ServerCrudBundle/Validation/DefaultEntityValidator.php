<?php

namespace Modera\ServerCrudBundle\Validation;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2013 Modera Foundation
 */
class DefaultEntityValidator implements EntityValidatorInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ContainerInterface $container,
    ) {
    }

    public function validate(object $entity, array $config): ValidationResult
    {
        $validationResult = new ValidationResult();

        /** @var array{
         *      'ignore_standard_validator': bool,
         *      'entity_validation_method': false|string,
         *  } $config
         */
        if (false === $config['ignore_standard_validator']) {
            /** @var ConstraintViolationInterface $violation */
            foreach ($this->validator->validate($entity) as $violation) {
                $validationResult->addFieldError($violation->getPropertyPath(), $violation->getMessageTemplate());
            }
        }

        if (false !== $config['entity_validation_method'] && \in_array($config['entity_validation_method'], \get_class_methods($entity))) {
            $methodName = $config['entity_validation_method'];

            $entity->$methodName($validationResult, $this->container);
        }

        return $validationResult;
    }
}
