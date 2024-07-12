<?php

namespace Modera\FileUploaderBundle\Controller;

use Modera\FileRepositoryBundle\Exceptions\FileValidationException;
use Modera\FileUploaderBundle\Uploading\WebUploader;
use Modera\FoundationBundle\Translation\T;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UniversalUploaderController extends Controller
{
    protected function getContainer(): ContainerInterface
    {
        /** @var ContainerInterface $container */
        $container = $this->container;

        return $container;
    }

    /**
     * @Route("%modera_file_uploader.uploader_url%", name="modera_file_uploader", options={"expose"=true})
     */
    public function uploadAction(Request $request): Response
    {
        if (!$this->getContainer()->getParameter('modera_file_uploader.is_enabled')) {
            throw $this->createNotFoundException(T::trans('Uploader is not enabled.'));
        }

        /** @var WebUploader $webUploader */
        $webUploader = $this->getContainer()->get('modera_file_uploader.uploading.web_uploader');

        try {
            $response = $webUploader->upload($request);
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
