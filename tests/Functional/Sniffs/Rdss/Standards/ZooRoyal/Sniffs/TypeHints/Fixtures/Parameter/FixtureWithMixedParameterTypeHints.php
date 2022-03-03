<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Functional\Sniffs\Rdss\Standards\ZooRoyal\Sniffs\TypeHints\Fixtures\Parameter;

class FixtureWithMixedParameterTypeHints
{
    public function __construct(
        public int $testInt,
        public mixed $testString,
        public mixed $testArray,
    )
    {
    }

    public function addTestData(int $testInt) {
        $this->testInt = $testInt;
    }

    public function addMoreTestData(int $testInt, mixed $testString, mixed $testArray) {
        $this->testInt = $testInt;
        $this->testString = $testString;
        $this->testArray = $testArray;
    }

    /**
     * @param mixed $mixed
     */
    public function addArray(array $mixed) {
        return array_map(static function(mixed $data): array {
            return $data;
        }, $mixed);
    }
}
