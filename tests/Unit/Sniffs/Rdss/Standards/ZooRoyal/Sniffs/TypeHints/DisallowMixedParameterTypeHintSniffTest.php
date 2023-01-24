<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Tests\Unit\Sniffs\Rdss\Standards\ZooRoyal\Sniffs\TypeHints;

use Hamcrest\Matchers as H;
use Mockery;
use Mockery\MockInterface;
use PHP_CodeSniffer\Files\File;
use PHPUnit\Framework\TestCase;
use SlevomatCodingStandard\Helpers\DocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\SuppressHelper;
use SlevomatCodingStandard\Helpers\TypeHint;
use Zooroyal\CodingStandard\Sniffs\Rdss\Standards\ZooRoyal\Sniffs\TypeHints\DisallowMixedParameterTypeHintSniff;

use const T_CLOSURE;
use const T_FUNCTION;

class DisallowMixedParameterTypeHintSniffTest extends TestCase
{
    private DisallowMixedParameterTypeHintSniff $subject;
    private string $subjectName = 'Zooroyal.TypeHints.DisallowMixedParameterTypeHint';

    protected function setUp(): void
    {
        $this->subject = new DisallowMixedParameterTypeHintSniff();
    }

    protected function assertPostConditions(): void
    {
        Mockery::close();
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     * @dataProvider         processAddsNoErrorIfTypeHintNotMixedDataProvider
     *
     * @param array<array<string,int|string>> $forgedTokens
     */
    public function processAddsNoErrorIfTypeHintNotMixed(
        array $forgedTokens,
        string $forgedFQN,
        string $forgedTypeHint2Name,
        string $forgedErrorMessage,
    ): void {
        $forgedFunctionPointer = 1;
        $forgedTypeLabel = 'Function';
        $mockedFile = Mockery::mock(File::class);
        $forgedTypeHint1Name = 'firstName';
        $mockedTypeHint1 = Mockery::mock(TypeHint::class);
        $mockedTypeHint2 = Mockery::mock(TypeHint::class);
        $mockedTypeHints = [$forgedTypeHint1Name => $mockedTypeHint1, $forgedTypeHint2Name => $mockedTypeHint2, 'blub' => null];

        /** @var MockInterface|FunctionHelper $mockedFunctionHelper */
        $mockedFunctionHelper = $this->prepareForGuardClauses($mockedFile, $forgedFunctionPointer, $mockedTypeHints);

        $mockedFile->expects()->getTokens()->andReturn($forgedTokens);
        $mockedTypeHint1->expects()->getTypeHint()->andReturn('string');
        $mockedTypeHint2->expects()->getTypeHint()->andReturn('mixed');

        $mockedFunctionHelper->expects()->getTypeLabel($mockedFile, $forgedFunctionPointer)->andReturn(
            $forgedTypeLabel,
        );
        $mockedFunctionHelper->expects()->getFullyQualifiedName($mockedFile, $forgedFunctionPointer)
            ->andReturn($forgedFQN);

        $mockedFile->expects()->addError(
            H::containsString($forgedErrorMessage),
            $forgedFunctionPointer,
            'MixedParameterTypeHintUsed',
        );

        $this->subject->process($mockedFile, $forgedFunctionPointer);
    }

    /** @return array<string,array<string,array<array<string,int|string>>|string>> */
    public function processAddsNoErrorIfTypeHintNotMixedDataProvider(): array
    {
        return [
            'function' => [
                'forgedTokens' => [[], ['code' => T_FUNCTION]],
                'forgedFQN' => 'My\NameSpace\Name',
                'forgedTypeHint2Name' => 'secondName',
                'forgedErrorMessage' => 'Function My\NameSpace\Name() uses "mixed" type hint for parameter secondName, '
                    . 'which is disallowed',
            ],
            'closure' => [
                'forgedTokens' => [[], ['code' => T_CLOSURE]],
                'forgedFQN' => 'My\NameSpace\Name',
                'forgedTypeHint2Name' => 'arglwargl',
                'forgedErrorMessage' => 'closure() uses "mixed" type hint for parameter arglwargl, which is disallowed',
            ],
        ];
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function processEndsEarlyIfHasInheritDoc(): void
    {
        $mockedFile = Mockery::mock(File::class);
        $forgedFunctionPointer = 0;

        /** @var MockInterface|SuppressHelper $mockedSuppressHelper */
        $mockedSuppressHelper = Mockery::mock('overload:' . SuppressHelper::class);
        $mockedSuppressHelper->expects()->isSniffSuppressed($mockedFile, $forgedFunctionPointer, $this->subjectName)
            ->andReturn(false);

        /** @var MockInterface|DocCommentHelper $mockedDocCommentHelper */
        $mockedDocCommentHelper = Mockery::mock('overload:' . DocCommentHelper::class);
        $mockedDocCommentHelper->expects()->hasInheritdocAnnotation($mockedFile, $forgedFunctionPointer)->andReturn(
            true,
        );

        $this->subject->process($mockedFile, $forgedFunctionPointer);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function processEndsEarlyIfHasNoTypeHints(): void
    {
        $mockedFile = Mockery::mock(File::class);
        $forgedFunctionPointer = 0;

        /** @var MockInterface|SuppressHelper $mockedSuppressHelper */
        $mockedSuppressHelper = Mockery::mock('overload:' . SuppressHelper::class);
        $mockedSuppressHelper->expects()->isSniffSuppressed($mockedFile, $forgedFunctionPointer, $this->subjectName)
            ->andReturn(false);

        /** @var MockInterface|DocCommentHelper $mockedDocCommentHelper */
        $mockedDocCommentHelper = Mockery::mock('overload:' . DocCommentHelper::class);
        $mockedDocCommentHelper->expects()->hasInheritdocAnnotation($mockedFile, $forgedFunctionPointer)
            ->andReturn(false);

        /** @var MockInterface|FunctionHelper $mockedFunctionHelper */
        $mockedFunctionHelper = Mockery::mock('overload:' . FunctionHelper::class);
        $mockedFunctionHelper->expects()->getParametersTypeHints($mockedFile, $forgedFunctionPointer)
            ->andReturn([]);

        $this->subject->process($mockedFile, $forgedFunctionPointer);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function processEndsEarlyIfSniffSuppressed(): void
    {
        $mockedFile = Mockery::mock(File::class);
        $forgedFunctionPointer = 0;

        /** @var MockInterface|SuppressHelper $mockedSuppressHelper */
        $mockedSuppressHelper = Mockery::mock('overload:' . SuppressHelper::class);
        $mockedSuppressHelper->expects()->isSniffSuppressed($mockedFile, $forgedFunctionPointer, $this->subjectName)
            ->andReturn(true);

        $this->subject->process($mockedFile, $forgedFunctionPointer);
    }

    /**
     * @test
     */
    public function registerReturnsTokens(): void
    {
        $expectedResult = [T_FUNCTION, T_CLOSURE,];

        $result = $this->subject->register();
        self::assertSame($expectedResult, $result);
    }

    /** @param array<TypeHint> $mockedTypeHints */
    private function prepareForGuardClauses(
        File|MockInterface $mockedFile,
        int $forgedFunctionPointer,
        array $mockedTypeHints,
    ): MockInterface|FunctionHelper {
        Mockery::mock('overload:' . SuppressHelper::class)->shouldIgnoreMissing();
        Mockery::mock('overload:' . DocCommentHelper::class)->shouldIgnoreMissing();

        /** @var MockInterface|FunctionHelper $mockedFunctionHelper */
        $mockedFunctionHelper = Mockery::mock('overload:' . FunctionHelper::class);
        $mockedFunctionHelper->expects()->getParametersTypeHints($mockedFile, $forgedFunctionPointer)
            ->andReturn($mockedTypeHints);

        return $mockedFunctionHelper;
    }
}
