<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

interface FileExtensionTerminalCommand extends TerminalCommand
{
    /**
     * Lets the command know if it should run only on files with the given suffix.
     *
     * @param array<string> $fileExtensions
     */
    public function addAllowedFileExtensions(array $fileExtensions): void;
}
