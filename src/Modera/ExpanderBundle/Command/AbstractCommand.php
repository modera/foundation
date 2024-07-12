<?php

namespace Modera\ExpanderBundle\Command;

use Modera\ExpanderBundle\Misc\KernelProxy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractCommand extends Command
{
    /**
     * @var ?(KernelInterface&KernelProxy)
     */
    public ?KernelProxy $kernelProxy = null;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->kernelProxy) {
            /** @var KernelInterface&KernelProxy $kernelProxy */
            // @phpstan-ignore-next-line
            $kernelProxy = new KernelProxy('dev', true);
            $this->kernelProxy = $kernelProxy;
        }

        $this->kernelProxy->boot();
        $this->kernelProxy->cleanUp();

        try {
            $this->doExecute($this->kernelProxy, $input, $output);
        } catch (\Exception $e) {
            $this->kernelProxy->cleanUp();

            throw $e;
        }

        return 0;
    }

    /**
     * @param KernelInterface&KernelProxy $kernelProxy
     */
    abstract protected function doExecute(KernelProxy $kernelProxy, InputInterface $input, OutputInterface $output): void;
}
