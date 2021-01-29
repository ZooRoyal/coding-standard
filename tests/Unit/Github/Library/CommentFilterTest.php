<?php

namespace Zooroyal\CodingStandard\Tests\Unit\Github\Library;

use Mockery;
use Amp\PHPUnit\AsyncTestCase;
use Zooroyal\CodingStandard\Github\Library\CommentFilter;

class CommentFilterTest extends AsyncTestCase
{
    /** @var CommentFilter */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new CommentFilter();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function filterForStaleCommentsDoesExactlyThatDataProvider() : array
    {
        $position = 1;
        $commitId = '1asdasd2';

        $positionsData = ['position' => $position, 'original_position' => $position, 'original_commit_id' => $commitId];
        $onePositionWrongData = ['position' => $position, 'original_position' => 123, 'original_commit_id' => $commitId];
        $allWrongData = ['position' => 432, 'original_position' => 123, 'original_commit_id' => 'qweasdqwe121212'];

        return [
            'alles richtig' => [[$allWrongData, $positionsData], $position, 5, [$positionsData]],
            'eine position falsch' => [[$allWrongData, $onePositionWrongData], $position, 5, [$onePositionWrongData]],
            'positions falsch' => [[$allWrongData, $positionsData], 5, 5, []],
            'original_commit_id falsch' => [[$allWrongData, $positionsData], $position, $commitId, []],
        ];
    }

    /**
     * @test
     * @dataProvider filterForStaleCommentsDoesExactlyThatDataProvider
     */
    public function filterForStaleCommentsDoesExactlyThat($mockedComments, $mockedPosition, $mockedCommitId, $expectedResult)
    {
        $result = $this->subject->filterForStaleComments($mockedComments, $mockedPosition, $mockedCommitId);

        self::assertSame($expectedResult, $result);
    }

    public function filterForOwnCommentsDoesExactlyThatDataProvider() : array
    {
        $path = 'asdasd';
        $login = '1asdasd2';

        $positionData = ['path' => $path, 'user' => ['login' => $login]];
        $allWrongData = ['path' => 'asdasd1233', 'user' => ['login' => '123as21as12as']];

        return [
            'alles richtig' => [[$allWrongData, $positionData], $path, $login, [$positionData]],
            'login falsch' => [[$allWrongData, $positionData], $path, 5, []],
            'path falsch' => [[$allWrongData, $positionData], 5, $login, []],
        ];
    }

    /**
     * @test
     * @dataProvider filterForOwnCommentsDoesExactlyThatDataProvider
     */
    public function filterForOwnCommentsDoesExactlyThat($mockedComments, $mockedPath, $mockedUser, $expectedResult)
    {
        $result = $this->subject->filterForOwnComments($mockedComments, $mockedPath, $mockedUser);

        self::assertSame($expectedResult, $result);
    }
}
