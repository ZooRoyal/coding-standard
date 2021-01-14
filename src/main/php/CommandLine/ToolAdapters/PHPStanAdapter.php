<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use DI\Annotation\Injectable;
use Symfony\Component\Console\Output\OutputInterface;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\Library\GenericCommandRunner;
use Zooroyal\CodingStandard\CommandLine\Library\TerminalCommandFinder;
use Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators\PHPStanConfigGenerator;

/**
 * Class PHPStanAdapter
 *
 * @Injectable(lazy=true)
 */
class PHPStanAdapter extends AbstractBlackAndWhitelistAdapter implements ToolAdapterInterface
{
    protected string $blacklistToken = '.dontStanPHP';
    /** @var string[] */
    protected array $allowedFileEndings = ['.php'];
    protected string $blacklistGlue = ' ';
    protected string $whitelistGlue = ' ';
    private PHPStanConfigGenerator $phpstanConfigGenerator;

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

    /**
     * {@inheritdoc}
     */
    public function writeViolationsToOutput($targetBranch = '', bool $processIsolation = false): ?int
    {
        $toolShortName = 'PHPStan';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        return $this->runTool($targetBranch, $processIsolation, $fullMessage, $toolShortName, $diffMessage);
    }

    protected function init(): void
    {
        $phpstanConfig = $this->environment->getPackageDirectory()->getRealPath() . '/config/phpstan/phpstan.neon';

        $parameters = $this->phpstanConfigGenerator->addConfigParameters(
            $this->blacklistToken,
            $this->environment->getRootDirectory(),
            ['includes' => [$phpstanConfig . '.dist']]
        );
        $onTheFlyConfig = $this->phpstanConfigGenerator->generateConfig($parameters);
        $this->phpstanConfigGenerator->writeConfig($phpstanConfig, $onTheFlyConfig);

        $rootDirectory = $this->environment->getRootDirectory()->getRealPath();

        $this->commands['PHPStanBL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan analyse --no-progress '
            . '--error-format=github ' . $rootDirectory . ' -c ' . $phpstanConfig;

        $this->commands['PHPStanWL'] = 'php ' . $rootDirectory . '/vendor/bin/phpstan analyse --no-progress '
            . '--error-format=github ' . '-c ' . $phpstanConfig . ' %1$s';
    }
}
