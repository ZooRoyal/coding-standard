<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library\Exceptions;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Library\Exceptions\TerminalCommandNotFoundException;

class TerminalCommandNotFoundExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstantiatable()
    {
        new TerminalCommandNotFoundException();
    }
}
