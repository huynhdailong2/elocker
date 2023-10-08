<?php

namespace App\Http\Requests;

use App\Consts;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportCompartmentDamagedRequest extends FormRequest
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
            'transaction_id' => 'required|exists:taking_transactions,id',
            'bin_ids' => 'required|array',
            'is_rfid' => 'nullable'
        ];
    }
}
