<?php

namespace Zooroyal\CodingStandard\CommandLine\ToolAdapters;

use ComposerLocator;
use DI\Annotation\Injectable;
use RuntimeException;
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
    /** @var string */
    protected $blacklistToken = '.dontStanPHP';
    /** @var string[] */
    protected $allowedFileEndings = ['.php'];
    /** @var string */
    protected $blacklistGlue = ' ';
    /** @var string */
    protected $whitelistGlue = ' ';
    /** @var PHPStanConfigGenerator */
    private $phpstanConfigGenerator;
    /** @var array<string,string> */
    private array $toolFunctionsFileMapping
        = [
            'hamcrest/hamcrest-php' => '/hamcrest/Hamcrest.php',
            'sebastianknott/hamcrest-object-accessor' => '/src/functions.php',
            'mockery/mockery' => '/library/helpers.php',
        ];
    private string $rootDirectory;
    private string $phpstanConfigPath;
    private string $vendorPath;

    /**
     * PHPStanAdapter constructor.
     *
     * @param Environment            $environment
     * @param OutputInterface        $output
     * @param GenericCommandRunner   $genericCommandRunner
     * @param TerminalCommandFinder  $terminalCommandFinder
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
        $this->vendorPath = $environment->getVendorPath();
        $this->rootDirectory = $environment->getRootDirectory();
        $this->phpstanConfigPath = $environment->getPackageDirectory() . '/config/phpstan/phpstan.neon';
        parent::__construct($environment, $output, $genericCommandRunner, $terminalCommandFinder);
    }

    protected function init(): void
    {
        $this->commands['PHPStanBL'] = 'php ' . $this->vendorPath . '/bin/phpstan analyse --no-progress '
            . '--error-format=github ' . $this->rootDirectory . ' -c ' . $this->phpstanConfigPath;

        $this->commands['PHPStanWL'] = 'php ' . $this->vendorPath . '/bin/phpstan analyse --no-progress '
            . '--error-format=github ' . '-c ' . $this->phpstanConfigPath . ' %1$s';
    }

    /**
     * {@inheritdoc}
     */
    public function writeViolationsToOutput($targetBranch = ''): ?int
    {
        $toolShortName = 'PHPStan';
        $prefix = $toolShortName . ' : ';
        $fullMessage = $prefix . 'Running full check';
        $diffMessage = $prefix . 'Running check on diff';

        $this->writeConfigFile();

        return $this->runTool($targetBranch, $fullMessage, $toolShortName, $diffMessage);
    }

    /**
     * Writes a custom config file just in time for PHPStan to read.
     */
    private function writeConfigFile(): void
    {
        $additionalConfigValues = ['includes' => [$this->phpstanConfigPath . '.dist']];
        $additionalConfigValues = $this->addFunctionFilesToBootstrap($additionalConfigValues);

        $parameters = $this->phpstanConfigGenerator->addConfigParameters(
            $this->blacklistToken,
            $this->rootDirectory,
            $additionalConfigValues
        );
        $onTheFlyConfig = $this->phpstanConfigGenerator->generateConfig($parameters);
        $this->phpstanConfigGenerator->writeConfig($this->phpstanConfigPath, $onTheFlyConfig);
    }

    /**
     * Adds function bootstraps to PHPStan config so imported functions won't show up as unknown.
     *
     * @param array $additionalConfigValues
     *
     * @return array<array<string,string>>
     */
    private function addFunctionFilesToBootstrap(array $additionalConfigValues): array
    {
        foreach ($this->toolFunctionsFileMapping as $tool => $functionsFile) {
            try {
                $toolPath = ComposerLocator::getPath($tool);
                $additionalConfigValues['parameters']['bootstrapFiles'][] = $toolPath . $functionsFile;
            } catch (RuntimeException $exception) {
                $this->output->writeln(
                    '<info>' . $tool . ' not found. Skip loading ' . $functionsFile . '</info>',
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }
        return $additionalConfigValues;
    }
}
