<?php

namespace Levaral\Core\Exceptions;

use RuntimeException;

class PropertyTypeNotFoundException extends RuntimeException
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
    protected $type;

    public function setProperty($property, $class, $type)
    {
        $this->property = $property;
        $this->class = $class;
        $this->type = $type;
        $this->message = "$class:\$$property has invalid type '$type'";

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}