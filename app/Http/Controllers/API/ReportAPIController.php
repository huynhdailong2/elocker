<?php

namespace App\Http\Controllers\API;

use App\Exports\SpareExpiringExport;
use App\Exports\SparesByLoanExport;
use App\Exports\SparesByReturnsExport;
use App\Exports\SparesByTnxExport;
use App\Exports\SparesTorqueWrenchExport;
use App\Exports\SparesWriteOffExport;
use App\Exports\WeighingSystemTransactionExport;
use App\Http\Controllers\AppBaseController as BaseController;
use App\Http\Services\SpareService;
use App\Http\Services\WeightSystemService;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ReportAPIController extends BaseController
{
    public function __construct(SpareService $spareService)
    {
        $this->spareService = $spareService;
    }

    public function getSparesExpiring(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getSparesExpiring($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getYetToReturnSpares(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getSparesForReport($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSparesReportByWo(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getSparesForReport($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getReportByTnx(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getReportByTnx($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getReportByLoan(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getReportByLoan($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getReportByExpired(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getReportByExpired($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getReportForReturns(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getReportForReturns($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSparesWriteOff(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getSparesWriteOff($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function getSparesTorqueWrench(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->getSparesTorqueWrench($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function writeOffSpare(Request $request)
    {
        $request->validate([
            'return_spare_id' => 'required|exists:return_spares,id',
            'reason' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->writeOffSpare($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function unwriteOffSpare(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:write_offs,id',
        ]);

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->spareService->unwriteOffSpare($params);
            DB::commit();
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            DB::rollback();
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendSparesExpiringReport(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendSparesExpiringReport($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportSparesReportByExpiring(Request $request)
    {
        try {
            $currentTIme = now()->format('Y-m-d');

            $filename = "spares-expiring-{$currentTIme}.xlsx";
            return Excel::download(new SpareExpiringExport($request->all()), $filename, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendYetToReturnSparesReport(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendYetToReturnSparesReport($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendSparesReportByWo(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendSparesReportByWo($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendSparesReportByTnx(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendSparesReportByTnx($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportSparesReportByTnx(Request $request)
    {
        try {
            $currentTIme = now()->format('Y-m-d');

            $filename = "spares-trans-{$currentTIme}.xlsx";
            return Excel::download(new SparesByTnxExport($request->all()), $filename, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendSparesReportByReturns(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendSparesReportByReturns($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportSparesReportByReturns(Request $request)
    {
        try {
            $currentTIme = now()->format('Y-m-d');

            $filename = "spares-returns-{$currentTIme}.xlsx";
            return Excel::download(new SparesByReturnsExport($request->all()), $filename, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendSparesReportByLoan(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendSparesReportByLoan($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportSparesReportByLoan(Request $request)
    {
        try {
            $currentTIme = now()->format('Y-m-d');

            $filename = "spares-loan-{$currentTIme}.xlsx";
            return Excel::download(new SparesByLoanExport($request->all()), $filename, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendSparesReportByExpired(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendSparesReportByExpired($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendSparesWriteOffReport(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendSparesWriteOffReport($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportSparesReportByWriteOff(Request $request)
    {
        try {
            $currentTIme = now()->format('Y-m-d');

            $filename = "spares-write-off-{$currentTIme}.xlsx";
            return Excel::download(new SparesWriteOffExport($request->all()), $filename, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendSparesTorqueWrenchReport(Request $request)
    {
        try {
            $params = $request->all();
            $data = $this->spareService->sendSparesTorqueWrenchReport($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportSparesReportByTorqueWrench(Request $request)
    {
        try {
            $currentTIme = now()->format('Y-m-d');

            $filename = "torque-wrench-{$currentTIme}.xlsx";
            return Excel::download(new SparesTorqueWrenchExport($request->all()), $filename, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function reportCompartmentDamaged(Request $request)
    {
        try {
            $request->validate(
                [
                    'transaction_id' => 'required|exists:taking_transactions,id',
                    'bin_ids' => 'required|array',
                    'is_rfid' => 'nullable'
                ]
            );

            DB::beginTransaction();

            $data = $this->spareService->reportCompartmentDamaged($request->all());

            DB::commit();

            return $this->sendResponse($data, 'Successful');
        } catch (Exception $ex) {
            DB::rollBack();

            if($ex instanceof ValidationException) {
                return response()->json(["success" => false, "message" => "Something went wrong!"]);
            }

            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendWeighingSystemTransactionReport(Request $request)
    {
        try {
            /** @var WeightSystemService $weighingSystemService */
            $weighingSystemService = app(WeightSystemService::class);
            $params = $request->all();
            $data = $weighingSystemService->sendWeighingSystemTransactionReport($params);
            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function exportWeighingSystemTransaction(Request $request)
    {
        try {
            $currentTIme = now()->format('Y-m-d');

            $filename = "weighing-system-transaction-{$currentTIme}.xlsx";
            return Excel::download(new WeighingSystemTransactionExport($request->all()), $filename, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function sendTnxReportNotification()
    {
        try {
            $this->spareService->sendTnxReportNotification();

            return $this->sendResponse([], 'ok');
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }

    public function reportSparesReturnedAndIssued(Request $request)
    {
        try {
            $params = $request->all();

            $data = $this->spareService->reportSparesReturnedAndIssued($params);

            return $this->sendResponse($data);
        } catch (Exception $ex) {
            logger()->error($ex);
            return $this->sendError($ex);
        }
    }
}
