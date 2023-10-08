<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Consts;
use Illuminate\Support\Facades\Validator;
use App\Models\Spare;

class SpareFormRequest extends FormRequest
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
        $spareId = $this->input('id');
        return [
            'id'                => 'exists:spares,id',
            'name'              => 'required',
            'part_no'           => "required|unique_part_no:{$spareId}",
            'material_no'       => "required|unique_material_no:{$spareId}",
            'type'              => [
                'required',
                Rule::in(
                    [
                        Consts::SPARE_TYPE_CONSUMABLE,
                        Consts::SPARE_TYPE_DURABLE,
                        Consts::SPARE_TYPE_PERISHABLE,
                        Consts::SPARE_TYPE_AFES,
                        Consts::SPARE_TYPE_EUC,
                        Consts::SPARE_TYPE_OTHERS,
                        Consts::SPARE_TYPE_TORQUE_WRENCH,
                        Consts::SPARE_TYPE_LIFTING_EQUIPMENT,
                    ]

                )
            ]
        ];
    }
}
