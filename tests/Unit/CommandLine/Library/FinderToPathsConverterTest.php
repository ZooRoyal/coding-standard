<?php

namespace Zooroyal\CodingStandard\Tests\Unit\CommandLine\Library;

use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use Amp\PHPUnit\AsyncTestCase;
use Symfony\Component\Finder\Finder;
use Zooroyal\CodingStandard\CommandLine\Library\FinderToPathsConverter;

class FinderToPathsConverterTest extends AsyncTestCase
{
    /** @var FinderToPathsConverter */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
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
