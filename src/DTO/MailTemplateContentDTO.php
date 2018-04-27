<?php
/**
 * Created by PhpStorm.
 * User: levaral
 * Date: 23/04/18
 * Time: 7:09 PM
 */

namespace Levaral\Core\DTO;


class MailTemplateContentDTO extends BaseDTO
{
    /**
     * @var string
     */
    public $locale_code;

    /**
     * @var integer
     */
    public $mail_template_id;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $content;
}