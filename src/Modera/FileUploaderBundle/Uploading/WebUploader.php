<?php

namespace Modera\FileUploaderBundle\Uploading;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class WebUploader
{
    private ContributorInterface $gatewaysProvider;

    public function __construct(ContributorInterface $gatewaysProvider)
    {
        $this->gatewaysProvider = $gatewaysProvider;
    }

    public function upload(Request $request): ?Response
    {
        foreach ($this->gatewaysProvider->getItems() as $gateway) {
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
