<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet;

use Symfony\Component\Console\Input\InputDefinition;

interface ToolCommandInputFacet
{
    public function getInputDefinition(): InputDefinition;
}
