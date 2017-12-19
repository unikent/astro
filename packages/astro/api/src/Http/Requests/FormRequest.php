<?php
namespace Astro\API\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

class FormRequest extends LaravelFormRequest
{

    public function getValidatorInstance(){
        return parent::getValidatorInstance();
    }

}
