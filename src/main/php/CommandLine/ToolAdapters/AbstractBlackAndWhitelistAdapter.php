<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

abstract class AbstractBlackAndWhitelistAdapter
{
    /** @var OutputInterface */
    protected $output;
    /** @var Environment */
    protected $environment;
    /** @var GenericCommandRunner */
    protected $genericCommandRunner;

    /** @var string */
    protected $blacklistToken = '';
    /** @var string */
    protected $filter = '';
    /** @var string */
    protected $blacklistPrefix = '';
    /** @var string */
    protected $blacklistGlue = '';
    /** @var string */
    protected $whitelistGlue = '';
    /** @var string[] */
    protected $commands = [];
    /** @var bool */
    protected $escape = false;

    /**
     * Runs ESLint in normal or fix mode according to settings.
     *
     * @param string|false|null $targetBranch
     * @param bool              $processIsolation
     * @param string            $fullMessage
     * @param string            $tool
     * @param string            $diffMessage
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
                $this->filter,
                $processIsolation,
                $this->whitelistGlue
            );
        }

        return $exitCode;
    }

    public function getBlacklistToken() : string
    {
        return $this->blacklistToken;
    }

    public function getFilter() : string
    {
        return $this->filter;
    }

    public function getBlacklistPrefix() : string
    {
        return $this->blacklistPrefix;
    }

    public function getBlacklistGlue() : string
    {
        return $this->blacklistGlue;
    }

    public function getWhitelistGlue() : string
    {
        return $this->whitelistGlue;
    }

    public function getCommands() : array
    {
        return $this->commands;
    }

    public function isEscape() : bool
    {
        return $this->escape;
    }

}
