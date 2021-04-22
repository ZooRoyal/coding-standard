<?php

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

abstract class TargetableToolsCommand extends AbstractToolCommand
{
    /** @var TargetableInputFacet */
    private TargetableInputFacet $targetableFacet;

    public function __construct(TargetableInputFacet $targetableFacet, string $name = null)
    {
        $this->targetableFacet = $targetableFacet;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDefinition($this->targetableFacet->getInputDefinition());
    }
}
