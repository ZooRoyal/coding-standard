<?php

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;

class TerminalCommandFinder
{
    private ProcessRunner $processRunner;
    /**
     * TerminalCommandFinder constructor.
     *
     * @param ProcessRunner $processRunner
     */
    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    /**
     * Finds path to command.
     *
     * @param string $commandName
     *
     * @throws TerminalCommandNotFoundException
     */
    public function findTerminalCommand(string $commandName): string
    {
        $exitCode = $this->processRunner->runAsProcessReturningProcessObject(
            'npx --no-install ' . $commandName . ' --help'
        )->getExitCode();

        if ($exitCode !== 0) {
            throw new TerminalCommandNotFoundException(
                $commandName . ' could not be found in path or by npm',
                1595949828
            );
        }

        return 'npx --no-install ' . $commandName;
    }
}
