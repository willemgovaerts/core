<?php
namespace Levaral\Core\ModelProperty;


class TimestampProperty extends AbstractModelProperty
{
    protected $type = 'timestamp';
    protected $castType = '\Carbon\Carbon';
}
