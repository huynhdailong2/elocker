<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\SyncDataService;
use App\Consts;
use DB;
use Exception;

class SyncDataAPIController extends AppBaseController
{
    protected $syncDataService;

    public function __construct(SyncDataService $syncDataService)
    {
        $this->syncDataService = $syncDataService;
    }

    public function fetchData(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->syncDataService->fetchData($request->all());
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
             DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function pushData(Request $request)
    {
        // DB::beginTransaction();
        try {
            $request->validate([
                'scripts' => 'required|array|min:1',
            ]);
            $data = $this->syncDataService->pushData($request->all());
            // DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            // DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    /**
    * @SWG\Post(
    *   path="/synchronization/sync-json",
    *   summary="Push Data With JSON",
    *   tags={"Synchronization Data"},
    *   security={},
    *   @SWG\Parameter(
    *       name="json_data",
    *       in="body",
    *       required=true,
    *       @SWG\Schema(
    *           @SWG\Property(
    *               type="array",
    *               property="json_data",
    *               @SWG\Items(
    *                   @SWG\Property(property="model", type="string", description="users,vehicles,..."),
    *                   @SWG\Property(property="record", type="string", description="json record")
    *               ),
    *           ),
    *       ),
    *   ),
    *   @SWG\Response(response=200, description="Successful Operation"),
    *   @SWG\Response(response=401, description="Unauthenticated"),
    *   @SWG\Response(response=500, description="Internal Server Error")
    * )
    **/
    public function pushDataWithJson(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'json_data' => 'required|array|min:1',
            ]);
            $data = $this->syncDataService->pushDataWithJson($request->all());
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
