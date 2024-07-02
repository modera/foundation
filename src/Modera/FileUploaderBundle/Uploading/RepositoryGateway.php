<?php

namespace Modera\FileUploaderBundle\Uploading;

use Modera\FileRepositoryBundle\Entity\StoredFile;
use Modera\FileRepositoryBundle\Repository\FileRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class RepositoryGateway implements UploadGatewayInterface
{
    private FileRepository $fileRepository;
    private string $repositoryName;

    public function __construct(FileRepository $fileRepository, string $repositoryName)
    {
        $this->fileRepository = $fileRepository;
        $this->repositoryName = $repositoryName;
    }

    public function isResponsible(Request $request): bool
    {
        $repository = $request->request->get('_repository');
        if (!$repository) {
            throw new \RuntimeException('Unable to resolve what channel to use ( request parameter "_repository" is missing )');
        }

        return $repository === $this->repositoryName;
    }

    public function upload(Request $request): ?Response
    {
        $this->beforeUpload($request);

        $storedFiles = $this->doUpload($request);

        $this->afterUpload($request, $storedFiles);

        return $this->formatResponse($request, $storedFiles);
    }

    /**
     * @param StoredFile[] $storedFiles
     */
    protected function formatResponse(Request $request, array $storedFiles): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
            ]);
        } else {
            return new Response(Response::$statusTexts[Response::HTTP_OK]);
        }
    }

    /**
     * Method is invoked before files are uploaded, if you need to prevent files upload then you can throw an exception
     * in this method.
     *
     * Feel free to override this method in subclass.
     */
    protected function beforeUpload(Request $request): void
    {
    }

    /**
     * Method is invoked when file(s) have been successfully uploaded.
     *
     * Feel free to override this method in subclass.
     *
     * @param StoredFile[] $storedFiles
     */
    protected function afterUpload(Request $request, array $storedFiles): void
    {
    }

    /**
     * @return StoredFile[]
     */
    protected function doUpload(Request $request): array
    {
        $storedFiles = [];

        foreach ($request->files as $file) {
            if ($file instanceof \SplFileInfo) {
                $storedFiles[] = $this->fileRepository->put($this->repositoryName, $file);
            }
        }

        return $storedFiles;
    }
}
