<?php

namespace App\Http\Controllers\API;

use App\Consts;
use App\Http\Controllers\AppBaseController as BaseController;
use App\Http\Services\SpareService;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TakingTransactionAPIController extends BaseController
{
    public function __construct(SpareService $spareService)
    {
        $this->spareService = $spareService;
    }

    public function takingTransaction(Request $request)
    {
        $request->validate([
            'type'      => [
                'required',
                Rule::in(
                    Consts::TAKING_TRANSACTION_TYPE_ISSUE,
                    Consts::TAKING_TRANSACTION_TYPE_REPLENISH,
                    Consts::TAKING_TRANSACTION_TYPE_RETURN,
                    Consts::TAKING_TRANSACTION_TYPE_REPLENISH_AUTO
                )
            ],
            'user_id'   => 'required',
            'total_qty' => 'numeric',
        ]);

        // DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->takingTransaction($params);
            // DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createWeighingTransaction(Request $request)
    {
        $request->validate(
            [
                'user_id' => 'required|exists:users,id',
            ]
        );

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->createWeighingTransaction($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
    public function createTransaction(Request $request)
    {
        $request->validate([
            'type' => 'in:'.Consts::TAKING_TRANSACTION_TYPE_ISSUE .','. Consts::TAKING_TRANSACTION_TYPE_REPLENISH.','. Consts::TAKING_TRANSACTION_TYPE_RETURN,
            // 'user_id'   => 'required',
            'total_qty' => 'numeric',
        ]);
        $userAgent = $request->server('HTTP_USER_AGENT');
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->createTransaction($params,$userAgent);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
