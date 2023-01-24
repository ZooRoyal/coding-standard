<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\Sniffs\Rdss\Standards\ZooRoyal\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\DocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\SuppressHelper;
use SlevomatCodingStandard\Helpers\TypeHint;

use function Safe\sprintf;

use const T_CLOSURE;
use const T_FUNCTION;

class DisallowMixedParameterTypeHintSniff
{
    private const NAME = 'Zooroyal.TypeHints.DisallowMixedParameterTypeHint';
    private const ERROR_CODE = 'MixedParameterTypeHintUsed';
    private const INVALID_TYPE_HINT = 'mixed';
    private const ERROR_MESSAGE_FUNCTION
        = 'uses "' . self::INVALID_TYPE_HINT . '" type hint for parameter %s, which is disallowed';

    public function process(File $phpcsFile, int $functionPointer): void
    {
        if (SuppressHelper::isSniffSuppressed($phpcsFile, $functionPointer, self::NAME)) {
            return;
        }
        if (DocCommentHelper::hasInheritdocAnnotation($phpcsFile, $functionPointer)) {
            return;
        }
        $parametersTypeHints = FunctionHelper::getParametersTypeHints($phpcsFile, $functionPointer);
        if (empty($parametersTypeHints)) {
            return;
        }
        /** @var array<string|int> $token */
        $token = $phpcsFile->getTokens()[$functionPointer];
        $this->checkTypeHints(
            $phpcsFile,
            $functionPointer,
            $parametersTypeHints,
            (int) $token['code'],
        );
    }

    /** @return array<int|string> */
    public function register(): array
    {
        return [
            T_FUNCTION,
            T_CLOSURE,
        ];
    }

    /**
     * Check the parameter type hints
     *
     * @param array<TypeHint> $parametersTypeHints
     */
    private function checkTypeHints(
        File $phpcsFile,
        int $functionPointer,
        array $parametersTypeHints,
        int $token,
    ): void {
        foreach (array_filter($parametersTypeHints) as $parameterName => $parametersTypeHint) {
            if ($parametersTypeHint->getTypeHint() === self::INVALID_TYPE_HINT) {
                $phpcsFile->addError(
                    $this->getErrorMessage($phpcsFile, $functionPointer, $parameterName, $token),
                    $functionPointer,
                    self::ERROR_CODE,
                );
            }
        }
    }

    private function getErrorMessage(
        File $phpcsFile,
        int $functionPointer,
        string $parameterName,
        int $token,
    ): string {
        return $token === T_FUNCTION
            ? sprintf(
                '%s %s() ' . self::ERROR_MESSAGE_FUNCTION,
                FunctionHelper::getTypeLabel($phpcsFile, $functionPointer),
                FunctionHelper::getFullyQualifiedName($phpcsFile, $functionPointer),
                $parameterName,
            )
            : sprintf(
                'closure() ' . self::ERROR_MESSAGE_FUNCTION,
                $parameterName,
            );
    }
}
