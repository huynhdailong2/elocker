<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
 use Illuminate\Support\Facades\Validator;

class ReplenishManualEucForm extends FormRequest
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
            'spares' => 'required|array'
        ];
    }

    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validateResolved()
    {
        parent::validateResolved();

        $this->validateManual();
    }

    private function validateManual()
    {
        $spares = $this->input('spares');
        foreach ($spares as $value) {
            $validator = Validator::make($value, [
                'euc_box_id'           => 'required|exists:euc_boxes,id',
                'spare_id'             => $this->input('spare_id') ? 'required|exists:spares,id' : '',
                'mpn'                  => $this->input('spare_id') ? '' : 'required',
                'ssn'                  => $this->input('spare_id') ? '' : 'required',
                'is_confirmed'         => 'required',
                'requester_id'         => 'required|exists:users,id'
            ])->validate();
        }
    }
}
