<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\ToolCommandFacet\FixableInputFacet;

class FixableInputFacetTest extends TestCase
{
    public function testGetInputDefinition()
    {
        $subject = new FixableInputFacet();

        $result = $subject->getInputDefinition();

        $option = $result->getOption('fix');

        self::assertSame('f', $option->getShortcut());
        self::assertSame('Runs tool to try to fix violations automagically.', $option->getDescription());
        self::assertFalse($option->acceptValue());
    }
}
