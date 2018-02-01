<?php

namespace Levaral\Core\Criteria;

use Levaral\Core\Mapper\AutoMapper;

class BaseCriteria
{
    public function __construct(array $data)
    {
        AutoMapper::mapData($this, $data);
    }
}