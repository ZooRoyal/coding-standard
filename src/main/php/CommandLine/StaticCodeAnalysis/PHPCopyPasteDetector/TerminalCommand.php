<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPCopyPasteDetector;

use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\AbstractTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\ExcludingTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\FileExtensionTerminalCommand;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\ExcludingTrait;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits\FileExtensionTrait;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;
use function Safe\sprintf;

class TerminalCommand extends AbstractTerminalCommand implements
    ExcludingTerminalCommand,
    FileExtensionTerminalCommand
{
    use ExcludingTrait, FileExtensionTrait;

    private const TEMPLATE = 'php %1$s --fuzzy %3$s%2$s .';
    private const STATIC_EXCLUDES = ['ZRBannerSlider.php', 'Installer.php', 'ZRPreventShipping.php'];
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
        $vendorPath = $this->environment->getVendorPath()->getRealPath();

        $terminalApplication = $vendorPath . '/bin/phpcpd';

        $sprintfCommand = sprintf(
            self::TEMPLATE,
            $terminalApplication,
            $this->buildExcludingString(),
            $this->buildExtensionsString()
        );

        $this->command = $sprintfCommand;
        $this->commandParts = explode(' ', $sprintfCommand);
    }

    /**
     * This method returns the string representation of the excluded files list.
     */
    private function buildExcludingString(): string
    {
        $excludingString = '';
        if ($this->excludesFiles !== []) {
            $excludesFilePaths = array_map(
                static fn(EnhancedFileInfo $item) => '--exclude ' . $item->getRelativePathname() . '/',
                $this->excludesFiles
            );
            $excludingString = implode(' ', $excludesFilePaths) . ' ';
        };

        $staticExcludes = array_map(
            static fn(string $item) => '--exclude ' . $item,
            self::STATIC_EXCLUDES
        );
        return $excludingString . implode(' ', $staticExcludes);
    }

    private function buildExtensionsString(): string
    {
        $extensionsString = '';
        if ($this->fileExtensions !== []) {
            $extensionsString = '--suffix ' . implode(' --suffix ', $this->fileExtensions);
            $extensionsString .= ' ';
        }
        return $extensionsString;
    }
}
