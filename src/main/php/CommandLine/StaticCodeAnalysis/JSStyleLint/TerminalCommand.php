<?php
declare(strict_types = 1);
namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\JSStyleLint;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\ExcludingTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\FixingTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\TargetableTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\ExcludingTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\FileExtensionTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\FixingTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\TargetableTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\VerbosityTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\VerboseTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use function Safe\sprintf;

class TerminalCommand extends AbstractTerminalCommand implements
    TargetableTerminalCommand,
    FixingTerminalCommand,
    ExcludingTerminalCommand,
    FileExtensionTerminalCommand,
    VerboseTerminalCommand
{
    use TargetableTrait, FixingTrait, ExcludingTrait, FileExtensionTrait, VerbosityTrait;

    private const TEMPLATE = 'npx --no-install stylelint %3$s %4$s%5$s--allow-empty-input --config=%1$s%2$s';
    private Environment $environment;

    /**
     * TerminalCommand constructor.
     *
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
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
                static fn(EnhancedFileInfo $item) => $item->getRelativePathname() . '/',
                $this->excludesFiles
            );
            $excludingString .= implode(' --ignore-pattern=', $excludesFilePaths);
        };
        return $excludingString;
    }

    /**
     * This method returns the string representation of the targeted files list.
     */
    private function buildTargetingString(): string
    {
        if ($this->targetedFiles !== null) {
            $targetedFilePaths = array_map(
                static fn(EnhancedFileInfo $item) => $item->getRelativePathname(),
                $this->targetedFiles
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
