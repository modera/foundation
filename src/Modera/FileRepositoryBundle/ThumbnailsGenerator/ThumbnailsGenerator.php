<?php

namespace Modera\FileRepositoryBundle\ThumbnailsGenerator;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 *
 * @copyright 2016 Modera Foundation
 */
class ThumbnailsGenerator
{
    /**
     * @param int|string|null $mode One of the ImageInterface::THUMBNAIL_* constants, when omitted
     *                              then ImageInterface::THUMBNAIL_INSET is used. A string is accepted
     *                              for backward compatibility with old constant values that were strings
     *
     * @return string A path to a temporary file where thumbnail is saved
     *
     * @throws NotImageGivenException
     */
    public function generate(File $image, int $width, int $height, int|string|null $mode = null): string
    {
        $isImage = 'image/' === \substr($image->getMimeType() ?? '', 0, \strlen('image/'));
        if (!$isImage) {
            throw NotImageGivenException::create($image);
        }

        $pathname = \tempnam(\sys_get_temp_dir(), 'thumbnail_').'.'.$image->guessExtension();

        $size = new Box($width, $height);
        if (null === $mode) {
            $mode = ImageInterface::THUMBNAIL_INSET;
        }

        $imagine = new Imagine();
        $img = $imagine->open($image->getPathname());

        if (\function_exists('exif_read_data')) {
            $exif = @\exif_read_data($image->getPathname());
            if (\is_array($exif) && \is_int($exif['Orientation'] ?? null)) {
                $img = self::applyExifOrientation($img, $exif['Orientation']);
            }
        }

        $img->thumbnail($size, $mode)->save($pathname);

        return $pathname;
    }

    private static function applyExifOrientation(ImageInterface $image, int $orientation): ImageInterface
    {
        return match ($orientation) {
            2 => $image->flipHorizontally(),
            3 => $image->rotate(180),
            4 => $image->flipVertically(),
            5 => $image->rotate(90)->flipHorizontally(),
            6 => $image->rotate(90),
            7 => $image->rotate(-90)->flipHorizontally(),
            8 => $image->rotate(-90),
            default => $image,
        };
    }

    /**
     * @param array<mixed> $thumbnailConfig
     */
    public function updateStoredFileAlternativeMeta(StoredFile $alternative, array $thumbnailConfig): void
    {
        $alternative->mergeMeta(['thumbnail' => $thumbnailConfig]);
    }
}
