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
    private string $copyPasteDetectCommand;
    private OutputInterface $output;
    private GenericCommandRunner $genericCommandRunner;
    private string $stopword;
    /** @var string */
    private const FULL_MESSAGE = 'PHPCPD : Running full check';

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
        $this->output->writeln(self::FULL_MESSAGE, OutputInterface::VERBOSITY_NORMAL);
        $exitCode = $this->genericCommandRunner->runBlacklistCommand(
            $this->copyPasteDetectCommand,
            $this->stopword,
            '--exclude ',
            ' '
        );

        return $exitCode;
    }
}
