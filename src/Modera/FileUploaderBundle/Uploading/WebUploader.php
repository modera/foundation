<?php

namespace Modera\FileUploaderBundle\Uploading;

use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2014 Modera Foundation
 */
class WebUploader
{
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    public function upload(Request $request): ?Response
    {
        $gatewaysProvider = $this->extensionProvider->get('modera_file_uploader.uploading.gateways');

        foreach ($gatewaysProvider->getItems() as $gateway) {
            /** @var UploadGatewayInterface $gateway */
            if ($gateway->isResponsible($request)) {
                if ($result = $gateway->upload($request)) {
                    return $result;
                }
            }
        }

        return null;
    }
}
