<?php

namespace Modera\DynamicallyConfigurableAppBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Modera\DynamicallyConfigurableAppBundle\KernelConfig;
use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class KernelConfigCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('modera:dynamically-configurable-app:kernel-config')
            ->setDescription('Kernel config read/write command.')
            ->addArgument('json', InputArgument::OPTIONAL, 'kernel config in json format')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($json = json_decode($input->getArgument('json'), true)) {
            $validation = array(
                'debug' => [ false, true ],
                'env' => [ 'prod', 'dev' ],
            );

            $this->kernelConfigWrite(array_filter(
                $json,
                function($value, $key) use ($validation) {
                    return in_array($key, array_keys($validation), true) && in_array($value, $validation[$key], true);
                },
                ARRAY_FILTER_USE_BOTH
            ));
        }

        if ($input->getArgument('json') && json_last_error() != JSON_ERROR_NONE) {
            $output->writeln('<error>Invalid JSON</error>');

        } else {
            $output->writeln(json_encode(array_filter($this->kernelConfigRead(), function($key) {
                return '_comment' !== $key;
            }, ARRAY_FILTER_USE_KEY), JSON_PRETTY_PRINT));
        }
    }

    /**
     * @return array
     */
    private function kernelConfigRead()
    {
        return call_user_func(array($this->getKernelConfigFQCN(), 'read'));
    }

    /**
     * @param array $mode
     */
    private function kernelConfigWrite(array $mode)
    {
        $types = array(
            'debug' => 'bool',
            'env' => 'string',
        );

        foreach ($mode as $key => $value) {
            if (array_key_exists($key, $types)) {
                $this->em()->createQuery(sprintf(
                    'UPDATE %s e SET e.%sValue = :value WHERE e.name = :name AND e.category = :category',
                    ConfigurationEntry::clazz(),
                    $types[$key]
                ))
                    ->setParameter('value', $value)
                    ->setParameter('name', 'kernel_' . $key)
                    ->setParameter('category', 'general')
                    ->execute()
                ;
            }
        }

        call_user_func(array($this->getKernelConfigFQCN(), 'write'), $mode);
    }

    /**
     * @return string
     */
    private function getKernelConfigFQCN()
    {
        $kernelConfig = $this->getContainer()->getParameter('modera_dynamically_configurable_app.kernel_config_fqcn');

        return $kernelConfig ?: KernelConfig::class;
    }

    /**
     * @return EntityManager
     */
    protected function em()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
