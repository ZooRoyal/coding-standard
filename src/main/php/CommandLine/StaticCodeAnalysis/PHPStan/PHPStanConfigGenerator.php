<?php

declare(strict_types=1);

namespace Zooroyal\CodingStandard\CommandLine\StaticCodeAnalysis\PHPStan;

use ComposerLocator;
use PHPStan\DependencyInjection\NeonAdapter;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Zooroyal\CodingStandard\CommandLine\EnhancedFileInfo\EnhancedFileInfo;
use Zooroyal\CodingStandard\CommandLine\Environment\Environment;

class PHPStanConfigGenerator
{
    private const TOOL_FUNCTIONS_FILE_MAPPING
        = [
            'hamcrest/hamcrest-php' => ['/hamcrest/Hamcrest.php'],
            'sebastianknott/hamcrest-object-accessor' => ['/src/functions.php'],
            'mockery/mockery' => ['/library/helpers.php'],
        ];
    private const STATIC_DIRECTORIES_TO_SCAN
        = [
            '/Plugins',
            '/custom/plugins',
            '/custom/project',
            '/vendor',
        ];

    private string $phpStanConfigPath;

    public function __construct(
        private readonly NeonAdapter $neonAdapter,
        private readonly Filesystem $filesystem,
        private readonly Environment $environment,
    ) {
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
            OutputInterface::VERBOSITY_VERBOSE,
        );

        $configValues = $this->generateConfig($output, $exclusionList);

        /** @phpstan-ignore-next-line */
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
     * @return array<string,array<string|int,array<string>>>
     */
    private function addFunctionsFiles(array $configValues, OutputInterface $output): array
    {
        foreach (self::TOOL_FUNCTIONS_FILE_MAPPING as $tool => $functionsFiles) {
            try {
                $toolPath = ComposerLocator::getPath($tool);
                foreach ($functionsFiles as $functionsFile) {
                    $configValues['parameters']['bootstrapFiles'][] = $toolPath . $functionsFile;
                }
            } catch (RuntimeException) {
                $output->writeln(
                    '<info>' . $tool . ' not found. Skip loading ' . implode(', ', $functionsFiles) . '.</info>',
                    OutputInterface::VERBOSITY_VERBOSE,
                );
            }
        }

        return $configValues;
    }

    /**
     * Adds the list of files to be excluded to the config.
     *
     * @param array<string,array<string|int,string|array<string>>> $configValues
     * @param array<EnhancedFileInfo>                              $exclusionList
     *
     * @return array<string,array<array<string|int,string>>>
     */
    private function addExcludedFiles(array $configValues, array $exclusionList): array
    {
        $directoryExcludedFilesStrings = array_map(
            static fn(EnhancedFileInfo $file): string => $file->getRealPath(),
            $exclusionList,
        );
        $configValues['parameters']['excludePaths'] = $directoryExcludedFilesStrings;
        return $configValues;
    }

    /**
     * Adds the list of static folders to scan if they exist.
     *
     * @param array<string,array<string|int,string|array<string>>> $configValues
     *
     * @return array<string,array<string|int,array<string>>>
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
}
