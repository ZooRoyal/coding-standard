<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand;

use Exception;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\TerminalCommand\NoUsefulCommandFoundException;

class NoUsefulCommandFoundExceptionTest extends TestCase
{

    /**
     * @test
     */
    public function noUsefulCommandFoundExceptionIsExcpetion(): void
    {
        $subject = new NoUsefulCommandFoundException('Es ist beliebig');

        self::assertInstanceOf(Exception::class, $subject);
    }
}
