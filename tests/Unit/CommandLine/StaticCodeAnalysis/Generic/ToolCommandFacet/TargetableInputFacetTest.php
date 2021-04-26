<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\TargetableInputFacet;

class TargetableInputFacetTest extends TestCase
{
    public function testGetInputDefinition(): void
    {
        $subject = new TargetableInputFacet();

        $result = $subject->getInputDefinition();

        $targetOption = $result->getOption('target');

        self::assertSame('t', $targetOption->getShortcut());
        self::assertSame(
            'Finds Files which have changed since the current branch parted from the target branch '
            . 'only. The Value has to be a commit-ish.',
            $targetOption->getDescription()
        );
        self::assertTrue($targetOption->isValueRequired());

        $autoTargetOption = $result->getOption('auto-target');

        self::assertSame('a', $autoTargetOption->getShortcut());
        self::assertSame(
            'Finds Files which have changed since the current branch parted from the parent branch '
            . 'only. It tries to find the parent branch by automagic.',
            $autoTargetOption->getDescription()
        );
        self::assertFalse($autoTargetOption->acceptValue());
    }
}
