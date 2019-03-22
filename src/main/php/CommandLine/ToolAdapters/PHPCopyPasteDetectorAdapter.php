<?php
namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

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
        $this->output               = $output;
        $this->genericCommandRunner = $genericCommandRunner;

        $this->stopword = '.dontCopyPasteDetectPHP';
        $rootDirectory  = $environment->getRootDirectory();

        $this->copyPasteDetectCommand = 'php ' . $rootDirectory . '/vendor/bin/phpcpd -vvv '
            . '--progress --fuzzy -n --names-exclude=ZRBannerSlider.php,Installer.php,ZRPreventShipping.php %1$s '
            . $rootDirectory;
    }

    /**
     * Search for violations by using PHPCBD and write finds to screen.
     *
     * @param string $targetBranch
     * @param bool   $processIsolation
     *
     * @return int|null
     */
    public function writeViolationsToOutput($targetBranch = '', $processIsolation = false)
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
