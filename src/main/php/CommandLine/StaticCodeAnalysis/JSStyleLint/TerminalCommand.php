<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSStyleLint;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Exclusion\ExclusionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Extension\FileExtensionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix\FixTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Fix\FixTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Verbose\VerboseTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Verbose\VerboseTrait;

use function Safe\sprintf;

class TerminalCommand extends AbstractTerminalCommand implements
    TargetTerminalCommand,
    FixTerminalCommand,
    ExclusionTerminalCommand,
    FileExtensionTerminalCommand,
    VerboseTerminalCommand
{
    use TargetTrait;
    use FixTrait;
    use ExclusionTrait;
    use FileExtensionTrait;
    use VerboseTrait;

    private const TEMPLATE = 'npx --no-install stylelint %3$s %4$s%5$s--allow-empty-input --config=%1$s%2$s';

    /**
     * TerminalCommand constructor.
     */
    public function __construct(private readonly Environment $environment)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function compile(): void
    {
        $this->validateTargets();

        $styleLintConfig = $this->environment->getPackageDirectory()->getRealPath()
            . '/config/stylelint/.stylelintrc';

        $sprintfCommand = sprintf(
            self::TEMPLATE,
            $styleLintConfig,
            $this->buildExcludingString(),
            $this->buildTargetingString(),
            $this->buildVerbosityString(),
            $this->buildFixingString(),
        );

        $this->command = $sprintfCommand;
        $this->commandParts = explode(' ', $sprintfCommand);
    }

    /**
     * This method returns the string representation of the verbosity level.
     */
    private function buildVerbosityString(): string
    {
        $verbosityString = '';
        if ($this->verbosityLevel > OutputInterface::VERBOSITY_NORMAL) {
            $verbosityString = '--formatter verbose ';
        } elseif ($this->verbosityLevel < OutputInterface::VERBOSITY_NORMAL) {
            $verbosityString = '--quiet ';
        }

        return $verbosityString;
    }

    /**
     * This method returns the string representation of the excluded files list.
     */
    private function buildExcludingString(): string
    {
        $excludingString = '';
        if ($this->excludesFiles !== []) {
            $excludingString = ' --ignore-pattern=';
            $excludesFilePaths = array_map(
                static fn(EnhancedFileInfo $item): string => $item->getRelativePathname() . '/',
                $this->excludesFiles,
            );
            $excludingString .= implode(' --ignore-pattern=', $excludesFilePaths);
        }

        return $excludingString;
    }

    /**
     * This method returns the string representation of the targeted files list.
     */
    private function buildTargetingString(): string
    {
        if ($this->targetedFiles !== null) {
            $targetedFilePaths = array_map(
                static fn(EnhancedFileInfo $item): string => $item->getRelativePathname(),
                $this->targetedFiles,
            );
            $targetingString = implode(' ', $targetedFilePaths);
        } elseif ($this->fileExtensions !== []) {
            $targetingString = '**/*.{' . implode(',', $this->fileExtensions) . '}';
        } else {
            $targetingString = '.';
        }

        return $targetingString;
    }

    /**
     * This method returns the string representation of the fixing mode flag.
     */
    private function buildFixingString(): string
    {
        $fixingString = '';
        if ($this->fixingMode) {
            $fixingString = '--fix ';
        }
        return $fixingString;
    }
}
