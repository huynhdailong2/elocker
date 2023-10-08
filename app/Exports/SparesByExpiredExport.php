<?php

namespace App\Exports;

use App\Models\Spare;
use App\Consts;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Services\SpareService;

class SparesByExpiredExport extends BaseExport implements FromView {

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
        $spareService = new SpareService;

        $params = array_merge($this->params, [
            'no_pagination' => Consts::TRUE
        ]);

        $data = $spareService->getReportByExpired($params);

        return view('excels.spares_by_expired', [
            'data'  => $data
        ]);
    }

}
