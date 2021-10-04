<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

abstract class TargetableToolsCommand extends AbstractToolCommand
{
    private TargetableInputFacet $targetableFacet;

    public function __construct(TargetableInputFacet $targetableFacet, string $name = null)
    {
        $this->targetableFacet = $targetableFacet;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDefinition($this->targetableFacet->getInputDefinition());
    }
}
