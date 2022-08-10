<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCodeSniffer;

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
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess\MultiprocessTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Multiprocess\MultiprocessTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Target\TargetTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Verbose\VerboseTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Verbose\VerboseTrait;
use function Safe\sprintf;

class TerminalCommand extends AbstractTerminalCommand implements
    FixTerminalCommand,
    TargetTerminalCommand,
    ExclusionTerminalCommand,
    FileExtensionTerminalCommand,
    VerboseTerminalCommand,
    MultiprocessTerminalCommand
{
    use TargetTrait, FixTrait, ExclusionTrait, FileExtensionTrait, VerboseTrait, MultiprocessTrait;

    private const TEMPLATE = 'php %1$s %5$s%6$s--parallel=%7$d -p --standard=%2$s %3$s%4$s';
    private Environment $environment;

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

        $vendorPath = $this->environment->getVendorDirectory()->getRealPath();
        $phpCodeSnifferConfig = $this->environment->getPackageDirectory()->getRealPath()
            . '/config/phpcs/ZooRoyal/ruleset.xml';

        $terminalApplication = $this->fixingMode
            ? $vendorPath . '/bin/phpcbf'
            : $vendorPath . '/bin/phpcs -s';

        $sprintfCommand = sprintf(
            self::TEMPLATE,
            $terminalApplication,
            $phpCodeSnifferConfig,
            $this->buildExcludingString(),
            $this->buildTargetingString(),
            $this->buildVerbosityString(),
            $this->buildExtensionString(),
            $this->maxConcurrentProcesses,
        );

        $this->command = $sprintfCommand;
        $this->commandParts = explode(' ', $sprintfCommand);
    }

    private function buildExtensionString(): string
    {
        $extensionString = '';
        if ($this->fileExtensions !== []) {
            $extensionString = '--extensions=' . implode(',', $this->fileExtensions);
            $extensionString .= ' ';
        }
        return $extensionString;
    }

    /**
     * This method returns the string representation of the verbosity level.
     */
    private function buildVerbosityString(): string
    {
        $verbosityString = '';
        if ($this->verbosityLevel === OutputInterface::VERBOSITY_VERBOSE) {
            $verbosityString = '-v ';
        } elseif ($this->verbosityLevel === OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $verbosityString = '-vv ';
        } elseif ($this->verbosityLevel === OutputInterface::VERBOSITY_DEBUG) {
            $verbosityString = '-vvv ';
        } elseif ($this->verbosityLevel < OutputInterface::VERBOSITY_NORMAL) {
            $verbosityString = '-q ';
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
            $excludingString = '--ignore=';
            $excludesFilePaths = array_map(
                static fn(EnhancedFileInfo $item) => $item->getRealPath(),
                $this->excludesFiles
            );
            $excludingString .= implode(',', $excludesFilePaths);
            $excludingString .= ' ';
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
                static fn(EnhancedFileInfo $item) => $item->getRelativePathname(),
                $this->targetedFiles
            );
            $targetingString = implode(' ', $targetedFilePaths);
        } else {
            $targetingString = $this->environment->getRootDirectory()->getRelativePathname();
        }
        return $targetingString;
    }
}
