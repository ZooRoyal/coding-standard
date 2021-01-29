<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Zooroyal\CodingStandard\CommandLine\Library\FinderToPathsConverter;

class FinderToPathsConverterTest extends TestCase
{
    /** @var FinderToPathsConverter */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FinderToPathsConverter();
    }

    /**
     * @test
     */
    public function finderToArrayOfPaths()
    {
        $forgedFinder = new Finder();
        $forgedFinder->in(__DIR__);

        $result = $this->subject->finderToArrayOfPaths($forgedFinder);

        MatcherAssert::assertThat($result, H::hasValue('FinderToPathsConverterTest.php'));
    }

}
