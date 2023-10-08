<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Services\SpareService;
use App\Models\Spare;
use App\Consts;

class IssueCardFormRequest extends FormRequest
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
            'spares'                            => 'required|array',
            'spares.*.job_card_id'              => 'required|exists:job_cards,id',
            'spares.*.bin_id'                   => 'exists:bins,id',
            'spares.*.euc_box_id'               => 'exists:euc_boxes,id',
            'spares.*.spare_id'                 => 'required|exists:spares,id',
            'spares.*.quantity'                 => 'required|integer|min:1',
            'spares.*.taker_id'                 => 'required|exists:users,id',
            'spares.*.torque_wrench_area_id'    => 'exists:torque_wrench_areas,id',
        ];
    }

    protected function prepareForValidation()
    {
        $cleanSpares = [];
        foreach ($this->spares as $spare) {
            if (empty($spare['euc_box_id'])) {
                unset($spare['euc_box_id']);
            }
            if (empty($spare['bin_id'])) {
                unset($spare['bin_id']);
            }
            $cleanSpares[] = $spare;
        }

        $this->merge([
            'spares' => $cleanSpares,
        ]);
    }

    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validateResolved()
    {

        parent::validateResolved();

        // $this->validateDataConsistency();
    }

    // public function validateDataConsistency()
    // {
    //     $inputSpares = $this->input('spares', []);
    //     $inputSpareIds = collect($inputSpares)->pluck('spare_id')->toArray();

    //     $mapSpare = $this->getSparesIssued($inputSpareIds);

    //     foreach ($inputSpares as $input) {
    //         if (empty($mapSpare[$input['spare_id']])) {
    //             continue;
    //         }
    //         $spare = $mapSpare[$input['spare_id']];
    //         if ($input['quantity'] <= $spare->quantity) {
    //             continue;
    //         }

    //         throw ValidationException::withMessages([
    //             'quantity' => ['The quantity is invalid.']
    //         ]);
    //     }
    // }

    // private function getSparesIssued($spareIds)
    // {
    //     $service = new SpareService;
    //     return $service->getSparesReturn($spareIds)
    //         ->groupBy('spare_id')
    //         ->mapWithKeys(function ($itemList, $key) {
    //             $total = collect($itemList)->sum('quantity');
    //             return [
    //                 $key => (object) [
    //                     'spare_id' => $key,
    //                     'quantity' => $total
    //                 ]
    //             ];
    //         });
    // }
}
