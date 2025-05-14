<?php

namespace Modera\ExpanderBundle\Generation;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @copyright 2024 Modera Foundation
 */
class StandardContributionGenerator implements ContributionGeneratorInterface
{
    private ?string $className = null;

    private string $dirName = 'Contribution';

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        if (\is_string($config['className'] ?? null)) {
            $this->className = $config['className'];
        }
        if (\is_string($config['dirName'] ?? null)) {
            $this->dirName = $config['dirName'];
        }
    }

    public function generate(
        BundleInterface $bundle,
        ExtensionPoint $extensionPoint,
        InputInterface $input,
        OutputInterface $output,
        ?HelperSet $helperSet = null,
    ): void {
        $dirName = $this->dirName;
        if (!\file_exists($bundle->getPath().'/'.$dirName)) {
            $output->writeln('Creating contribution directory ...');
            \mkdir($bundle->getPath().'/'.$dirName);
        }

        /** @var string $className */
        $className = $this->className;
        while (!$this->isValidClassName($className)) {
            if (!$helperSet) {
                throw new \RuntimeException('Contribution class name not defined');
            }

            /** @var QuestionHelper $questionHelper */
            $questionHelper = $helperSet->get('question');
            $question = new Question('<info>Please specify a contribution class name:</info> ');
            /** @var string $className */
            $className = $questionHelper->ask($input, $output, $question);
        }

        $contributionFilename = $bundle->getPath().'/'.$dirName.'/'.$className.'.php';
        if (\file_exists($contributionFilename)) {
            throw new \RuntimeException(\sprintf('File "%s" already exists!', $contributionFilename));
        }
        \file_put_contents($contributionFilename, $this->compileContributionClassTemplate($bundle, $extensionPoint, $className));

        $output->writeln(' - New file: '.$contributionFilename);
        $output->writeln(\sprintf(' - Register contribution in "%s":', $bundle->getPath().'/Resources/config/services.php'));
        $output->writeln('');
        $output->writeln($this->compileServices($bundle, $extensionPoint, $className));
        $output->writeln('');

        $output->writeln('Done!');
    }

    /**
     * @private
     */
    public function isValidClassName(?string $className): bool
    {
        // a simple validation against accidental mistyping rather than
        // a fully-fledged class name validation
        return $className && false === \strpos($className, ' ');
    }

    protected function getContributionClassTemplate(): string
    {
        return <<<TPL
<?php

namespace %namespace%\%dir_name%;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

#[AsContributorFor('%extension_point_id%')]
class %class_name% implements ContributorInterface
{
    public function getItems(): array
    {
        return [];
    }
}
TPL;
    }

    protected function compileContributionClassTemplate(BundleInterface $bundle, ExtensionPoint $extensionPoint, string $className): string
    {
        $tpl = $this->getContributionClassTemplate();

        return \str_replace(
            ['%namespace%', '%dir_name%', '%class_name%', '%extension_point_id%'],
            [$bundle->getNamespace(), $this->dirName, $className, $extensionPoint->getId()],
            $tpl,
        );
    }

    protected function getServicesTemplate(): string
    {
        return <<<TPL
<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use %namespace%\%dir_name%\%class_name%;

return static function (ContainerConfigurator \$container): void {
    \$services = \$container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    \$services->set(%class_name%::class);
};
TPL;
    }

    protected function compileServices(BundleInterface $bundle, ExtensionPoint $extensionPoint, string $className): string
    {
        $tpl = $this->getServicesTemplate();

        return \str_replace(
            ['%namespace%', '%dir_name%', '%class_name%', '%extension_point_id%'],
            [$bundle->getNamespace(), $this->dirName, $className, $extensionPoint->getId()],
            $tpl,
        );
    }
}
