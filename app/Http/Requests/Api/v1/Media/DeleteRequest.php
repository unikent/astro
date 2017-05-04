<?php
namespace App\Http\Requests\Api\v1\Media;

use Gate;
use App\Models\Media;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'site_ids' => 'required_without:publishing_group_ids|array',
            'publishing_group_ids' => 'required_without:site_ids|array'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
        ];
    }
}
