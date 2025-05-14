<?php

namespace Modera\FileUploaderBundle\Uploading;

use Modera\FileRepositoryBundle\Repository\FileRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2014 Modera Foundation
 */
class AllExposedRepositoriesGateway implements UploadGatewayInterface
{
    public function __construct(
        private readonly FileRepository $fileRepository,
    ) {
    }

    protected function getRepositoryName(Request $request): ?string
    {
        /** @var ?string $repositoryName */
        $repositoryName = $request->request->get('_repository');

        return $repositoryName;
    }

    public function isResponsible(Request $request): bool
    {
        if ($repositoryName = $this->getRepositoryName($request)) {
            return $this->fileRepository->repositoryExists($repositoryName);
        }

        return false;
    }

    public function upload(Request $request): ?Response
    {
        /** @var string $repositoryName */
        $repositoryName = $this->getRepositoryName($request);

        $ids = [];
        foreach ($request->files as $file) {
            if ($file instanceof \SplFileInfo) {
                $storedFile = $this->fileRepository->put($repositoryName, $file);
                $ids[] = $storedFile->getId();
            }
        }

        return new JsonResponse([
            'success' => true,
            'ids' => $ids,
        ]);
    }
}
