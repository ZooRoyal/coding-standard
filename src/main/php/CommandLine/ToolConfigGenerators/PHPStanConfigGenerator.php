<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators;

use PHPStan\DependencyInjection\NeonAdapter;
use PHPStan\File\FileWriter;
use Symplify\SmartFileSystem\SmartFileInfo;
use Zooroyal\CodingStandard\CommandLine\Factories\BlacklistFactory;

class PHPStanConfigGenerator implements ToolConfigGeneratorInterface
{
    /** @var NeonAdapter */
    private $neonAdapter;
    /** @var FileWriter */
    private $fileWriter;
    /** @var BlacklistFactory */
    private $blacklistFactory;

    public function __construct(
        NeonAdapter $neonAdapter,
        FileWriter $fileWriter,
        BlacklistFactory $blacklistFactory
    ) {
        $this->fileWriter = $fileWriter;
        $this->neonAdapter = $neonAdapter;
        $this->blacklistFactory = $blacklistFactory;
    }


    public function addConfigParameters(string $blackListToken, SmartFileInfo $rootDirectory, array $parameters): array
    {
        $blacklistFiles = $this->blacklistFactory->build($blackListToken);
        $directoryBlackListfiles = array_map(
            static fn(SmartFileInfo $file) => $file->getRealPath(),
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
