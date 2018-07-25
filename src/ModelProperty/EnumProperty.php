<?php

namespace Levaral\Core\ModelProperty;


class EnumProperty extends AbstractModelProperty
{
    protected $type = 'enum';
    protected $options = null;
    protected $enumClass = null;

    public function getOptions(): array
    {
        if (!$this->options) {
            $this->options(Enum::getValues($this->getEnumClass()));
        }

        return $this->options;
    }

    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getEnumClass()
    {
        return $this->enumClass;
    }

    public function enumClass($enumClass): self
    {
        $this->enumClass = $enumClass;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRenderProperty()
    {
        if (!$this->options) {
            throw new \Exception('property '. $this->getName(). ' options not defined.');
        }

        $props = parent::getRenderProperty();
        $props += ($this->options) ? ['options' => $this->options] : $props;

        return $props;
    }
}
