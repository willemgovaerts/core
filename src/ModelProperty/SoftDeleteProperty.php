<?php
namespace Levaral\Core\ModelProperty;


class SoftDeleteProperty extends AbstractModelProperty
{
    protected $type = 'softDeletes';
    protected $castType = '\Carbon\Carbon';

    public function __construct($name)
    {
        $this->name = 'deleted_at';
    }

    public static function create($name = 'deleted_at')
    {
        return new static('deleted_at');
    }
}
