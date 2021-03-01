<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\Library;

use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;

class TerminalCommandFinder
{
    /**
     * TerminalCommandFinder constructor.
     */
    public function __construct(private ProcessRunner $processRunner)
    {
    }

    /**
     * Finds path to command.
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
                ucfirst($commandName) . ' could not be found in path or by npm',
                1595949828
            );
        }

        return 'npx --no-install ' . $commandName;
    }
}
