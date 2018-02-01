<?php

namespace Levaral\Core\Exceptions;

use RuntimeException;

class PropertyTypeMissingException extends RuntimeException
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $property;

    public function setProperty($property, $class)
    {
        $this->property = $property;
        $this->class = $class;
        $this->message = "No type defined for $class::\$$property";

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
}