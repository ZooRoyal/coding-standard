<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Factories;

use Amp\PHPUnit\AsyncTestCase;
use Symfony\Component\Finder\Finder;
use Zooroyal\CodingStandard\CommandLine\Factories\FinderFactory;

class FinderFactoryTest extends AsyncTestCase
{

    /** @var FinderFactory */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
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
