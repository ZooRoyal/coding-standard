<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;
use function Safe\sprintf;

class GenericCommandRunner
{
    /** @var OutputInterface */
    private $output;
    /** @var ProcessRunner */
    private $processRunner;
    /** @var AdaptableFileFinder */
    private $adaptableFileFinder;
    /** @var ExclusionListFactory */
    private $blacklistFactory;

    /**
     * AbstractToolAdapter constructor.
     *
     * @param OutputInterface      $output
     * @param ProcessRunner        $processRunner
     * @param AdaptableFileFinder  $adaptableFileFinder
     * @param ExclusionListFactory $blacklistFactory
     *
     * @inject
     */
    public function __construct(
        OutputInterface $output,
        ProcessRunner $processRunner,
        AdaptableFileFinder $adaptableFileFinder,
        ExclusionListFactory $blacklistFactory
    ) {
        $this->output = $output;
        $this->processRunner = $processRunner;
        $this->adaptableFileFinder = $adaptableFileFinder;
        $this->blacklistFactory = $blacklistFactory;
    }


    /**
     * Builds a CLI-Command by inserting a whitelist of file paths in the command template and executes it.
     *
     * @param string $template
     * @param string|null $targetBranch
     * @param string $blacklistToken
     * @param string[] $allowedFileEndings
     * @param string $glue
     *
     * @return int
     */
    public function runWhitelistCommand(
        string $template,
        $targetBranch,
        string $blacklistToken,
        array $allowedFileEndings,
        string $glue = ','
    ): int {
        $exitCode = 0;
        $whitelistArguments = $this->buildWhitelistArguments(
            $targetBranch,
            $blacklistToken,
            $allowedFileEndings,
            $glue
        );

        foreach ($whitelistArguments as $argument) {
            $commandWithParameters = $this->buildCommand($template, $argument);

            $exitCode = $this->runAndWriteToOutput($commandWithParameters);
        }

        return $exitCode;
    }

    /**
     * Builds a CLI-Command by inserting a blacklist of file paths in the command template and executes it.
     *
     * @param string $template
     * @param string $blacklistToken
     * @param string $prefix
     * @param string $glue
     *
     * @return int|null
     */
    public function runBlacklistCommand(
        string $template,
        string $blacklistToken,
        string $prefix = '',
        string $glue = ','
    ) {
        $argument = $this->concatBlackListArguments($blacklistToken, $prefix, $glue);
        $command = $this->buildCommand($template, $argument);
        return $this->runAndWriteToOutput($command);
    }

    /**
     * Builds a list of arguments for insertion into the template.
     *
     * @param string|null $targetBranch
     * @param string $blacklistToken
     * @param string[] $allowedFileEndings
     * @param string $glue
     *
     * @return string[]
     */
    private function buildWhitelistArguments(
        $targetBranch,
        string $blacklistToken,
        array $allowedFileEndings,
        string $glue = ','
    ): array {
        $gitChangeSet = $this->adaptableFileFinder->findFiles($allowedFileEndings, $blacklistToken, '', $targetBranch);
        $changedFiles = $gitChangeSet->getFiles();

        $whitelistArguments = empty($changedFiles)
            ? $changedFiles
            : [implode($glue, $changedFiles)];

        $this->output->writeln(
            'Checking diff to ' . $gitChangeSet->getCommitHash(),
            OutputInterface::OUTPUT_NORMAL
        );

        $this->output->writeln(
            'Files to handle:' . "\n" . implode("\n", $changedFiles) . "\n",
            OutputInterface::VERBOSITY_VERBOSE
        );

        return $whitelistArguments;
    }


    /**
     * Runs a command and prints the output to the screen if the command couldn't be executed without errors.
     *
     * @param string $commandWithParameters
     *
     * @return int
     */
    private function runAndWriteToOutput($commandWithParameters)
    {
        $exitCode = 0;
        $process = $this->processRunner->runAsProcessReturningProcessObject(
            $commandWithParameters
        );
        if ($process->getExitCode() !== 0) {
            $exitCode = $process->getExitCode();
            $this->output->writeln($process->getOutput(), OutputInterface::OUTPUT_NORMAL);
            $this->output->writeln($process->getErrorOutput(), OutputInterface::VERBOSITY_NORMAL);
        }

        return $exitCode;
    }

    /**
     * Builds a command from themplate and argument.
     *
     * @param string $command
     * @param string $argument
     *
     * @return string
     */
    private function buildCommand($command, $argument)
    {
        $command = sprintf($command, $argument);
        $this->output->writeln(
            'Calling following command:' . "\n" . $command,
            OutputInterface::VERBOSITY_DEBUG
        );

        return $command;
    }

    /**
     * Concats Balcklist result with glue and prefix
     *
     * @param string $blacklistToken
     * @param string $prefix
     * @param string $glue
     */
    private function concatBlackListArguments(
        string $blacklistToken,
        string $prefix,
        string $glue
    ): string {
        $blackList = $this->blacklistFactory->build($blacklistToken);
        return $prefix . implode($glue . $prefix, $blackList);
    }
}
