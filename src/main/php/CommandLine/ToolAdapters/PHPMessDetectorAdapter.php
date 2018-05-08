<?php
namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class PHPMessDetectorAdapter implements ToolAdapterInterface
{
    /** @var string */
    private $messDetectCommandWhitelist;
    /** @var string */
    private $messDetectCommandBlacklist;
    /** @var OutputInterface */
    private $output;
    /** @var Environment */
    private $environment;
    /** @var GenericCommandRunner */
    private $genericCommandRunner;
    /** @var string */
    private $stopword;
    /** @var string */
    private $filter;

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
        $this->environment          = $environment;
        $this->output               = $output;
        $this->genericCommandRunner = $genericCommandRunner;

        $phpMessDetectorConfig = $environment->getPackageDirectory() . '/src/config/phpmd/ZooRoyalDefault/phpmd.xml';
        $rootDirectory         = $environment->getRootDirectory();

        $this->stopword = '.dontMessDetectPHP';
        $this->filter   = '.php';

        $this->messDetectCommandWhitelist = 'php ' . $rootDirectory . '/vendor/bin/phpmd %1$s' .
            ' text ' . $phpMessDetectorConfig . ' --suffixes php';
        $this->messDetectCommandBlacklist = 'php ' . $rootDirectory . '/vendor/bin/phpmd '
            . $rootDirectory . ' text ' . $phpMessDetectorConfig . ' --suffixes php --exclude %1$s';
    }

    /**
     * Search for violations by using PHPCS and write finds to screen.
     *
     * @param string $targetBranch
     * @param bool   $processIsolation
     *
     * @return int|null
     */
    public function writeViolationsToOutput($targetBranch = '', $processIsolation = false)
    {
        if (empty($targetBranch) || $this->environment->getLocalBranch() === 'master') {
            $this->output->writeln('Running full check.', OutputInterface::VERBOSITY_NORMAL);
            $exitCode = $this->genericCommandRunner->runBlacklistCommand(
                $this->messDetectCommandBlacklist,
                $this->stopword
            );
        } else {
            $this->output->writeln('Running check on diff to ' . $targetBranch, OutputInterface::VERBOSITY_NORMAL);
            $exitCode = $this->genericCommandRunner->runWhitelistCommand(
                $this->messDetectCommandWhitelist,
                $targetBranch,
                $this->stopword,
                $this->filter,
                $processIsolation
            );
        }

        return $exitCode;
    }
}
