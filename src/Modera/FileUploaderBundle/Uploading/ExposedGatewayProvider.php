<?php

namespace Modera\FileUploaderBundle\Uploading;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ExposedGatewayProvider implements ContributorInterface
{
    /**
     * @var UploadGatewayInterface[]
     */
    private array $items;

    public function __construct(AllExposedRepositoriesGateway $gateway)
    {
        $this->items = [$gateway];
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
