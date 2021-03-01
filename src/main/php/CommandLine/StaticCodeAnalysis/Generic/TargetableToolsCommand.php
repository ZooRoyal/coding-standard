<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

abstract class TargetableToolsCommand extends AbstractToolCommand
{
    public function __construct(private TargetableInputFacet $targetableFacet, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDefinition($this->targetableFacet->getInputDefinition());
    }
}
