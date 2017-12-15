<?php

namespace App\Validation\Rules;

use Astro\API\Models\Page;

class UniqueSitePathRule
{
    /**
    * Create a new rule instance.
    * @param string $host The domain name / host that this rule is testing along with the path.
    * @return void
    */
    public function __construct($host)
    {
        $this->host = $host;
    }

    /**
    * Determine if the validation rule passes.
    *
    * @param  string  $attribute
    * @param  mixed  $value
    * @return bool
    */
    public function passes($attribute, $value)
    {
        $page = Page::findByHostAndPath($this->host, $value, Page::STATE_DRAFT);
        return $page ? false : true;
    }

    /**
    * Get the validation error message.
    *
    * @return string
    */
    public function message()
    {
        return 'A page with the given site and path already exists.';
    }
}
