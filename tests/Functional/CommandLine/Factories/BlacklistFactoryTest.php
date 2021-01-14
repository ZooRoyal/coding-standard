<?php

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\Factories;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;

class BlacklistFactoryTest extends TestCase
{
    /** @var BlacklistFactory */
    private $subject;

    protected function setUp(): void
    {
        mkdir(__DIR__ . '/Fixtures/gitExclude/.git');

        $container = ContainerFactory::getUnboundContainerInstance();
        $this->subject = $container->get(BlacklistFactory::class);
    }

    protected function tearDown(): void
    {
        rmdir(__DIR__ . '/Fixtures/gitExclude/.git');
    }

    /**
     * @test
     * @medium
     */
    public function buildContainsGitBlacklistAndStopword()
    {
        $forgedStopword = '.stopword';
        $result = $this->subject->build($forgedStopword);

        self::assertContains('tests/Functional/CommandLine/Factories/Fixtures/gitExclude', $result);
        self::assertContains('tests/Functional/CommandLine/Factories/Fixtures/StopWordTest', $result);
        self::assertContains('vendor', $result);
    }
}
