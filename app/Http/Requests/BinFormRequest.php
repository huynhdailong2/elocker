<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class BinFormRequest extends FormRequest
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
            'id'            => 'required|exists:bins,id',
            'shelf_id'      => 'required|exists:shelfs,id',
            'spare_id'      => 'required|exists:spares,id',
            // 'quantity'      => 'required|integer',
            'critical'      => 'nullable|integer|min:0',
            'min'           => 'required|integer|min:0',
            'max'           => 'required|integer|gte_field:min'
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
        $configures = $this->input('configures') ?: [];
        foreach ($configures as $value) {
            $validator = Validator::make($value, [
                'batch_no'                      => empty($value['has_batch_no']) ? '' : 'required',
                'charge_timeserial_no'          => empty($value['has_serial_no']) ? '' : 'required',
                'charge_time'                   => empty($value['has_charge_time']) ? '' : 'required',
                'calibration_due'               => empty($value['has_calibration_due']) ? '' : 'required',
                'load_hydrostatic_test_due'     => empty($value['has_load_hydrostatic_test_due']) ? '' : 'required',
                'expiry_date'                   => empty($value['has_expiry_date']) ? '' : 'required'
            ])->validate();
        }
    }
}
