<?php

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\Factories;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;

class BlacklistFactoryTest extends TestCase
{
    /** @var BlacklistFactory */
    private $subject;

    protected function setUp(): void
    {
        $forgedGitPath = __DIR__ . '/Fixtures/gitExclude/.git';
        if (!is_dir($forgedGitPath)) {
            mkdir($forgedGitPath);
        }

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

        MatcherAssert::assertThat(
            $result,
            H::allOf(
                H::hasItem('tests/Functional/CommandLine/Factories/Fixtures/gitExclude/'),
                H::hasItem('tests/Functional/CommandLine/Factories/Fixtures/StopWordTest/'),
                H::hasItem('vendor/')
            )
        );
    }
}
