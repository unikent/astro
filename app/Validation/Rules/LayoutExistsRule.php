<?php

namespace App\Validation\Rules;

use Astro\API\Models\Definitions\BaseDefinition;
use Astro\API\Models\Definitions\Layout;
use Astro\API\Models\Page;
use Illuminate\Support\Facades\Config;

class LayoutExistsRule
{
    /**
    * Create a new rule instance.
    * @param int $version The version of the layout to test existance of.
    * @return void
    */
    public function __construct($version)
    {
        $this->version = $version;
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
        return Layout::locateDefinition(
            Layout::idFromNameAndVersion($value, $this->version)
        ) != false;
    }

    /**
    * Get the validation error message.
    *
    * @return string
    */
    public function message()
    {
        return 'The given layout and version does not exist';
    }
}
