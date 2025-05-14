<?php

namespace Modera\FileRepositoryBundle\ThumbnailsGenerator;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @internal
 *
 * Marker class, used in Interceptor class
 *
 * @copyright 2016 Modera Foundation
 */
class AlternativeUploadedFile extends UploadedFile
{
    use AlternativeFileTrait;

    private ?int $size = null;

    public function getSize(): int|false
    {
        return $this->size ?: $this->getSize();
    }

    /**
     * @internal
     */
    public function setClientSize(?int $size = null): void
    {
        $this->size = $size;
    }

    /**
     * We can afford this behaviour because file's validity is determined by original file and its alternative
     * should omit any validation (originally it checks if file is uploaded and in case of thumbnails this will
     * fail by definition, because we create them manually).
     */
    public function isValid(): bool
    {
        $original = $this->getOriginalFile();

        if ($original instanceof File) {
            if (\method_exists($original, 'isValid')) {
                return $original->isValid();
            }
        }

        return true;
    }
}
