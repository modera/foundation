<?php

namespace Modera\ExpanderBundle\Generation;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class StandardContributionGenerator implements ContributionGeneratorInterface
{
    private ?string $className = null;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        if (\is_string($config['className'] ?? null)) {
            $this->className = $config['className'];
        }
    }

    public function generate(
        BundleInterface $bundle,
        ExtensionPoint $extensionPoint,
        InputInterface $input,
        OutputInterface $output,
        ?HelperSet $helperSet = null
    ): void {
        if (!\file_exists($bundle->getPath().'/Contributions')) {
            $output->writeln('Creating contributions directory ...');
            \mkdir($bundle->getPath().'/Contributions');
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

        $contributionFilename = $bundle->getPath().'/Contributions/'.$className.'.php';
        if (\file_exists($contributionFilename)) {
            throw new \RuntimeException("File '$contributionFilename' already exists!");
        }

        $servicesFilename = $bundle->getPath().'/Resources/config/services.xml';
        if (!\file_exists($servicesFilename)) {
            throw new \RuntimeException("File '$servicesFilename' doesn't exist.");
        }

        $servicesXml = \file_get_contents($servicesFilename);
        if (!$servicesXml) {
            throw new \RuntimeException('Service XML file cannot be loaded.');
        }

        \file_put_contents($contributionFilename, $this->compileContributionClassTemplate($bundle, $extensionPoint, $className));
        \file_put_contents($servicesFilename, $this->compileServicesXml($bundle, $extensionPoint, $className, $servicesXml));

        $output->writeln('Done!');
        $output->writeln(' - New file: '.$contributionFilename);
        $output->writeln(' - Updated: '.$servicesFilename);
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

namespace %namespace%\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

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

        return \str_replace(['%namespace%', '%class_name%'], [$bundle->getNamespace(), $className], $tpl);
    }

    protected function getServiceXmlTemplate(): string
    {
        return <<<TPL

        <service id="%id%"
                 class="%class_name%">

            <tag name="%tag_name%" />
        </service>
TPL;
    }

    protected function compileServicesXml(BundleInterface $bundle, ExtensionPoint $extensionPoint, string $className, string $servicesXml): string
    {
        $tpl = $this->getServiceXmlTemplate();

        $bundleServicesNamespace = \substr($this->camelToSnake($bundle->getName()), 0, -1 * \strlen('_bundle'));
        $serviceId = $bundleServicesNamespace.'.contributions.'.$this->camelToSnake($className);
        $fqcn = $bundle->getNamespace().'\\Contributions\\'.$className;
        $tagName = $extensionPoint->getContributionTag();

        $compiledServiceXml = \str_replace(['%id%', '%class_name%', '%tag_name%'], [$serviceId, $fqcn, $tagName], $tpl);
        $compiledServiceXmlAsArray = \explode(PHP_EOL, $compiledServiceXml);

        $servicesXmlAsArray = \explode(PHP_EOL, $servicesXml);

        $closingServicesTagIndex = null;
        foreach ($servicesXmlAsArray as $lineIndex => $rootLine) {
            // we are going to add a new service right before a closing </services> tag
            if ('</services>' === \trim($rootLine)) {
                $closingServicesTagIndex = $lineIndex;
            }
        }

        if (null === $closingServicesTagIndex) {
            throw new \RuntimeException('Unable to find a closing </services> tag!');
        }

        $resultXmlArray = [];
        foreach ($servicesXmlAsArray as $lineIndex => $rootLine) {
            if ($lineIndex === $closingServicesTagIndex) {
                foreach ($compiledServiceXmlAsArray as $innerLine) {
                    $resultXmlArray[] = $innerLine;
                }
            }

            $resultXmlArray[] = $rootLine;
        }

        return \implode(PHP_EOL, $resultXmlArray);
    }

    protected function camelToSnake(string $word): string
    {
        return \strtolower(\preg_replace('~(?<=\\w)([A-Z])~', '_$1', $word) ?? $word);
    }
}
