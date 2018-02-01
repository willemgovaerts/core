<?php

namespace Levaral\Core\Exceptions;

use RuntimeException;

class PropertyTypeMismatchException extends RuntimeException
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $property;

    /**
     * @var string
     */
    protected $propertyType;

    /**
     * @var string
     */
    protected $type;

    public function setProperty($property, $class, $propertyType, $type)
    {
        $this->property = $property;
        $this->class = $class;
        $this->propertyType = $propertyType;
        $this->type = $type;

        $this->message = "Trying to map '$type' to $propertyType $class::\$$property";

        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getPropertyType(): string
    {
        return $this->propertyType;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}