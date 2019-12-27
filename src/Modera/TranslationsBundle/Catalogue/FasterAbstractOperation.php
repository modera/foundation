<?php


namespace Modera\TranslationsBundle\Catalogue;

use Symfony\Component\Translation\Catalogue\AbstractOperation;
use Symfony\Component\Translation\Exception\LogicException;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * Base catalogues binary operation class.
 *
 * A catalogue binary operation performs operation on
 * source (the left argument) and target (the right argument) catalogues.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
abstract class FasterAbstractOperation extends AbstractOperation
{
    /**
     * @throws LogicException
     */
    public function __construct(MessageCatalogueInterface $source, MessageCatalogueInterface $target)
    {
        parent::__construct($source, $target);

        $this->result = new FasterMessageCatalogue($source->getLocale());
    }

}