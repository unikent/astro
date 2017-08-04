<?php

namespace App\Exceptions;

use App\Models\Page;

/**
 * Thrown when an attempt is made to set the draft PageContent of a Page when it already has a draft.
 * @package App\Exceptions
 */
class PageExistsException extends \Exception
{
    /**
     * @var Page The Page which already contained a draft.
     */
    public $page = null;
    /**
     * PageExistsException constructor.
     * @param Page $page
     */
    public function __construct($page,$message = null)
    {
        $this->page = $page;
        parent::__construct($message ? $message : 'A page already exists at ' . $page->path);
    }

    public function toString()
    {
        return $this->getMessage();
    }
}