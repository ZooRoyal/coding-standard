<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Functional\PHPCodesniffer\Standards\ZooRoyal\Sniffs\TypeHints\Fixtures\ReturnType\ReturnType\Parameter;

class FixtureNoMixedParameterTypeHints
{
    public function __construct(
        public int $testInt,
        public string $testString,
        public array $testArray,
    )
    {
    }

    public function addMoreTestData(int $testInt, string $testString, array $testArray) {
        $this->testInt = $testInt;
        $this->testString = $testString;
        $this->testArray = $testArray;
    }
}
