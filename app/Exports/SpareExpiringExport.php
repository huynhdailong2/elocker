<?php

namespace App\Exports;

use App\Models\Spare;
use App\Consts;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Services\SpareService;

class SpareExpiringExport extends BaseExport implements FromView {

    /**
     * @return View
     */
    public function view(): View
    {
        $spareService = new SpareService;
        $data = $spareService->getSparesExpiring([
            'no_pagination' => Consts::TRUE
        ]);

        return view('excels.spares_expiring_export', [
            'data'  => $data
        ]);
    }

}
