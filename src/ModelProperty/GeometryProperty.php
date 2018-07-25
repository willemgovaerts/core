<?php

namespace Levaral\Core\ModelProperty;

class GeometryProperty extends AbstractModelProperty
{
    protected $type = 'geometry';

    public function geometryCollection()
    {
        $this->type = 'geometryCollection';
    }
}
