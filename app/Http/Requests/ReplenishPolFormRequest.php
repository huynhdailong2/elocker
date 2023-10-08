<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplenishPolFormRequest extends FormRequest
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
            'pols'                   => 'required|array',
            'pols.*.id'              => 'required|exists:pol_managements,id',
            'pols.*.quantity'        => 'required|numeric|min:1',
            'pols.*.requester_id'    => 'required|exists:users,id'
        ];
    }
}
