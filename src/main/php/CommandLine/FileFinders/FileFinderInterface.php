<?php
namespace Zooroyal\CodingStandard\CommandLine\FileFinders;

interface FileFinderInterface
{
    public function findFiles($filter = '', $stopword = '', $targetBranch = '');
}
