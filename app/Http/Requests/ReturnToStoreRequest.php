<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Services\SpareService;
use Illuminate\Validation\Rule;
use App\Consts;

class ReturnToStoreRequest extends FormRequest
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
            'spares.*.bin_id'   => 'required|exists:bins,id',
            'spares.*.spare_id' => 'required|exists:spares,id',
            'spares.*.quantity' => 'required|numeric',
            'spares.*.state'    => ['required', Rule::in(
                Consts::RETURN_SPARE_STATE_INCOMPLETE,
                Consts::RETURN_SPARE_STATE_WORKING,
                Consts::RETURN_SPARE_STATE_DAMAGE,
                Consts::RETURN_SPARE_STATE_EXPIRED,
                Consts::RETURN_SPARE_STATE_FINISHED,
                Consts::RETURN_SPARE_STATE_INCOMPLETE,
            )]
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

        // $this->validateDataConsistency();
    }

    // private function validateDataConsistency ()
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
    //             $executedQty = collect($itemList)->sum('returned_quantity');
    //             return [
    //                 $key => (object) [
    //                     'spare_id' => $key,
    //                     'quantity' => $total - $executedQty
    //                 ]
    //             ];
    //         });
    // }
}
