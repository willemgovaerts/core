<?php

namespace Levaral\Core\ModelProperty;

class JsonProperty extends AbstractModelProperty
{
    protected $type = 'json';

    public function jsonb()
    {
        $this->type = 'jsonb';
    }
}
