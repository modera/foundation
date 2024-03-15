<?php

namespace Modera\TranslationsBundle\Service;

use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TranslationHandlersChain
{
    /**
     * @var TranslationHandlerInterface[]
     */
    private array $handlers = [];

    /**
     * @param TranslationHandlerInterface $handler
     */
    public function addHandler($handler): void
    {
        if ($handler instanceof TranslationHandlerInterface) {
            $this->handlers[] = $handler;
        }
    }

    /**
     * @return TranslationHandlerInterface[]
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
