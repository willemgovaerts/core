<?php
namespace Levaral\Core\ModelProperty;


class FloatProperty extends AbstractModelProperty
{
    protected $type = 'float';
    protected $length = 8;
    protected $places = 2; // number of digits after decimal point
    protected $castType = 'double';

    public function getLength()
    {
        return $this->length;
    }

    public function length($length): self
    {
        $this->length = $length;
        return $this;
    }

    public function getPlaces()
    {
        return $this->places;
    }

    public function places($places): self
    {
        $this->places = $places;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRenderProperty()
    {
        $props = parent::getRenderProperty();

        $props += ($this->length) ? ['length' => $this->length] : $props;
        $props += ($this->places) ? ['places' => $this->places] : $props;

        return $props;
    }
}
