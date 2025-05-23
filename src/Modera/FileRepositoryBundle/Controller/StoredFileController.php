<?php

namespace Modera\FileRepositoryBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Modera\FileRepositoryBundle\DependencyInjection\ModeraFileRepositoryExtension;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @copyright 2015 Modera Foundation
 */
#[AsController]
class StoredFileController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/{storageKey}', name: 'modera_file_repository.get_file', requirements: ['storageKey' => '.+'])]
    public function getAction(Request $request, string $storageKey): Response
    {
        /** @var bool $isEnabled */
        $isEnabled = $this->getParameter(ModeraFileRepositoryExtension::CONFIG_KEY.'.controller.is_enabled');

        if (!$isEnabled) {
            throw $this->createAccessDeniedException();
        }

        return $this->createFileResponse($request, $storageKey);
    }

    /**
     * @internal
     */
    protected function getFile(string $storageKey): ?StoredFile
    {
        /** @var ObjectRepository $repository */
        $repository = $this->managerRegistry->getManager()->getRepository(StoredFile::class);

        /** @var ?StoredFile $file */
        $file = $repository->findOneBy([
            'storageKey' => $storageKey,
        ]);

        return $file;
    }

    /**
     * @internal
     */
    protected function createFileResponse(Request $request, string $storageKey): Response
    {
        $response = new Response();

        $parts = \explode('/', $storageKey);

        $file = $this->getFile($parts[0]);
        if (!$file) {
            return $response->setContent('File not found.')->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        if (null !== $request->get('dl')) {
            $filename = $file->getFilename();
            if (\count($parts) > 1) {
                $filename = $parts[\count($parts) - 1];
            }

            $filenameFallback = \filter_var($filename, FILTER_SANITIZE_URL) ?: '';
            if ($filenameFallback != $filename) {
                $extension = \filter_var(
                    MimeTypes::getDefault()->getExtensions($file->getMimeType() ?? '')[0] ?? $file->getExtension(),
                    FILTER_SANITIZE_URL
                );
                $filenameFallback = $file->getStorageKey().($extension ? '.'.$extension : '');
            }

            $response->headers->set(
                'Content-Disposition',
                $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $filename,
                    $filenameFallback
                )
            );
        } else {
            $response->setCache([
                'etag' => $file->getStorageKey(),
                'last_modified' => $file->getCreatedAt(),
            ]);

            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $response->setContent($file->getContents());
        $response->headers->set('Content-type', $file->getMimeType());
        $response->headers->set('Content-length', (string) $file->getSize());

        return $response;
    }
}
