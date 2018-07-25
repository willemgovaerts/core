<?php

namespace Levaral\Core\ModelProperty;


class StringProperty extends AbstractModelProperty
{
    protected $type = 'string';
    protected $length = null;

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