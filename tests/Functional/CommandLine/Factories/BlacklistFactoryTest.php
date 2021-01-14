<?php

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\Factories;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty;
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
    public function buildContainsGitBlacklistAndStopword(): void
    {
        $forgedStopword = '.stopword';
        $result = $this->subject->build($forgedStopword);

        MatcherAssert::assertThat(
            $result,
            H::hasItems(
                HasProperty::hasProperty(
                    'getRelativePathname',
                    'tests/Functional/CommandLine/Factories/Fixtures/gitExclude'
                ),
                HasProperty::hasProperty(
                    'getRelativePathname',
                    'tests/Functional/CommandLine/Factories/Fixtures/StopWordTest'
                ),
                HasProperty::hasProperty(
                    'getRelativePathname',
                    'vendor'
                )
            )
        );
    }
}
