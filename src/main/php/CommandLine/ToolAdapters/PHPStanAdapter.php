<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\PHPStandConfigGenerator;

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
    /** @var bool */
    protected $blackListArgument = false;
    /** @var BlacklistFactory */
    private $blacklistFactory;
    /** @var PHPStandConfigGenerator */
    private $phpstanConfigGenerator;

    public function __construct(
        Environment $environment,
        OutputInterface $output,
        GenericCommandRunner $genericCommandRunner,
        TerminalCommandFinder $terminalCommandFinder,
        BlacklistFactory $blacklistFactory,
        PHPStandConfigGenerator $phpstanConfigGenerator
    ) {
        $this->phpstanConfigGenerator = $phpstanConfigGenerator;
        $this->blacklistFactory = $blacklistFactory;
        parent::__construct($environment, $output, $genericCommandRunner, $terminalCommandFinder);
    }


    /**
     * {@inheritDoc}
     */
    protected function init()
    {
        $rootDirectory = $this->environment->getRootDirectory();
        $phpstanConfig = $this->environment->getPackageDirectory() . '/config/phpstan/phpstan.neon';

        $blackListfiles = $this->blacklistFactory->build($this->blacklistToken);
        $diretoryBlackListfiles = [];
        foreach ($blackListfiles as $file) {
            $diretoryBlackListfiles[] =  $rootDirectory.'/'.$file;
        }
        $configfileData = ['parameters' => ['excludes_analyse' => $diretoryBlackListfiles]];
        $onTheFlyConfig = $this->phpstanConfigGenerator->generateConfig($configfileData);
        $this->phpstanConfigGenerator->writeConfig($phpstanConfig.'.dist', $onTheFlyConfig);

        $this->commands['PHPStanBL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan analyse --no-progress '.
                $rootDirectory .' -c '.$phpstanConfig;

        $this->commands['PHPStanWL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan analyse --no-progress -c '
            .$phpstanConfig. ' %1$s';
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
