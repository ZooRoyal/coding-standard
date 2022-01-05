<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\StaticCodeAnalysis\Generic\NpmAppFinder;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\Generic\NpmAppFinder\NpmCommandNotFoundException;

class NpmCommandNotFoundExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstantiatable(): void
    {
        $result = new NpmCommandNotFoundException();
        self::assertInstanceOf(NpmCommandNotFoundException::class, $result);
    }
}
