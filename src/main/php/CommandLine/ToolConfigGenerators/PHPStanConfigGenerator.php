<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators;

use PHPStan\DependencyInjection\NeonAdapter;
use PHPStan\File\FileWriter;
use Zooroyal\CodingStandard\CommandLine\Factories\ExclusionListFactory;
use Zooroyal\CodingStandard\CommandLine\ValueObjects\EnhancedFileInfo;

class PHPStanConfigGenerator implements ToolConfigGeneratorInterface
{
    private NeonAdapter $neonAdapter;
    private FileWriter $fileWriter;
    private ExclusionListFactory $blacklistFactory;

    public function __construct(
        NeonAdapter $neonAdapter,
        FileWriter $fileWriter,
        ExclusionListFactory $blacklistFactory
    ) {
        $this->fileWriter = $fileWriter;
        $this->neonAdapter = $neonAdapter;
        $this->blacklistFactory = $blacklistFactory;
    }

    public function addConfigParameters(string $blackListToken, array $parameters): array
    {
        $blacklistFiles = $this->blacklistFactory->build($blackListToken);
        $directoryBlackListfiles = array_map(
            static fn(EnhancedFileInfo $file) => $file->getRealPath(),
            $blacklistFiles
        );

        return array_merge_recursive(['parameters' => ['excludes_analyse' => $directoryBlackListfiles]], $parameters);
    }

    public function generateConfig(array $parameters): string
    {
        return $this->neonAdapter->dump($parameters);
    }

    public function writeConfig(string $filename, string $content): void
    {
        $this->fileWriter->write($filename, $content);
    }
}
