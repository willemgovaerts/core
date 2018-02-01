<?php

namespace Levaral\Core\Exceptions;

use RuntimeException;

class PropertyDoesNotExistException extends RuntimeException
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
        $this->message = "Trying to set property '$property' to $class";

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