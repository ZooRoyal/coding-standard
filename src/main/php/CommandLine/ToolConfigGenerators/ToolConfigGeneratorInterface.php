<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators;

use Symplify\SmartFileSystem\SmartFileInfo;

interface ToolConfigGeneratorInterface
{
    public function addConfigParameters(string $blackListToken, SmartFileInfo $rootDirectory, array $parameters): array;

    public function generateConfig(array $parameters): string;

    public function writeConfig(string $filename, string $content): void;
}
