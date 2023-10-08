<?php

namespace App\Exports;

use App\Models\Spare;
use App\Consts;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Services\AdminService;

class CycleCountExport extends BaseExport implements FromView {

    /**
     * @return View
     */
    public function view(): View
    {
        $adminService = new AdminService;
        $types = [
            Consts::SPARE_TYPE_CONSUMABLE,
            Consts::SPARE_TYPE_DURABLE,
            Consts::SPARE_TYPE_PERISHABLE,
            Consts::SPARE_TYPE_AFES,
            Consts::SPARE_TYPE_EUC,
            Consts::SPARE_TYPE_TORQUE_WRENCH,
            Consts::SPARE_TYPE_OTHERS
        ];
        $data = $adminService->getSparesAssignedBin()->groupBy('type');

        return view('excels.cycle_count_export', [
            'types' => $types,
            'data'  => $data
        ]);
    }

}
