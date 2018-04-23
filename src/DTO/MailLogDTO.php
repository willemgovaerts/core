<?php

namespace Levaral\Core\DTO;


class MailLogDTO extends BaseDTO
{
    /**
     * @var string
     */
    public $event;

    /**
     * @var integer
     */
    public $model_id;

    /**
     * @var string
     */
    public $reason;

    /**
     * @var integer
     */
    public $code;
}