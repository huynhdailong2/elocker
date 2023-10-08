<?php


namespace App\Http\Controllers\API;


use App\Http\Requests\ScanBarcodeRequest;
use App\Http\Services\ScanBarcodeService;
use App\Http\Controllers\AppBaseController as BaseController;
use DB;
use Auth;
use Exception;


class ScanBarcodeController extends BaseController
{
    private $scanBarcodeService;

    public function __construct(
        ScanBarcodeService $scanBarcodeService
    ) {
        $this->scanBarcodeService = $scanBarcodeService;
    }

    public function scanBarcode(ScanBarcodeRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $this->scanBarcodeService->scanBarcode($request->all());

            DB::commit();

            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function finishedScanBarcode()
    {
        try {
            $data = $this->scanBarcodeService->finishedScanBarcode();

            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
