<?php

namespace Modera\TranslationsBundle\Service;

use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;

/**
 * @copyright 2014 Modera Foundation
 */
class TranslationHandlersChain
{
    /**
     * @var TranslationHandlerInterface[]
     */
    private array $handlers = [];

    public function addHandler(TranslationHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @return TranslationHandlerInterface[]
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
