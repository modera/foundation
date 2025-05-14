<?php

namespace Modera\FileUploaderBundle\Controller;

use Modera\FileRepositoryBundle\Exceptions\FileValidationException;
use Modera\FileUploaderBundle\Uploading\WebUploader;
use Modera\FoundationBundle\Translation\T;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class UniversalUploaderController extends AbstractController
{
    public function __construct(
        private readonly WebUploader $webUploader,
    ) {
    }

    #[Route(path: '%modera_file_uploader.uploader_url%', name: 'modera_file_uploader', options: ['expose' => true])]
    public function uploadAction(Request $request): Response
    {
        if (!$this->getParameter('modera_file_uploader.is_enabled')) {
            throw $this->createNotFoundException(T::trans('Uploader is not enabled.'));
        }

        try {
            $response = $this->webUploader->upload($request);
        } catch (FileValidationException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => \implode(', ', $e->getErrors()),
                'errors' => $e->getErrors(),
            ]);
        }

        if (null === $response) {
            return new JsonResponse([
                'success' => false,
                'error' => T::trans('Unable to find an upload gateway that is able to process this file upload.'),
            ]);
        }

        return $response;
    }
}
