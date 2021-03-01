<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Functional\PHPCodesniffer\Standards\ZooRoyal\Sniffs\TypeHints\Fixtures\ReturnType;

class FixtureWithMixedReturnTypeHints
{
    public function __construct(
        public int $testInt,
        public mixed $testString,
        public mixed $testArray,
    )
    {
    }

    public function addTestData(int $testInt): array {
        $this->testInt = $testInt;
    }

    public function addMoreTestData(int $testInt, mixed $testString, mixed $testArray): mixed {
        $this->testInt = $testInt;
        $this->testString = $testString;
        $this->testArray = $testArray;

        return [
            $this->testArray,
        ];
    }

    /**
     * @param mixed $mixed
     */
    public function addArray(array $mixed): mixed {
        return (function (): mixed {
            return [1,2,'2'];
        })();
    }
}
