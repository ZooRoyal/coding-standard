<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\PHPStanConfigGenerator;

class PHPStanAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface
{
    /** @var string */
    protected $blacklistToken = '.dontStanPHP';
    /** @var string */
    protected $filter = '.php';
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';
    /** @var PHPStanConfigGenerator */
    private $phpstanConfigGenerator;

    /**
     * PHPStanAdapter constructor.
     *
     * @param Environment $environment
     * @param OutputInterface $output
     * @param GenericCommandRunner $genericCommandRunner
     * @param TerminalCommandFinder $terminalCommandFinder
     * @param PHPStanConfigGenerator $phpstanConfigGenerator
     */
    public function __construct(
        Environment $environment,
        OutputInterface $output,
        GenericCommandRunner $genericCommandRunner,
        TerminalCommandFinder $terminalCommandFinder,
        PHPStanConfigGenerator $phpstanConfigGenerator
    ) {
        $this->phpstanConfigGenerator = $phpstanConfigGenerator;
        parent::__construct($environment, $output, $genericCommandRunner, $terminalCommandFinder);
    }

    protected function init()
    {
        $rootDirectory = $this->environment->getRootDirectory();
        $phpstanConfig = $this->environment->getPackageDirectory() . '/config/phpstan/phpstan.neon';

        $parameters = $this->phpstanConfigGenerator->addConfigParameters($this->blacklistToken, $rootDirectory);
        $onTheFlyConfig = $this->phpstanConfigGenerator->generateConfig($parameters);
        $this->phpstanConfigGenerator->writeConfig($phpstanConfig . '.dist', $onTheFlyConfig);

        $this->commands['PHPStanBL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan analyse --no-progress ' .
                $rootDirectory . ' -c ' . $phpstanConfig;

        $this->commands['PHPStanWL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan analyse --no-progress -c '
                . $phpstanConfig . ' %1$s';
    }

    /**
     * {@inheritdoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false) : ?int
    {
        $toolShortName = 'PHPStan';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        return $this->runTool($targetBranch, $processIsolation, $fullMessage, $toolShortName, $diffMessage);
    }
}
