<?php

namespace Levaral\Core\DTO;

use Levaral\Core\Mapper\AutoMapper;

class BaseDTO
{
    public function __construct(array $data)
    {
        AutoMapper::mapData($this, $data);
    }
}