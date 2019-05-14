<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;

class PHPCodeSnifferAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface, FixerSupportInterface
{
    /** @var string */
    protected $blacklistToken = '.dontSniffPHP';
    /** @var string */
    protected $filter = '.php';
    /** @var string */
    protected $blacklistPrefix = '';
    /** @var string */
    protected $blacklistGlue = ',';
    /** @var string */
    protected $whitelistGlue = ' ';
    /** @var bool */
    protected $escape = true;

    /**
     * PHPCodeSnifferAdapter constructor.
     *
     * @param Environment          $environment
     * @param OutputInterface      $output
     * @param GenericCommandRunner $genericCommandRunner
     */
    public function __construct(
        Environment $environment,
        OutputInterface $output,
        GenericCommandRunner $genericCommandRunner
    ) {
        $this->environment = $environment;
        $this->output = $output;
        $this->genericCommandRunner = $genericCommandRunner;

        $phpCodeSnifferConfig = $environment->getPackageDirectory() . '/config/phpcs/ZooRoyal/ruleset.xml';
        $rootDirectory = $environment->getRootDirectory();

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
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false)
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
    public function fixViolations($targetBranch = '', bool $processIsolation = false)
    {
        $tool = 'PHPCBF';
        $prefix = $tool . ' : ';
        $fullMessage = $prefix . 'Fix all Files';
        $diffMessage = $prefix . 'Fix Files in diff';

        $exitCode = $this->runTool($targetBranch, $processIsolation, $fullMessage, $tool, $diffMessage);

        return $exitCode;
    }
}
