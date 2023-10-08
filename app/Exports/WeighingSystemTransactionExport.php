<?php


namespace App\Exports;


use App\Http\Services\WeightSystemService;
use App\Consts;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class WeighingSystemTransactionExport extends BaseExport implements FromView {

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
        $weighingSystemService = new WeightSystemService();

        $params = array_merge($this->params, [
            'no_pagination' => Consts::TRUE
        ]);

        $data = $weighingSystemService->transactionsWeighingSystem($params);

        return view('excels.weighing_system_transaction', [
            'data'  => $data
        ]);
    }

}
