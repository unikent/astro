<?php

namespace App\Http\Requests\Api\v1\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'host.unique' => '',
            'path.unique' => 'A site with this host and path already exists.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $path = $this->input('path', '');
        if(is_null($path)){
            $this->merge(['path'=>'']);
        }
        $rules = [
            'name' => ['required', 'max:190' ],
            'publishing_group_id' => [ 'required' ],
            'host' => [
                'required',
                'max:100',
                'regex:/^[a-z0-9.-]+(:[0-9]+)?$/',
                'unique:sites,host,null,id,path,' . $this->input('path')
            ],
            'path' =>[
                'nullable',
                'regex:/^(\/[a-z0-9_-]+)*$/i',
                'unique:sites,path,null,id,host,' . $this->input('host')
            ],
        ];
        $rules['publishing_group_id'][] = Rule::exists('publishing_groups', 'id');
        return $rules;
    }
}
