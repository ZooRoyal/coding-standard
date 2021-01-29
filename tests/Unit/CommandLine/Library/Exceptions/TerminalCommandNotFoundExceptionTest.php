<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library\Exceptions;

use Amp\PHPUnit\AsyncTestCase;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;

class TerminalCommandNotFoundExceptionTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function shouldBeInstantiatable()
    {
        $result = new TerminalCommandNotFoundException();
        self::assertInstanceOf(TerminalCommandNotFoundException::class, $result);
    }
}
