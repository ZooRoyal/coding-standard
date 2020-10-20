<?php


namespace Zooroyal\CodingStandard\CommandLine\ToolConfigGenerators;

use PHPStan\DependencyInjection\NeonAdapter;
use PHPStan\File\FileWriter;

class PHPStandConfigGenerator implements ToolConfigGeneratorInterface
{
    /** @var NeonAdapter */
    private $neonAdapter;
    /** @var FileWriter */
    private $fileWriter;


    public function __construct(NeonAdapter $neonAdapter, FileWriter $fileWriter)
    {
        $this->fileWriter = $fileWriter;
        $this->neonAdapter = $neonAdapter;
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
