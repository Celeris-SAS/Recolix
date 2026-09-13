<?php

use PHPUnit\Framework\TestCase;

/**
 * Los Controller/Model de API_Recoleccion abren una conexión MySQL real
 * dentro de su constructor (vía `new conexion()`), así que no se pueden
 * instanciar normalmente en un test unitario sin una base de datos.
 *
 * Esta clase base construye los objetos con ReflectionClass::newInstanceWithoutConstructor()
 * para probar la lógica de validación y las reglas de negocio puras sin
 * tocar la base de datos, e inyecta dobles de prueba en las propiedades
 * privadas (ej. el Model) cuando un método sí necesita delegar en él.
 */
abstract class RecoleccionTestCase extends TestCase
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
