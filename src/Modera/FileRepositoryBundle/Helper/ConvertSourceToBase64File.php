<?php

namespace Modera\FileRepositoryBundle\Helper;

use Modera\FileRepositoryBundle\File\Base64File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;

/**
 * @copyright 2022 Modera Foundation
 *
 * Example:
 * if (\is_array($source)) {
 *     $file = ConvertSourceToBase64File::fromArray($source);
 * } elseif (\strpos($source, 'data:') === 0) {
 *     $file = ConvertSourceToBase64File::fromData($source);
 * } elseif (\filter_var($source, \FILTER_VALIDATE_URL)) {
 *     $file = ConvertSourceToBase64File::fromURL($source);
 * } else {
 *     $file = ConvertSourceToBase64File::fromFile($source);
 * }
 */
final class ConvertSourceToBase64File
{
    /**
     * @param array{
     *     'mimeType'?: string,
     *     'fileContent'?: string,
     *     'fileName'?: string,
     * } $source
     */
    public static function fromArray(array $source): ?Base64File
    {
        $required = ['mimeType', 'fileContent'];
        if (\count(\array_intersect_key(\array_flip($required), $source)) === \count($required)) {
            /** @var array{
             *     'mimeType': string,
             *     'fileContent': string,
             *     'fileName'?: string
             * } $source */
            $base64 = \sprintf('data:%s;base64,%s', $source['mimeType'], $source['fileContent']);

            return new Base64File($base64, $source['fileName'] ?? static::generateFilename($base64));
        }

        return null;
    }

    public static function fromData(string $source): ?Base64File
    {
        try {
            return new Base64File($source, static::generateFilename($source));
        } catch (\UnexpectedValueException $e) {
        }

        return null;
    }

    public static function fromFile(string $source): ?Base64File
    {
        $base64 = static::fileAsBase64($source);
        if ($base64) {
            return new Base64File($base64, static::extractFilename($source) ?? static::generateFilename($base64));
        }

        return null;
    }

    public static function fromURL(string $source): ?Base64File
    {
        $base64 = static::fetchAsBase64($source);
        if ($base64) {
            return new Base64File($base64, static::extractFilename($source) ?? static::generateFilename($base64));
        }

        return null;
    }

    private static function extractFilename(string $source): ?string
    {
        $url = \parse_url($source);
        $filename = isset($url['path']) ? \basename($url['path']) : null;

        return $filename ?: null;
    }

    private static function generateFilename(string $base64): string
    {
        /** @var callable $callback */
        $callback = 'trim';

        return \implode('.', \array_filter(\array_map($callback, [
            \sprintf('%d', \time()),
            Base64File::extractExtension($base64),
        ])));
    }

    private static function guessMimeTypeByContent(?string $content): ?string
    {
        if (!$content) {
            return null;
        }

        $fileInfo = new \finfo(\FILEINFO_MIME_TYPE);
        $mimeType = @$fileInfo->buffer($content) ?: null;

        return static::normalizeMimeType($mimeType);
    }

    private static function guessMimeTypeByExtension(?string $ext): ?string
    {
        if (!$ext) {
            return null;
        }

        $mimeTypes = MimeTypes::getDefault()->getMimeTypes($ext);

        return static::normalizeMimeType($mimeTypes[0] ?? null);
    }

    private static function getPathExtension(string $path): ?string
    {
        $ext = \pathinfo($path, PATHINFO_EXTENSION) ?: null;

        return $ext ? \strtolower($ext) : null;
    }

    private static function normalizeMimeType(?string $mimeType): ?string
    {
        if (!$mimeType) {
            return null;
        }

        $mimeType = \strtolower(\trim(\explode(';', $mimeType, 2)[0]));

        if (\in_array($mimeType, [
            'application/octet-stream',
            'application/x-empty',
        ], true)) {
            return null;
        }

        return $mimeType;
    }

    private static function fileAsBase64(string $source): ?string
    {
        if (\is_file($source) && $content = @\file_get_contents($source) ?: null) {
            $mimeType = static::guessMimeTypeByContent($content)
                ?? static::normalizeMimeType(MimeTypes::getDefault()->guessMimeType($source))
                ?? static::guessMimeTypeByExtension(static::getPathExtension($source))
            ;

            if ($mimeType) {
                return \sprintf('data:%s;base64,%s', $mimeType, \base64_encode($content));
            }
        }

        return null;
    }

    private static function fetchAsBase64(string $source): ?string
    {
        $context = \stream_context_create([
            'http' => [
                'ignore_errors' => true,
            ],
        ]);
        if ($content = \file_get_contents($source, false, $context) ?: null) {
            if (\function_exists('http_get_last_response_headers')) {
                /** @var ?string[] $rawHeaders */
                $rawHeaders = \http_get_last_response_headers();
            } else {
                $rawHeaders = $http_response_header;
            }

            if (\is_array($rawHeaders) && isset($rawHeaders[0])) {
                \preg_match('{HTTP\/\S*\s(\d{3})}', \array_shift($rawHeaders), $matches);
                $status = (int) ($matches[1] ?? 0);

                if (Response::HTTP_OK === $status) {
                    $headers = [];
                    foreach ($rawHeaders as $value) {
                        $matches = \explode(':', $value, 2);
                        if (2 === \count($matches)) {
                            $headers[\strtolower(\trim($matches[0]))] = \trim($matches[1]);
                        }
                    }

                    $mimeType = static::guessMimeTypeByContent($content)
                        ?? static::normalizeMimeType($headers['content-type'] ?? null)
                    ;

                    if (!$mimeType) {
                        $url = \parse_url($source);
                        if (isset($url['path'])) {
                            $mimeType = static::guessMimeTypeByExtension(static::getPathExtension($url['path']));
                        }
                    }

                    if ($mimeType) {
                        return \sprintf('data:%s;base64,%s', $mimeType, \base64_encode($content));
                    }
                }
            }
        }

        return null;
    }
}
