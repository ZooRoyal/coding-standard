<?php
namespace Zooroyal\CodingStandard\CommandLine\Library;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\FileFinders\DiffCheckableFileFinder;

class GenericCommandRunner
{
    /** @var OutputInterface */
    private $output;
    /** @var ProcessRunner */
    private $processRunner;
    /** @var DiffCheckableFileFinder */
    private $diffCheckableFileFinder;
    /** @var BlacklistFactory */
    private $blacklistFactory;

    /**
     * AbstractToolAdapter constructor.
     *
     * @param OutputInterface         $output
     * @param ProcessRunner           $processRunner
     * @param DiffCheckableFileFinder $diffCheckableFileFinder
     * @param BlacklistFactory        $blacklistFactory
     *
     * @inject
     */
    public function __construct(
        OutputInterface $output,
        ProcessRunner $processRunner,
        DiffCheckableFileFinder $diffCheckableFileFinder,
        BlacklistFactory $blacklistFactory
    ) {
        $this->output                  = $output;
        $this->processRunner           = $processRunner;
        $this->diffCheckableFileFinder = $diffCheckableFileFinder;
        $this->blacklistFactory        = $blacklistFactory;
    }


    /**
     * Builds a CLI-Command by inserting a whitelist of file paths in the command template and executes it.
     *
     * @param string $template
     * @param string $targetBranch
     * @param string $stopword
     * @param string $filter
     * @param bool   $processIsolation
     * @param string $glue
     *
     * @return int
     */
    public function runWhitelistCommand(
        $template,
        $targetBranch,
        $stopword,
        $filter,
        $processIsolation = false,
        $glue = ','
    ) {
        $exitCode           = 0;
        $whitelistArguments = $this->buildWhitelistArguments(
            $targetBranch,
            $stopword,
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
     * @param string $stopword
     * @param string $prefix
     * @param string $glue
     * @param bool   $escape if true the blacklist entries will be escaped for regexp
     *
     * @return int|null
     */
    public function runBlacklistCommand($template, $stopword, $prefix = '', $glue = ',', $escape = false)
    {
        $blackList = $this->blacklistFactory->build($stopword);
        if ($escape) {
            $blackList = array_map(
                function ($value) {
                    return preg_quote(preg_quote($value, '/'), '/');
                },
                $blackList
            );
        }
        $argument = $prefix . implode($glue . $prefix, $blackList);

        $command = $this->buildCommand($template, $argument);

        return $this->runAndWriteToOutput($command);
    }

    /**
     * Builds a list of arguments for insertion into the template.
     *
     * @param string $targetBranch
     * @param string $stopword
     * @param string $filter
     * @param bool   $processIsolation
     * @param string $glue
     *
     * @return string[]
     */
    private function buildWhitelistArguments(
        $targetBranch,
        $stopword,
        $filter,
        $processIsolation,
        $glue = ','
    ) {
        $gitChangeSet = $this->diffCheckableFileFinder->findFiles($filter, $stopword, $targetBranch);
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
        $process  = $this->processRunner->runAsProcessReturningProcessObject(
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
}
