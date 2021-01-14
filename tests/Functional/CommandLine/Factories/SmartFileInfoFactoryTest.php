<?php

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\Factories;

use Composer\Factory;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty;
use SplFileInfo;
use Symfony\Component\Finder\SplFileInfo as SymfonyFileInfo;
use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\SmartFileInfoFactory;

class SmartFileInfoFactoryTest extends TestCase
{
    private static $DiSe = DIRECTORY_SEPARATOR;

    private SmartFileInfoFactory $subject;
    private string $absolutFilePath;
    private string $relativeFilePath;
    private string $relativeFilePath2;
    private $rootDirectory;

    public function setUp(): void
    {
        $container = ContainerFactory::getContainerInstance();

        $this->relativeFilePath = 'tests'
            . self::$DiSe . 'Functional'
            . self::$DiSe . 'CommandLine'
            . self::$DiSe . 'Factories'
            . self::$DiSe . 'Fixtures'
            . self::$DiSe . 'gitExclude'
            . self::$DiSe . '.keep';

        $this->relativeFilePath2 = 'composer.json';

        $this->rootDirectory = dirname(realpath(Factory::getComposerFile()));
        $this->absolutFilePath = $this->rootDirectory . self::$DiSe . $this->relativeFilePath;

        $this->subject = $container->get(SmartFileInfoFactory::class);
    }

    /**
     * @test
     */
    public function buildFromPathToDirectoryCreatesFromPath()
    {
        $result = $this->subject->buildFromPath(__DIR__ . '/..');

        self::assertInstanceOf(SmartFileInfo::class, $result);
    }

    /**
     * @test
     */
    public function buildFromPathCreatesFromAbsolutPath()
    {
        $result = $this->subject->buildFromPath(
            $this->absolutFilePath
        );

        self::assertInstanceOf(SmartFileInfo::class, $result);
    }

    /**
     * @test
     */
    public function buildFromPathCreatesRelativToComposerPath()
    {
        $result = $this->subject->buildFromPath('.' . self::$DiSe . $this->relativeFilePath);

        self::assertInstanceOf(SmartFileInfo::class, $result);
        MatcherAssert::assertThat(
            HasProperty::hasProperty(
                'RealPath',
                realpath($this->absolutFilePath)
            )
        );
    }

    /**
     * @test
     */
    public function buildFromPathReturnsSameObjectsForSameFiles()
    {
        $result = $this->subject->buildFromPath('.' . self::$DiSe . $this->relativeFilePath);
        $result2 = $this->subject->buildFromPath($this->absolutFilePath);

        self::assertSame($result, $result2);
    }

    /**
     * @test
     */
    public function buildFromArrayOfPathsReturnsArrayOfSmartFileInfo()
    {
        $result = $this->subject->buildFromArrayOfPaths(
            [
                '.' . self::$DiSe . $this->relativeFilePath,
                $this->relativeFilePath2,
                $this->absolutFilePath,
            ]
        );

        MatcherAssert::assertThat(
            $result,
            H::allOf(
                H::hasItem(
                    HasProperty::hasProperty(
                        'RealPath',
                        realpath($this->absolutFilePath)
                    )
                ),
                H::hasItem(
                    HasProperty::hasProperty(
                        'RealPath',
                        $this->rootDirectory . self::$DiSe . 'composer.json'
                    )
                ),
                H::arrayWithSize(2)
            )
        );
    }

    /**
     * @test
     */
    public function sanitizeGeneratesSmartFileInfo()
    {
        $forgedFileInfo = new SplFileInfo($this->absolutFilePath);
        $result = $this->subject->sanitize($forgedFileInfo);

        MatcherAssert::assertThat(
            $result,
            H::allOf(
                H::anInstanceOf(SmartFileInfo::class),
                HasProperty::hasProperty('RealPath', $this->absolutFilePath)
            )
        );
    }

    /**
     * @test
     */
    public function sanitizeReturnsSameObjectForSameFiles()
    {
        $forgedFileInfo = new SplFileInfo($this->absolutFilePath);
        $forgedFileInfo2 = new SymfonyFileInfo($this->absolutFilePath, '', '');
        $forgedFileInfo3 = new SmartFileInfo($this->relativeFilePath);

        $result = $this->subject->sanitize($forgedFileInfo);
        $result2 = $this->subject->sanitize($forgedFileInfo2);
        $result3 = $this->subject->sanitize($forgedFileInfo3);

        MatcherAssert::assertThat(
            $result,
            H::allOf(
                H::anInstanceOf(SmartFileInfo::class),
                HasProperty::hasProperty('RealPath', $this->absolutFilePath)
            )
        );

        MatcherAssert::assertThat(
            $result,
            H::allOf(
                H::sameInstance($result2),
                H::sameInstance($result3)
            )
        );
    }

    /**
     * @test
     */
    public function sanatizeArrayReturnsSanatizedSmartFileInfos()
    {
        $result = $this->subject->sanitizeArray(
            [
                new SplFileInfo($this->absolutFilePath),
                new SymfonyFileInfo($this->absolutFilePath, '', ''),
                new SmartFileInfo($this->relativeFilePath2),
            ]
        );

        MatcherAssert::assertThat(
            $result,
            H::allOf(
                H::hasItem(
                    HasProperty::hasProperty(
                        'RealPath',
                        realpath($this->absolutFilePath)
                    )
                ),
                H::hasItem(
                    HasProperty::hasProperty(
                        'RealPath',
                        $this->rootDirectory . self::$DiSe . 'composer.json'
                    )
                ),
                H::arrayWithSize(2)
            )
        );
    }

    /**
     * @test
     */
    public function buildFromArrayOfPathsContinuesWorkEvenIfAPathDoesNotExist()
    {
        $result = $this->subject->buildFromArrayOfPaths(
            [
                $this->absolutFilePath,
                'wurbelschwurbel',
            ]
        );

        MatcherAssert::assertThat(
            $result,
            H::allOf(
                H::hasItem(
                    HasProperty::hasProperty(
                        'RealPath',
                        realpath($this->absolutFilePath)
                    )
                ),
                H::arrayWithSize(1)
            )
        );
    }

    /**
     * @test
     */
    public function buildFromPathThrowsExceptionIfFileDoesNotExist()
    {
        self::expectException(FileNotFoundException::class);
        self::expectExceptionCode(1610034580);
        $this->subject->buildFromPath('asd123weasdyxcasdqwe23');
    }
}
