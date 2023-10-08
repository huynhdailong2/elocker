<?php

namespace App\Exports;

use App\Models\Spare;
use App\Consts;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Services\AdminService;

class VehiclesExport extends BaseExport implements FromView
{
    private $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        $adminService = new AdminService;
        $data = $adminService->getVehicles($this->params);

        return view('excels.vehicles_export', [
            'data'  => $data
        ]);
    }

}
