<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\PHPCodeSniffer\Standards\ZooRoyal\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\SuppressHelper;
use SlevomatCodingStandard\Helpers\TypeHint;
use function sprintf;
use const T_CLOSURE;
use const T_FUNCTION;

class DisallowMixedReturnTypeHintSniff
{
	private const NAME = 'Zooroyal.TypeHints.DisallowMixedReturnTypeHint';

    private const ERROR_CODE = 'MixedParameterTypeHintUsed';

    private const INVALID_TYPE_HINT = 'mixed';

	/**
	 * @return array<int, (int|string)>
	 */
	public function register(): array
	{
		return [
			T_FUNCTION,
			T_CLOSURE,
		];
	}

	public function process(File $phpcsFile, int $pointer): void
	{
		if (SuppressHelper::isSniffSuppressed($phpcsFile, $pointer, self::NAME)) {
			return;
		}
		$token = $phpcsFile->getTokens()[$pointer];
        /** @var int|string $tokenCode */
        $tokenCode = $token['code'];
        $returnTypeHint = $this->getReturnTypeHintByTokenCode($phpcsFile, $pointer, $tokenCode);
        if ($returnTypeHint === null) {
            return;
        }
		if ($tokenCode === T_FUNCTION) {
			$this->checkFunctionTypeHint($phpcsFile, $pointer, $returnTypeHint);
		} elseif ($tokenCode === T_CLOSURE) {
			$this->checkClosureTypeHint($phpcsFile, $pointer, $returnTypeHint);
		}
	}

    private function getReturnTypeHintByTokenCode(File $phpcsFile, int $pointer, int|string $tokenCode): ?TypeHint
    {
        return in_array($tokenCode, $this->register(), true)
            ? FunctionHelper::findReturnTypeHint($phpcsFile, $pointer)
            : null;
    }

	private function checkFunctionTypeHint(
		File $phpcsFile,
		int $functionPointer,
		TypeHint $returnTypeHint,
	): void
	{
        if ($returnTypeHint->getTypeHint() !== self::INVALID_TYPE_HINT) {
            return;
        }
        $phpcsFile->addError(
            sprintf(
                '%s %s() uses "mixed" return type hint which is disallowed',
                FunctionHelper::getTypeLabel($phpcsFile, $functionPointer),
                FunctionHelper::getFullyQualifiedName($phpcsFile, $functionPointer),
            ),
            $functionPointer,
            self::ERROR_CODE);
	}

	private function checkClosureTypeHint(File $phpcsFile, int $closurePointer, TypeHint $returnTypeHint,): void
    {
        if ($returnTypeHint->getTypeHint() !== self::INVALID_TYPE_HINT) {
            return;
        }
        $phpcsFile->addError(
            'closure() uses "mixed" return type hint which is disallowed',
            $closurePointer,
            self::ERROR_CODE);
    }
}
