<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\Traits;

trait FileExtensionTrait
{
    protected array $fileExtensions = [];

    public function addAllowedFileExtensions(array $fileExtensions): void
    {
        $this->fileExtensions = $fileExtensions;
    }
}
