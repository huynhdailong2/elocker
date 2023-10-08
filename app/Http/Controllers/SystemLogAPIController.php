<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\SystemLogsReport;
use App\Exports\SpareLogsReport;
use App\Utils;
use ZipArchive;

class SystemLogAPIController extends AppBaseController
{

    public function downloadSystemLogs(Request $request)
    {
        $currentMills = Utils::currentMilliseconds();
        $filename = "Drk_System_Log_Report_{$currentMills}";
        $params = $request->all();
        // $fileDownload = Excel::download(new SystemLogsReport($params), "{$filename}.csv", \Maatwebsite\Excel\Excel::CSV);
        $fileDownload = Excel::download(new SpareLogsReport($params), "{$filename}.xlsx", \Maatwebsite\Excel\Excel::XLSX);
        return $this->compress($filename, $fileDownload);
    }

    private function compress($filename, $fileDownload)
    {
        $zip = new ZipArchive;
        $filenameZip = storage_path() . "/app/public/{$filename}.zip";

        if ($zip->open($filenameZip, ZipArchive::CREATE) === true) {
            $zip->addFile($fileDownload->getFile(), "{$filename}.xlsx");
            $zip->close();
        }
        return response()->download($filenameZip);
    }
}
