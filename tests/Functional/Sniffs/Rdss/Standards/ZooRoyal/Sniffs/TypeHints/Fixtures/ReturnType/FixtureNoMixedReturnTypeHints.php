<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Functional\Sniffs\Rdss\Standards\ZooRoyal\Sniffs\TypeHints\Fixtures\ReturnType;

class FixtureNoMixedReturnTypeHints
{
    public function __construct(
        public int $testInt,
        public string $testString,
        public array $testArray,
    )
    {
    }

    public function addMoreTestData(int $testInt, string $testString, array $testArray): void {
        $this->testInt = $testInt;
        $this->testString = $testString;
        $this->testArray = $testArray;
    }

    public function isCorrect(): ?bool
    {
        return (1 > 2) ? null : false;
    }
}
