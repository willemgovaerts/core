<?php

namespace Levaral\Core\ModelProperty;

class TextProperty extends AbstractModelProperty
{
    protected $type = 'text';

    public function longText()
    {
        $this->type = 'longText';
    }
}
