<?php
namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class PHPParallelLintAdapter implements ToolAdapterInterface
{
    /** @var string */
    private $parallelLintWhitelistCommand;
    /** @var string */
    private $parallelLintBlacklistCommand;
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
     * PHPParallelLintAdapter constructor.
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

        $rootDirectory = $environment->getRootDirectory();

        $this->stopword = '.dontLintPHP';
        $this->filter   = '.php';

        $this->parallelLintWhitelistCommand = 'php ' . $rootDirectory
            . '/vendor/bin/parallel-lint -j 2 %1$s';
        $this->parallelLintBlacklistCommand = 'php ' . $rootDirectory
            . '/vendor/bin/parallel-lint -j 2 %1$s ./';
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
        if (empty($targetBranch) || $this->environment->isLocalBranchEqualTo('origin/master')) {
            $this->output->writeln('Running full check.', OutputInterface::VERBOSITY_NORMAL);
            $exitCode = $this->genericCommandRunner->runBlacklistCommand(
                $this->parallelLintBlacklistCommand,
                $this->stopword,
                '--exclude ',
                ' '
            );
        } else {
            $this->output->writeln('Running check on diff to ' . $targetBranch, OutputInterface::VERBOSITY_NORMAL);
            $exitCode = $this->genericCommandRunner->runWhitelistCommand(
                $this->parallelLintWhitelistCommand,
                $targetBranch,
                $this->stopword,
                $this->filter,
                $processIsolation,
                ' '
            );
        }

        return $exitCode;
    }
}
