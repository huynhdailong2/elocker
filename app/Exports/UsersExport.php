<?php


namespace App\Exports;


use App\Consts;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Services\UserService;


class UsersExport extends BaseExport implements FromView {
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
        $userService = new UserService;

        $params = array_merge($this->params, [
            'no_pagination' => Consts::TRUE
        ]);

        $data = $userService->getAllUsers($params);

        return view('excels.users', [
            'data'  => $data
        ]);
    }

}
