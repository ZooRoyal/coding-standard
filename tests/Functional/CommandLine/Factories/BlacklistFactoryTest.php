<?php

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\Factories;

use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use function Safe\mkdir;
use function Safe\rmdir;

class BlacklistFactoryTest extends TestCase
{
    /** @var ExclusionListFactory */
    private $subject;

    protected function setUp(): void
    {
        mkdir(__DIR__ . '/Fixtures/gitExclude/.git');

        $container = ContainerFactory::getUnboundContainerInstance();
        $this->subject = $container->get(ExclusionListFactory::class);
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
