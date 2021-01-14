<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use DI\Annotation\Injectable;

/**
 * Class PHPCodeSnifferAdapter
 *
 * @Injectable(lazy=true)
 */
class PHPCodeSnifferAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface, FixerSupportInterface
{
    protected string $blacklistToken = '.dontSniffPHP';
    /** @var string[] */
    protected array $allowedFileEndings = ['.php'];
    protected string $blacklistPrefix = '';
    protected string $blacklistGlue = ',';
    protected string $whitelistGlue = ' ';
    protected bool $escape = false;

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        $phpCodeSnifferConfig = $this->environment->getPackageDirectory()->getRealPath() . '/config/phpcs/ZooRoyal/ruleset.xml';
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $sniffWhitelistCommand = 'php ' . $rootDirectory . '/vendor/bin/phpcs -s --extensions=php --standard='
            . $phpCodeSnifferConfig . ' %1$s';
        $cbfWhitelistCommand = 'php ' . $rootDirectory . '/vendor/bin/phpcbf --extensions=php --standard='
            . $phpCodeSnifferConfig . ' %1$s';
        $sniffBlacklistCommand = 'php ' . $rootDirectory
            . '/vendor/bin/phpcs -s --extensions=php --standard=' . $phpCodeSnifferConfig . ' --ignore=%1$s ' . $rootDirectory;
        $cbfBlacklistCommand = 'php ' . $rootDirectory
            . '/vendor/bin/phpcbf --extensions=php --standard=' . $phpCodeSnifferConfig . ' --ignore=%1$s ' . $rootDirectory;

        $this->commands = [
            'PHPCSWL' => $sniffWhitelistCommand,
            'PHPCBFWL' => $cbfWhitelistCommand,
            'PHPCSBL' => $sniffBlacklistCommand,
            'PHPCBFBL' => $cbfBlacklistCommand,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false): ?int
    {
        $tool = 'PHPCS';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function fixViolations($targetBranch = '', bool $processIsolation = false): ?int
    {
        $tool = 'PHPCBF';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Fix all Files';
        $diffMessage = $prefix . 'Fix Files in diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }
}
