<?php

use PHPUnit\Framework\TestCase;

abstract class GestionTestCase extends TestCase
{
    protected function newWithoutConstructor(string $class): object
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    protected function callPrivate(object $object, string $method, array $args = [])
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($object, $args);
    }

    protected function setPrivateProperty(object $object, string $property, $value): void
    {
        $reflection = new ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }
}
