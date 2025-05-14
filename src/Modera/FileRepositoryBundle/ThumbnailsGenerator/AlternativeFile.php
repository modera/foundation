<?php

namespace Modera\FileRepositoryBundle\ThumbnailsGenerator;

use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 *
 * Marker class, used in Interceptor class
 *
 * @copyright 2016 Modera Foundation
 */
class AlternativeFile extends File
{
    use AlternativeFileTrait;
}
