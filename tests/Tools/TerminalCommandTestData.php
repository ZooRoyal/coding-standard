<?php

namespace Zooroyal\CodingStandard\Tests\Tools;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

/**
 * The sole use of this class is to make TerminalCommandTests easier to mass produce.
 */
class TerminalCommandTestData
{
    private string $expectedCommand;
    private array $targets = [];
    private bool $fixingMode = false;
    private array $excluded = [];
    private int $verbosityLevel = OutputInterface::VERBOSITY_NORMAL;
    private array $extensions = [];

    /**
     * TerminalCommandTestData constructor.
     *
     * @param array<string,string|bool|int|array<EnhancedFileInfo|string>> $values
     */
    public function __construct(array $values)
    {
        $this->expectedCommand = $values['expectedCommand'];
        $this->targets = $values['targets'] ?? $this->targets;
        $this->fixingMode = $values['fixingMode'] ?? $this->fixingMode;
        $this->excluded = $values['excluded'] ?? $this->excluded;
        $this->verbosityLevel = $values['verbosityLevel'] ?? $this->verbosityLevel;
        $this->extensions = $values['extensions'] ?? $this->extensions;
    }

    public function getExpectedCommand(): string
    {
        return $this->expectedCommand;
    }

    public function getTargets(): array
    {
        return $this->targets;
    }

    public function isFixing(): bool
    {
        return $this->fixingMode;
    }

    public function getExcluded(): array
    {
        return $this->excluded;
    }

    public function getVerbosityLevel(): int
    {
        return $this->verbosityLevel;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
