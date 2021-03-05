<?php

namespace Zooroyal\CodingStandard\Tests\Functional\CommandLine\Factories;

use ComposerLocator;
use Hamcrest\MatcherAssert;
use Hamcrest\Matchers as H;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SebastianKnott\HamcrestObjectAccessor\HasProperty;
use Zooroyal\CodingStandard\CommandLine\Factories\ContainerFactory;
use Zooroyal\CodingStandard\CommandLine\Factories\EnhancedFileInfoFactory;
use function Safe\realpath;

class EnhancedFileInfoFactoryTest extends TestCase
{
    private static $DiSe = DIRECTORY_SEPARATOR;
    private EnhancedFileInfoFactory $subject;
    private string $absolutFilePath;
    private string $relativeFilePath;
    private string $relativeFilePath2;
    private string $rootDirectory;

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

        $this->rootDirectory = realpath(ComposerLocator::getRootPath());
        $this->absolutFilePath = $this->rootDirectory . self::$DiSe . $this->relativeFilePath;

        $this->subject = $container->get(EnhancedFileInfoFactory::class);
    }

    /**
     * @test
     */
    public function buildFromPathToDirectoryCreatesFromPath(): void
    {
        $this->subject->buildFromPath(__DIR__ . '/..');
    }

    /**
     * @test
     */
    public function buildFromPathCreatesFromAbsolutPath(): void
    {
        $this->subject->buildFromPath($this->absolutFilePath);
    }

    /**
     * @test
     */
    public function buildFromNonCannonicalPath(): void
    {
        $result = $this->subject->buildFromPath(
            $this->rootDirectory
            . self::$DiSe
            . 'asdasdasd'
            . self::$DiSe
            . '..'
            . self::$DiSe
            . $this->relativeFilePath
        );

        self::assertSame($this->absolutFilePath, $result->getRealPath());
    }

    /**
     * @test
     */
    public function buildFromPathCreatesRelativToComposerPath(): void
    {
        $result = $this->subject->buildFromPath('.' . self::$DiSe . $this->relativeFilePath);

        MatcherAssert::assertThat(
            $result,
            HasProperty::hasProperty(
                'RealPath',
                realpath($this->absolutFilePath)
            )
        );
    }

    /**
     * @test
     */
    public function buildFromPathReturnsSameObjectsForSameFiles(): void
    {
        $result = $this->subject->buildFromPath('.' . self::$DiSe . $this->relativeFilePath);
        $result2 = $this->subject->buildFromPath($this->absolutFilePath);

        self::assertSame($result, $result2);
    }

    /**
     * @test
     */
    public function buildFromArrayOfPathsReturnsArrayOfEnhancedFileInfo(): void
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
    public function buildFromArrayOfPathsContinuesWorkEvenIfAPathDoesNotExist(): void
    {
        $result = $this->subject->buildFromArrayOfPaths(
            [
                $this->absolutFilePath,
                'asdasd',
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
    public function buildFromPathThrowsExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('asd123weasdyxcasdqwe23 could not be found.');
        $this->expectExceptionCode(1610034580);
        $this->subject->buildFromPath('asd123weasdyxcasdqwe23');
    }
}
