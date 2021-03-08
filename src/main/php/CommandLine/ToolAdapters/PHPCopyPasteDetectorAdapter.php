<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use DI\Annotation\Injectable;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

/**
 * Class PHPCopyPasteDetectorAdapter
 *
 * @Injectable(lazy=true)
 */
class PHPCopyPasteDetectorAdapter implements ToolAdapterInterface
{
    /** @var string */
    private $copyPasteDetectCommand;
    /** @var OutputInterface */
    private $output;
    /** @var GenericCommandRunner */
    private $genericCommandRunner;
    /** @var string */
    private $stopword;

    /**
     * PHPCodeSnifferAdapter constructor.
     *
     * @param Environment          $environment
     * @param OutputInterface      $output
     * @param GenericCommandRunner $genericCommandRunner
     */
    public function __construct(
        Environment $environment,
        OutputInterface $output,
        GenericCommandRunner $genericCommandRunner
    ) {
        $this->output = $output;
        $this->genericCommandRunner = $genericCommandRunner;

        $this->stopword = '.dontCopyPasteDetectPHP';

        $vendorPath = $environment->getVendorPath()->getRealPath();
        $rootDirectory = $environment->getRootDirectory()->getRelativePathname();

        $this->copyPasteDetectCommand = 'php ' . $vendorPath . '/bin/phpcpd '
            . '--fuzzy --exclude=ZRBannerSlider.php,Installer.php,ZRPreventShipping.php %1$s '
            . $rootDirectory;
    }

    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = ''): ?int
    {
        $fullMessage = 'PHPCPD : Running full check';

        $this->output->writeln($fullMessage, OutputInterface::VERBOSITY_NORMAL);
        $exitCode = $this->genericCommandRunner->runBlacklistCommand(
            $this->copyPasteDetectCommand,
            $this->stopword,
            '--exclude ',
            ' '
        );

        return $exitCode;
    }
}
