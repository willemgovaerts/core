<?php
namespace Levaral\Core\ModelProperty;


class DateTimeProperty extends AbstractModelProperty
{
    protected $type = 'dateTime';
    protected $castType = '\Carbon\Carbon';
}
