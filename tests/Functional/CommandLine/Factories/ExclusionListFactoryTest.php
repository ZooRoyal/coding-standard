<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\Factories;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty;
use Zooroyal\CodingStandard\CommandLine\ApplicationLifeCycle\ContainerFactory;
use Zooroyal\CodingStandard\CommandLine\ExclusionList\ExclusionListFactory;

use function Safe\mkdir;
use function Safe\rmdir;

class ExclusionListFactoryTest extends TestCase
{
    private ExclusionListFactory $subject;

    protected function setUp(): void
    {
        $forgedGitPath = __DIR__ . '/Fixtures/gitExclude/.git';
        if (!is_dir($forgedGitPath)) {
            mkdir($forgedGitPath);
        }

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
    public function buildContainsGitBlacklistAndStopword(): void
    {
        $forgedStopword = '.stopword';
        $result = $this->subject->build($forgedStopword);

        MatcherAssert::assertThat(
            $result,
            H::hasItems(
                HasProperty::hasProperty(
                    'getRelativePathname',
                    'tests/Functional/CommandLine/Factories/Fixtures/gitExclude',
                ),
                HasProperty::hasProperty(
                    'getRelativePathname',
                    'tests/Functional/CommandLine/Factories/Fixtures/StopWordTest',
                ),
                HasProperty::hasProperty(
                    'getRelativePathname',
                    'vendor',
                ),
            ),
        );
    }
}
