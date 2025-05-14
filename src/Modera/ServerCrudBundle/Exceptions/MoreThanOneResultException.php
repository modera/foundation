<?php

namespace Modera\ServerCrudBundle\Exceptions;

/**
 * Exception will be thrown when exactly one entity was expected to be returned from database but in fact several
 * entities have been returned.
 *
 * @copyright 2014 Modera Foundation
 */
class MoreThanOneResultException extends \RuntimeException
{
}
