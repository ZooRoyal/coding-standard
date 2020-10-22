<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators;

use PHPStan\DependencyInjection\NeonAdapter;
use PHPStan\File\FileWriter;
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


    public function addConfigParameters(...$args): array
    {
        $blackListfiles = $this->blacklistFactory->build($args[0]);
        $diretoryBlackListfiles = [];

        foreach ($blackListfiles as $file) {
            $diretoryBlackListfiles[] = $args[1].'/'.$file;
        }

        return ['parameters' => ['excludes_analyse' => $diretoryBlackListfiles]];
    }

    public function generateConfig(array $parameters): string
    {
        return $this->neonAdapter->dump($parameters);
    }

    public function writeConfig(string $filename, string $content) : void
    {
        $this->fileWriter->write($filename, $content);
    }
}
