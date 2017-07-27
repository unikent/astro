<?php

namespace App\Exceptions;

use App\Models\Page;

/**
 * Thrown when an attempt is made to set the draft PageContent of a Page when it already has a draft.
 * @package App\Exceptions
 */
class DraftExistsException extends \Exception
{
    /**
     * @var Page The Page which already contained a draft.
     */
    public $page = null;
    /**
     * DraftExistsException constructor.
     * @param Page $page
     */
    public function __construct($page,$message = null)
    {
        $this->page = $page;
        parent::__construct($message ? $message : 'Page "' . $page->path . ' already has a draft.');
    }
}