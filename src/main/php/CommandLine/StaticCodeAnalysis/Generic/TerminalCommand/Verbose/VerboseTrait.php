<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Verbose;

use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

trait VerboseTrait
{
    protected int $verbosityLevel = OutputInterface::VERBOSITY_NORMAL;
    /** @var array<int> */
    private array $allowedValues = [
            OutputInterface::VERBOSITY_DEBUG,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
            OutputInterface::VERBOSITY_VERBOSE,
            OutputInterface::VERBOSITY_NORMAL,
            OutputInterface::VERBOSITY_QUIET,
        ];

    /**
     * Specify a set of files paths which should **NOT** be checked.
     *
     * @param int $verbosityLevel The constants from \Symfony\Component\Console\Output\OutputInterface should be used.
     *
     * @throws InvalidArgumentException
     */
    final public function addVerbosityLevel(int $verbosityLevel): void
    {
        if (!in_array($verbosityLevel, $this->allowedValues, true)) {
            throw new InvalidArgumentException(
                'Only verbosity settings from OutputInterface constants are allowed',
                1617802684,
            );
        }
        $this->verbosityLevel = $verbosityLevel;
    }
}
