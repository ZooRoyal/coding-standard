<?php declare(strict_types = 1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

abstract class FixingToolCommand extends TargetableToolsCommand
{
    private FixableInputFacet $fixableFacet;

    public function __construct(
        FixableInputFacet $fixableFacet,
        TargetableInputFacet $targetableFacet,
        ?string $name = null
    ) {
        $this->fixableFacet = $fixableFacet;
        parent::__construct($targetableFacet, $name);
    }

    protected function configure(): void
    {
        parent::configure();
        $fixableInputDefinition = $this->fixableFacet->getInputDefinition();
        $this->getDefinition()->addOptions($fixableInputDefinition->getOptions());
        $this->getDefinition()->addArguments($fixableInputDefinition->getArguments());
    }
}
