<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators;

interface ToolConfigGeneratorInterface
{
    public function addConfigParameters(string $blackListToken, string $rootDirectory, array $parameters): array;

    public function generateConfig(array $parameters) :string;

    public function writeConfig(string $filename, string $content) : void;
}
