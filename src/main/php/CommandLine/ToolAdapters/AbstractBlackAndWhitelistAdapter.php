<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;

abstract class AbstractBlackAndWhitelistAdapter
{
    protected OutputInterface $output;
    protected Environment $environment;
    protected GenericCommandRunner $genericCommandRunner;

    protected string $blacklistToken = '';
    /** @var string[] */
    protected array $allowedFileEndings = [];
    protected string $blacklistPrefix = '';
    protected string $blacklistGlue = '';
    protected string $whitelistGlue = '';
    /** @var string[] */
    protected array $commands = [];
    protected bool $escape = false;
    protected TerminalCommandFinder $terminalCommandFinder;

    /**
     * Constructor of all ToolAdapters
     *
     * @param Environment $environment
     * @param OutputInterface $output
     * @param GenericCommandRunner $genericCommandRunner
     * @param TerminalCommandFinder $terminalCommandFinder
     */
    public function __construct(
        Environment $environment,
        OutputInterface $output,
        GenericCommandRunner $genericCommandRunner,
        TerminalCommandFinder $terminalCommandFinder
    ) {
        $this->environment = $environment;
        $this->output = $output;
        $this->genericCommandRunner = $genericCommandRunner;
        $this->terminalCommandFinder = $terminalCommandFinder;

        $this->init();
    }

    /**
     * This method is supposed to be used to set the tool up.
     */
    abstract protected function init(): void;

    /**
     * Runs tool in normal or fix mode according to settings.
     *
     * @param string|false|null $targetBranch
     * @param bool $processIsolation
     * @param string $fullMessage
     * @param string $tool
     * @param string $diffMessage
     *
     * @return int|null
     */
    protected function runTool(
        $targetBranch,
        bool $processIsolation,
        string $fullMessage,
        string $tool,
        string $diffMessage
    ) {
        if ($targetBranch === false || $this->environment->isLocalBranchEqualTo($targetBranch)) {
            $this->output->writeln($fullMessage, OutputInterface::VERBOSITY_NORMAL);
            $template = $this->commands[$tool . 'BL'];
            $exitCode = $this->genericCommandRunner->runBlacklistCommand(
                $template,
                $this->blacklistToken,
                $this->blacklistPrefix,
                $this->blacklistGlue,
                $this->escape
            );
        } else {
            $this->output->writeln($diffMessage, OutputInterface::VERBOSITY_NORMAL);
            $template = $this->commands[$tool . 'WL'];
            $exitCode = $this->genericCommandRunner->runWhitelistCommand(
                $template,
                $targetBranch,
                $this->blacklistToken,
                $this->allowedFileEndings,
                $processIsolation,
                $this->whitelistGlue
            );
        }

        return $exitCode;
    }

    public function getBlacklistToken(): string
    {
        return $this->blacklistToken;
    }

    public function getAllowedFileEndings(): array
    {
        return $this->allowedFileEndings;
    }

    public function getBlacklistPrefix(): string
    {
        return $this->blacklistPrefix;
    }

    public function getBlacklistGlue(): string
    {
        return $this->blacklistGlue;
    }

    public function getWhitelistGlue(): string
    {
        return $this->whitelistGlue;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function isEscape(): bool
    {
        return $this->escape;
    }
}
