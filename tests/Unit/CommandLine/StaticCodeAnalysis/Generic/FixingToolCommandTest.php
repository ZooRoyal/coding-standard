<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic;

use Mockery;
use Mockery\MockInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;

abstract class FixingToolCommandTest extends TargetableToolsCommandTest
{
    /** @var MockInterface|FixableInputFacet */
    protected FixableInputFacet $mockedFixableInputFacet;
    /** @var MockInterface|InputDefinition */
    protected InputDefinition $mockedFixableInputDefinition;
    /** @var MockInterface|InputOption */
    protected InputOption $mockedFixingOption;
    /** @var array<MockInterface|InputOption> */
    protected array $mockedFixingOptions;
    /** @var array<MockInterface|InputArgument> */
    protected array $mockedFixingArguments = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedFixableInputFacet = Mockery::mock(FixableInputFacet::class);
        $this->mockedFixableInputDefinition = Mockery::mock(InputDefinition::class);
        $this->mockedFixingOption = Mockery::mock(InputOption::class);
        $this->mockedFixingOptions = [$this->mockedFixingOption];

        $this->mockedFixableInputFacet->shouldReceive('getInputDefinition')
            ->andReturn($this->mockedFixableInputDefinition);
        $this->mockedFixableInputDefinition->shouldReceive('getOptions')->once()
            ->andReturn($this->mockedFixingOptions);
        $this->mockedFixableInputDefinition->shouldReceive('getArguments')->once()
            ->andReturn($this->mockedFixingArguments);

        $this->mockedTargetInputDefinition->shouldReceive('addOptions')->once()
            ->with($this->mockedTargetingOptions);
        $this->mockedTargetInputDefinition->shouldReceive('addArguments')->once()
            ->with($this->mockedTargetingArguments);
    }
}
