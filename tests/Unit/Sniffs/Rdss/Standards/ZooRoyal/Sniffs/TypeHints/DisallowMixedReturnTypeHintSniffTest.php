<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\Sniffs\Rdss\Standards\ZooRoyal\Sniffs\TypeHints;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\Sniffs\Rdss\Standards\ZooRoyal\Sniffs\TypeHints\DisallowMixedReturnTypeHintSniff;

class DisallowMixedReturnTypeHintSniffTest extends TestCase
{
    private DisallowMixedReturnTypeHintSniff $subject;

    protected function setUp(): void
    {
        $this->subject = new DisallowMixedReturnTypeHintSniff();
    }

    /**
     * @test
     */
    public function registerReturnsTokens(): void
    {
        $expectedResult = [T_FUNCTION, T_CLOSURE,];

        $result = $this->subject->register();
        self::assertSame($expectedResult, $result);
    }
}
