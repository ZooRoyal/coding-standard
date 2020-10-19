<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\AdaptableFileFinder;

class GenericCommandRunner
{
    /** @var OutputInterface */
    private $output;
    /** @var ProcessRunner */
    private $processRunner;
    /** @var AdaptableFileFinder */
    private $adaptableFileFinder;
    /** @var BlacklistFactory */
    private $blacklistFactory;

    /**
     * AbstractToolAdapter constructor.
     *
     * @param OutputInterface     $output
     * @param ProcessRunner       $processRunner
     * @param AdaptableFileFinder $adaptableFileFinder
     * @param BlacklistFactory    $blacklistFactory
     *
     * @inject
     */
    public function __construct(
        OutputInterface $output,
        ProcessRunner $processRunner,
        AdaptableFileFinder $adaptableFileFinder,
        BlacklistFactory $blacklistFactory
    ) {
        $this->output = $output;
        $this->processRunner = $processRunner;
        $this->adaptableFileFinder = $adaptableFileFinder;
        $this->blacklistFactory = $blacklistFactory;
    }


    /**
     * Builds a CLI-Command by inserting a whitelist of file paths in the command template and executes it.
     *
     * @param string      $template
     * @param string|null $targetBranch
     * @param string      $blacklistToken
     * @param string      $filter
     * @param bool        $processIsolation
     * @param string      $glue
     *
     * @return int
     */
    public function runWhitelistCommand(
        string $template,
        $targetBranch,
        string $blacklistToken,
        string $filter,
        bool $processIsolation = false,
        string $glue = ','
    ) : int {
        $exitCode = 0;
        $whitelistArguments = $this->buildWhitelistArguments(
            $targetBranch,
            $blacklistToken,
            $filter,
            $processIsolation,
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
     * @param bool   $escape  if true the blacklist entries will be escaped for regexp
     * @param bool   $blackListArgument if false there no arguments to build for command
     *
     * @return int|null
     */
    public function runBlacklistCommand(
        string $template,
        string $blacklistToken,
        string $prefix = '',
        string $glue = ',',
        bool $escape = false,
        bool $blackListArgument = true
    ) {
        if ($blackListArgument === false) {
            return $this->runAndWriteToOutput($template);
        }

        $argument = $this->concatBlackListArguments($blacklistToken, $escape, $prefix, $glue);
        $command = $this->buildCommand($template, $argument);
        return $this->runAndWriteToOutput($command);
    }

    /**
     * Builds a list of arguments for insertion into the template.
     *
     * @param string|null $targetBranch
     * @param string      $blacklistToken
     * @param string      $filter
     * @param bool        $processIsolation
     * @param string      $glue
     *
     * @return string[]
     */
    private function buildWhitelistArguments(
        $targetBranch,
        string $blacklistToken,
        string $filter,
        bool $processIsolation,
        string $glue = ','
    ) : array {
        $gitChangeSet = $this->adaptableFileFinder->findFiles($filter, $blacklistToken, '', $targetBranch);
        $changedFiles = $gitChangeSet->getFiles();

        $whitelistArguments = empty($changedFiles) || $processIsolation
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
     * @param bool $escape
     * @param string $prefix
     * @param string $glue
     */
    public function concatBlackListArguments(string $blacklistToken, bool $escape, string $prefix, string $glue): string
    {
        $blackList = $this->blacklistFactory->build($blacklistToken);
        if ($escape) {
            $blackList = array_map(
                function ($value) {
                    return preg_quote(preg_quote($value, '/'), '/');
                },
                $blackList
            );
        }
        return $prefix . implode($glue . $prefix, $blackList);
    }
}
