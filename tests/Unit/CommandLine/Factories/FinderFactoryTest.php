<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Zooroyal\CodingStandard\CommandLine\Factories\FinderFactory;

class FinderFactoryTest extends TestCase
{

    /** @var FinderFactory */
    private $subject;

    protected function setUp()
    {
        $this->subject = new FinderFactory();
    }

    /**
     * @test
     */
    public function buildReturnsFinderInstance()
    {
        $result = $this->subject->build();

        self::assertInstanceOf(Finder::class, $result);
    }
}
