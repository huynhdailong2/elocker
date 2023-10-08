<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Services\SpareService;

class ReturnHandoverRequest extends FormRequest
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
            'receiver_id'       => 'required|exists:users,id',
            'spares.*.bin_id'   => 'required|exists:bins,id',
            'spares.*.spare_id' => 'required|exists:spares,id',
            'spares.*.quantity' => 'required|numeric'
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

    // public function validateDataConsistency()
    // {
    //     $inputSpares = $this->input('spares', []);
    //     $inputSpareIds = collect($inputSpares)->pluck('spare_id')->toArray();

    //     $mapSpare = $this->getSparesReturn($inputSpareIds);

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

    // private function getSparesReturn($spareIds)
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
