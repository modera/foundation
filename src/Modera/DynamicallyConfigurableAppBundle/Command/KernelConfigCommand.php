<?php

namespace Modera\DynamicallyConfigurableAppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\DynamicallyConfigurableAppBundle\KernelConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class KernelConfigCommand extends Command
{
    private EntityManagerInterface $em;

    private ParameterBagInterface $params;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->params = $params;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('modera:dynamically-configurable-app:kernel-config')
            ->setDescription('Kernel config read/write command.')
            ->addArgument('json', InputArgument::OPTIONAL, 'kernel config in json format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $json = $input->getArgument('json');
        if (\is_string($json) && ($arr = \json_decode($json, true)) && \is_array($arr)) {
            $validation = [
                'debug' => [false, true],
                'env' => ['prod', 'dev'],
            ];

            /** @var array{'debug'?: bool, 'env'?: string} $mode */
            $mode = \array_filter(
                $arr,
                function ($value, $key) use ($validation) {
                    return \in_array($key, \array_keys($validation), true) && \in_array($value, $validation[$key], true);
                },
                \ARRAY_FILTER_USE_BOTH
            );

            $this->kernelConfigWrite($mode);
        }

        if ($json && \JSON_ERROR_NONE !== \json_last_error()) {
            $output->writeln('<error>Invalid JSON</error>');
        } else {
            $output->writeln(\json_encode(\array_filter($this->kernelConfigRead(), function ($key) {
                return '_comment' !== $key;
            }, \ARRAY_FILTER_USE_KEY), \JSON_PRETTY_PRINT) ?: '');
        }

        return 0;
    }

    /**
     * @return array{'debug': bool, 'env': string}
     */
    private function kernelConfigRead(): array
    {
        $callback = [$this->getKernelConfigFQCN(), 'read'];
        if (!\is_callable($callback)) {
            throw new \RuntimeException('Read method not found');
        }

        /** @var array{'debug': bool, 'env': string} $arr */
        $arr = \call_user_func($callback);

        return $arr;
    }

    /**
     * @param array{'debug'?: bool, 'env'?: string} $mode
     */
    private function kernelConfigWrite(array $mode): void
    {
        $types = [
            'debug' => 'bool',
            'env' => 'string',
        ];

        foreach ($mode as $key => $value) {
            if (\array_key_exists($key, $types)) {
                $this->em->createQuery(\sprintf(
                    'UPDATE %s e SET e.%sValue = :value WHERE e.name = :name AND e.category = :category',
                    ConfigurationEntry::class,
                    $types[$key]
                ))
                    ->setParameter('value', $value)
                    ->setParameter('name', 'kernel_'.$key)
                    ->setParameter('category', 'general')
                    ->execute()
                ;
            }
        }

        $callback = [$this->getKernelConfigFQCN(), 'write'];
        if (!\is_callable($callback)) {
            throw new \RuntimeException('Write method not found');
        }

        \call_user_func($callback, $mode);
    }

    private function getKernelConfigFQCN(): string
    {
        /** @var string $kernelConfig */
        $kernelConfig = $this->params->get('modera_dynamically_configurable_app.kernel_config_fqcn');

        return $kernelConfig ?: KernelConfig::class;
    }
}
