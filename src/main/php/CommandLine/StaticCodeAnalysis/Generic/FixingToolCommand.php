<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic;

use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

abstract class FixingToolCommand extends TargetableToolsCommand
{
    public function __construct(
        private readonly FixableInputFacet $fixableFacet,
        TargetableInputFacet $targetableFacet,
        ?string $name = null,
    ) {
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
