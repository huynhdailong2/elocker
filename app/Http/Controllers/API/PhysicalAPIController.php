<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController as BaseController;
use App\Http\Services\SpareService;
use Illuminate\Validation\Rule;
use App\Http\Requests\ReplenishManualEucForm;
use App\Consts;
use DB;
use Auth;
use Exception;

class PhysicalAPIController extends BaseController
{
    public function __construct(SpareService $spareService)
    {
        $this->spareService = $spareService;
    }

    public function generateCycleCount(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->spareService->generateCycleCount($request->all());
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
