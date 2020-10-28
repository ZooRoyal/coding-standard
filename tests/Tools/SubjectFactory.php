<?php

namespace Zooroyal\CodingStandard\Tests\Tools;

use Mockery;
use ReflectionClass;

class SubjectFactory
{

    public function buildSubject($className) :ReflectionClass
    {
        return new ReflectionClass($className);
    }


    public function buildParameters(ReflectionClass $reflection): array
    {
        $result['parameters'] = [];
        $parameters = $reflection->getConstructor()->getParameters();
        foreach ($parameters as $parameter) {
            $type = $parameter->getClass()->getName();
            $result['parameters'][$type] = Mockery::mock($type);
        }

        return $result['parameters'];
    }

    public function buildSubjectInstance(ReflectionClass $reflection, $parameterInstances)
    {
        return $reflection->newInstanceArgs($parameterInstances);
    }

}
