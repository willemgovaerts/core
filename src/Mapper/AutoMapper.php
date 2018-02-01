<?php

namespace Levaral\Core\Mapper;

use Levaral\Core\Exceptions\PropertyDoesNotExistException;
use Levaral\Core\Exceptions\PropertyTypeMismatchException;
use Levaral\Core\Exceptions\PropertyTypeMissingException;
use Levaral\Core\Exceptions\PropertyTypeNotFoundException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Maps an array to object.
 */
class AutoMapper
{
    public static function mapData($object, array $mappingData)
    {
        $reflectionClass = new ReflectionClass($object);

        foreach ($mappingData as $key => $value) {
            if (!$reflectionClass->hasProperty($key)) {
                throw (new PropertyDoesNotExistException)->setProperty($key, $reflectionClass->getName());
                continue;
            }
            $property = $reflectionClass->getProperty($key);

            if (!is_null($value)) {
                $type = is_object($value) ? get_class($value) : gettype($value);
                list($propertyType, $isClass, $itemClass) = static::parseType($property);

                if ($isClass && $type == 'array') {
                    $value = static::mapData(new $propertyType, $value);
                    $type = $propertyType;
                }

                if ($itemClass && $type == 'array') {
                    $items = collect([]);
                    foreach ($value as $item) {
                        $items->push(static::mapData(new $itemClass, $item));
                    }
                    $type = $itemClass = $propertyType;
                    $value = $items;
                }

                if ($type != $propertyType) {
                    throw (new PropertyTypeMismatchException)->setProperty($key, $reflectionClass->getName(), $propertyType, $type);
                }
            }

            $object->$key = $value;
        }

        return $object;
    }

    private static function parseType(ReflectionProperty $property)
    {
        $matches = [];
        preg_match_all('/@(\w+)(.*)/', $property->getDocComment(), $matches);
        $annotation = array_combine($matches[1], $matches[2]);
        $annotation = array_map('trim', $annotation);

        if (!isset($annotation['var'])) {
            throw (new PropertyTypeMissingException)->setProperty($property->getName(), $property->getDeclaringClass()->getName());
        }

        $isClass = false;
        $itemClass = null;

        switch(strtolower($annotation['var'])) {
            case "int":
            case "integer":
                $type = 'integer';
                break;
            case "bool":
            case "boolean":
                $type = 'boolean';
                break;
            case "float":
                $type = 'float';
                break;
            case "double":
                $type = 'double';
                break;
            case "real":
                $type = 'real';
                break;
            case "string":
                $type = 'string';
                break;
            case "array":
                $type = 'array';
                break;
            default:
                if (!class_exists($annotation['var'])) {
                    throw (new PropertyTypeNotFoundException)->setProperty($property->getName(), $property->getDeclaringClass()->getName(), $annotation['var']);
                }
                $isClass = true;
                $type = ltrim($annotation['var'], '\\');
        }

        if (isset($annotation['item'])) {
            $itemClass = $annotation['item'];
            if (!class_exists($itemClass)) {
                throw (new PropertyTypeNotFoundException)->setProperty($property->getName(), $property->getDeclaringClass()->getName(), $annotation['item']);
            }
        }

        return [$type, $isClass, $itemClass];
    }

}