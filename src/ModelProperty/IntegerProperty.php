<?php

namespace Levaral\Core\ModelProperty;


class IntegerProperty extends AbstractModelProperty
{
    protected $type = 'integer';
    protected $castType = 'integer';

    public function unsigned(): self
    {
        $this->type = 'unsignedInteger';
        return $this;
    }

    public function increments(): self
    {
        $this->type = 'increments';
        return $this;
    }

    public function tiny(): self
    {
        $this->type = 'tinyInteger';
        return $this;
    }

    public function small(): self
    {
        $this->type = 'smallInteger';
        return $this;
    }

    public function medium(): self
    {
        $this->type = 'mediumInteger';
        return $this;
    }

    public function big(): self
    {
        $this->type = 'bigInteger';
        return $this;
    }

    public function unsignedTiny(): self
    {
        $this->type = 'unsignedTinyInteger';
        return $this;
    }

    public function unsignedSmall(): self
    {
        $this->type = 'unsignedSmallInteger';
        return $this;
    }

    public function unsignedMedium(): self
    {
        $this->type = 'unsignedMediumInteger';
        return $this;
    }

    public function unsignedBig(): self
    {
        $this->type = 'unsignedBigInteger';
        return $this;
    }
}
