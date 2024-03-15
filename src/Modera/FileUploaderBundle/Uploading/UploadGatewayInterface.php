<?php

namespace Modera\FileUploaderBundle\Uploading;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
interface UploadGatewayInterface
{
    public function isResponsible(Request $request): bool;

    public function upload(Request $request): ?Response;
}
