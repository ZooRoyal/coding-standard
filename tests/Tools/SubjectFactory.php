<?php

namespace Zooroyal\CodingStandard\Tests\Tools;

use Mockery;
use ReflectionClass;

class SubjectFactory
{
    /** @var ReflectionClass */
    private $reflection;

    public function __construct($className)
    {
        $this->reflection = new ReflectionClass($className);
    }

    public function buildParameters(): array
    {
        $result['parameters'] = [];
        $parameters = $this->reflection->getConstructor()->getParameters();
        foreach ($parameters as $parameter) {
            $type = $parameter->getClass()->getName();
            $result['parameters'][$type] = Mockery::mock($type);
        }

        return $result['parameters'];
    }

    public function buildSubjectInstance($parameterInstances)
    {
        return $this->reflection->newInstanceArgs($parameterInstances);
    }

}
