<?php


namespace App\Exports;


use App\Consts;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Services\AdminService;


class SparesConfigureExport extends BaseExport implements FromView {
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

        $params = array_merge($this->params, [
            'no_pagination' => Consts::TRUE
        ]);

        $data = $adminService->getSpares($params);

        return view('excels.spares_configure', [
            'data'  => $data
        ]);
    }

}
