<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use PHPStan\DependencyInjection\NeonAdapter;
use PHPStan\File\CouldNotWriteFileException;
use PHPStan\File\FileWriter;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;

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
    /** @var NeonAdapter */
    private $neonAdapter;
    /** @var FileWriter */
    private $fileWriter;

    public function __construct(
        Environment $environment,
        OutputInterface $output,
        GenericCommandRunner $genericCommandRunner,
        TerminalCommandFinder $terminalCommandFinder,
        BlacklistFactory $blacklistFactory,
        NeonAdapter $neonAdapter,
        FileWriter $fileWriter
    ) {
        $this->fileWriter = $fileWriter;
        $this->blacklistFactory = $blacklistFactory;
        $this->neonAdapter = $neonAdapter;
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
        $onTheFlyConfig = $this->neonAdapter->dump($configfileData);

        try {
            $this->fileWriter->write($phpstanConfig.'.dist', $onTheFlyConfig);
            $this->commands['PHPStanBL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan analyse --no-progress '.
                $rootDirectory .' -c '.$phpstanConfig;
        } catch (CouldNotWriteFileException $exception) {
            $this->commands['PHPStanBL'] = '';
        }

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
