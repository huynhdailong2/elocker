<?php

namespace App\Exports;

use App\Models\Spare;
use App\Utils;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Services\AdminService;

class InventoryCountExport extends BaseExport implements FromView {

    /**
     * @return View
     */
    public function view(): View
    {
        $adminService = new AdminService;
        $data = $adminService->getSparesAssignedBin();

        return view('excels.inventory_count_export', [
            'data' => $data,
        ]);
    }

}
