<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\Tests\Tools;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

/**
 * The sole use of this class is to make TerminalCommandTests easier to mass produce.
 */
class TerminalCommandTestData
{
    private string $expectedCommand;
    /** @var array<EnhancedFileInfo>|null  */
    private ?array $targets = null;
    private bool $fixingMode = false;
    /** @var array<EnhancedFileInfo>  */
    private array $excluded = [];
    private int $verbosityLevel = OutputInterface::VERBOSITY_NORMAL;
    /** @var array<string>  */
    private array $extensions = [];
    private int $processes = 1;

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
        $this->processes = $values['processes'] ?? $this->processes;
    }

    /** @return array<EnhancedFileInfo> */
    public function getExcluded(): array
    {
        return $this->excluded;
    }

    public function getExpectedCommand(): string
    {
        return $this->expectedCommand;
    }

    /** @return array<string> */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function getProcesses(): int
    {
        return $this->processes;
    }

    /** @return array<EnhancedFileInfo>|null */
    public function getTargets(): ?array
    {
        return $this->targets;
    }

    public function getVerbosityLevel(): int
    {
        return $this->verbosityLevel;
    }

    public function isFixing(): bool
    {
        return $this->fixingMode;
    }
}
