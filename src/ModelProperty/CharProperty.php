<?php

namespace Levaral\Core\ModelProperty;

class CharProperty extends AbstractModelProperty
{
    protected $type = 'char';
    protected $length = 100;

    public function getLength()
    {
        return $this->length;
    }

    public function length($length): self
    {
        $this->length = $length;
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

        return $props;
    }
}
