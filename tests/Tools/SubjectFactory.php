<?php
namespace Zooroyal\CodingStandard\Tests\Tools;

use Mockery;
use ReflectionClass;

class SubjectFactory
{
    public function buildSubject($className)
    {
        $result             = ['subject' => null];
        $parameterInstances = [];

        $reflection = new ReflectionClass($className);

        $parameters = $reflection->getConstructor()->getParameters();

        foreach ($parameters as $parameter) {
            $type                                   = $parameter->getClass()->getName();
            $result['parameters'][$type] = Mockery::mock($type);
            $parameterInstances[]                   = $result['parameters'][$type];
        }

        $result['subject'] = $reflection->newInstanceArgs($parameterInstances);

        return $result;
    }
}
