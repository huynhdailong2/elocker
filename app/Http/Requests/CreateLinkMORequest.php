<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;


class CreateLinkMORequest extends FormRequest
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
            'bin_id' => 'required|exists:bins,id',
            'spare_id' => 'required|exists:spares,id',
            'job_card_id' => 'required|exists:job_cards,id',
            'torque_wrench_area_id' => 'required|exists:torque_wrench_areas,id',
        ];
    }
}
