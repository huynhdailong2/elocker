<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\PolManagement;
use App\Utils\BigNumber;

class IssuePolFormRequest extends FormRequest
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
            'pols'                  => 'required|array',
            'pols.*.id'             => 'required|exists:pol_managements,id',
            'pols.*.quantity'       => 'required|numeric|min:1',
            'pols.*.receiver_id'    => 'required|exists:users,id'
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

        $this->validateDataConsistency();
    }

    public function validateDataConsistency()
    {
        $pols = $this->input('pols', []);
        $polIds = collect($pols)->pluck('id')->toArray();

        $mapPol = PolManagement::whereIn('id', $polIds)
            ->get()
            ->mapWithKeys(function ($record) {
                return [$record->id => $record];
            });

        foreach ($pols as $value) {
            if (empty($mapPol[$value['id']])) {
                continue;
            }

            $pol = $mapPol[$value['id']];
            $isEqualOrBigger = BigNumber::new($pol->received_quantity)
                ->sub($pol->issued_quantity)
                ->comp($value['quantity']);
            if ($isEqualOrBigger) {
                continue;
            }

            throw ValidationException::withMessages([
                'quantity' => ['The quantity is invalid.']
            ]);
        }
    }
}
