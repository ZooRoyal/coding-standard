<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPStan;

use ComposerLocator;
use PHPStan\DependencyInjection\NeonAdapter;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Zooroyal\CodingStandard\CommandLine\Library\Environment;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class PHPStanConfigGenerator
{
    private const TOOL_FUNCTIONS_FILE_MAPPING
        = [
            'hamcrest/hamcrest-php' => '/hamcrest/Hamcrest.php',
            'sebastianknott/hamcrest-object-accessor' => '/src/functions.php',
            'mockery/mockery' => '/library/helpers.php',
        ];
    private const DEVOPS_AUTOMATION_FUNCTIONS
        = [
            '/devops/automation/deployer/Functions/databaseLocal.php',
        ];
    private const STATIC_DIRECTORIES_TO_SCAN
        = [
            '/Plugins',
            '/custom/plugins',
            '/custom/project',
        ];
    private NeonAdapter $neonAdapter;
    private Filesystem $filesystem;
    private string $phpStanConfigPath;
    private Environment $environment;

    public function __construct(
        NeonAdapter $neonAdapter,
        Filesystem $filesystem,
        Environment $environment
    ) {
        $this->neonAdapter = $neonAdapter;
        $this->filesystem = $filesystem;
        $this->environment = $environment;
        $this->phpStanConfigPath = $environment->getPackageDirectory()->getRealPath() . '/config/phpstan/phpstan.neon';
    }

    /**
     * Exposes the path where the config file will be found to the world.
     */
    public function getConfigPath(): string
    {
        return $this->phpStanConfigPath;
    }

    /**
     * Writes a custom config file just in time for PHPStan to read.
     *
     * @param array<EnhancedFileInfo> $exclusionList
     */
    public function writeConfigFile(OutputInterface $output, array $exclusionList): void
    {
        $output->writeln(
            '<info>Writing new PHPStan configuration.</info>' . PHP_EOL,
            OutputInterface::VERBOSITY_VERBOSE
        );

        $configValues = $this->generateConfig($output, $exclusionList);

        $onTheFlyConfig = $this->neonAdapter->dump($configValues);
        $this->filesystem->dumpFile($this->phpStanConfigPath, $onTheFlyConfig);
    }

    /**
     * Adds function bootstraps to PHPStan config so imported functions won't show up as unknown.
     *
     * @param array<EnhancedFileInfo> $exclusionList
     *
     * @return array<string,array<int|string,array<int,string>|string>>>
     */
    private function generateConfig(OutputInterface $output, array $exclusionList): array
    {
        $configValues = ['includes' => [$this->phpStanConfigPath . '.dist']];
        $configValues = $this->addFunctionsFiles($configValues, $output);
        $configValues = $this->addExcludedFiles($configValues, $exclusionList);
        $configValues = $this->addStaticDirectoriesToScan($configValues);

        return $configValues;
    }

    /**
     * Adds the functions files of several composer packages to the PHPStan Autoloader. PHPStan will complain about
     * unknown functions if this should fail.
     *
     * @param array<string,array<string>> $configValues
     *
     * @return array<string,array<string|int,string|array<string>>>
     */
    private function addFunctionsFiles(array $configValues, OutputInterface $output): array
    {
        foreach (self::TOOL_FUNCTIONS_FILE_MAPPING as $tool => $functionsFile) {
            try {
                $toolPath = ComposerLocator::getPath($tool);
                $configValues['parameters']['bootstrapFiles'][] = $toolPath . $functionsFile;
            } catch (RuntimeException $exception) {
                $output->writeln(
                    '<info>' . $tool . ' not found. Skip loading ' . $functionsFile . '</info>',
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }

        $configValues = $this->addLocalFunctionsFiles($configValues);
        return $configValues;
    }

    /**
     * Adds the list of files to be excluded to the config.
     *
     * @param array<string,array<string|int,string|array<string>>> $configValues
     * @param array<EnhancedFileInfo> $exclusionList
     *
     * @return array<string,array<string|int,string|array<string>>>
     */
    private function addExcludedFiles(array $configValues, array $exclusionList): array
    {
        $directoryExcludedFilesStrings = array_map(
            static fn(EnhancedFileInfo $file) => $file->getRealPath(),
            $exclusionList
        );
        $configValues['parameters']['excludes_analyse'] = $directoryExcludedFilesStrings;
        return $configValues;
    }

    /**
     * Adds the list of static folders to scan if they exist.
     *
     * @param array<string,array<string|int,string|array<string>>> $configValues
     *
     * @return array<string,array<string|int,string|array<string>>>
     */
    private function addStaticDirectoriesToScan(array $configValues): array
    {
        foreach (self::STATIC_DIRECTORIES_TO_SCAN as $directory) {
            $absolutePath = $this->environment->getRootDirectory()->getRealPath() . $directory;
            if (!$this->filesystem->exists($absolutePath)) {
                continue;
            }

            $configValues['parameters']['scanDirectories'][] = $absolutePath;
        }
        return $configValues;
    }

    /**
     * Adds list of local bootstrap-files for devops-automation tasks in config
     *
     * @param array<string,array<string|int,string|array<string>>> $configValues
     *
     * @return array<string,array<string|int,string|array<string>>>
     */
    private function addLocalFunctionsFiles(array $configValues): array
    {
        foreach (self::DEVOPS_AUTOMATION_FUNCTIONS as $devopsFunctionFile) {
            $devopsAutomationPath = $this->environment->getRootDirectory()->getRealPath() .
                $devopsFunctionFile;
            if ($this->filesystem->exists($devopsAutomationPath)) {
                $configValues['parameters']['bootstrapFiles'][] = $devopsAutomationPath;
            }
        }
        return $configValues;
    }
}
