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
    /** @var string */
    protected $blacklistToken = '.dontSniffPHP';
    /** @var string[] */
    protected $allowedFileEndings = ['.php'];
    /** @var string */
    protected $blacklistPrefix = '';
    /** @var string */
    protected $blacklistGlue = ',';
    /** @var string */
    protected $whitelistGlue = ' ';

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        $phpCodeSnifferConfig = $this->environment->getPackageDirectory()->getRealPath() . '/config/phpcs/ZooRoyal/ruleset.xml';
        $vendorPath = $this->environment->getVendorPath()->getRealPath();
        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $sniffWhitelistCommand = 'php ' . $vendorPath . '/bin/phpcs -s --extensions=php --standard='
            . $phpCodeSnifferConfig . ' %1$s';
        $cbfWhitelistCommand = 'php ' . $vendorPath . '/bin/phpcbf --extensions=php --standard='
            . $phpCodeSnifferConfig . ' %1$s';
        $sniffBlacklistCommand = 'php ' . $vendorPath
            . '/bin/phpcs -s --extensions=php --standard=' . $phpCodeSnifferConfig . ' --ignore=%1$s ' . $rootDirectory;
        $cbfBlacklistCommand = 'php ' . $vendorPath
            . '/bin/phpcbf --extensions=php --standard=' . $phpCodeSnifferConfig . ' --ignore=%1$s ' . $rootDirectory;

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
    public function writeViolationsToOutput($targetBranch = ''): ?int
    {
        $tool = 'PHPCS';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $exitCode = $this->runTool($targetBranch, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function fixViolations($targetBranch = ''): ?int
    {
        $tool = 'PHPCBF';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Fix all Files';
        $diffMessage = $prefix . 'Fix Files in diff';

        $exitCode = $this->runTool($targetBranch, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }
}
