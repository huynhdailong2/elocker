<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ping', 'HomeController@ping');

Route::post('/login', [ 'uses' => 'API\Auth\LoginAPIController@loginViaApi', 'middleware' => 'throttle:6000|6000,1' ])->name('login');
Route::post('/oauth/token', [ 'uses' => 'API\Auth\LoginAPIController@issueToken', 'middleware' => 'throttle:6000|6000,1' ]);
Route::group(['prefix' => 'weight'], function() {
    Route::get('/all-bins', 'API\WeightSystemAPIController@getAllBins');
});

Route::group(['middleware' => ['auth:api', 'log.requests', 'json.response']], function () {
    Route::group(['prefix' => 'users'], function() {
        Route::get('/', 'API\UserAPIController@getAllUsers');
        Route::get('/info', 'API\UserAPIController@getUserInfo');
        Route::get('/statistic', 'API\UserAPIController@getUsersStatistic');
        Route::get('/info/by-card-id', 'API\UserAPIController@getUserInfoByCardId');
        Route::post('/create', 'API\UserAPIController@createNewAccount');
        Route::post('/info', 'API\UserAPIController@updateAccountApp');
        Route::post('/import', 'API\UserAPIController@importUsers');
        Route::post('/{user_id}', 'API\UserAPIController@updateAccount');
        Route::delete('/{user_id}', 'API\UserAPIController@deleteUser');
        Route::get('/roles', 'API\UserAPIController@getAvailableRoles');
        Route::get('/export', 'API\UserAPIController@exportUsers');
    });

    Route::group(['prefix' => 'report'], function() {
        Route::get('/spares-expiring', 'API\ReportAPIController@getSparesExpiring');
        Route::get('/yet-return-spares', 'API\ReportAPIController@getYetToReturnSpares');
        Route::get('/spares-wo', 'API\ReportAPIController@getSparesReportByWo');
        Route::get('/tnx', 'API\ReportAPIController@getReportByTnx');
        Route::get('/loan', 'API\ReportAPIController@getReportByLoan');
        Route::get('/expired', 'API\ReportAPIController@getReportByExpired');
        Route::get('/returns', 'API\ReportAPIController@getReportForReturns');
        Route::get('/write-off', 'API\ReportAPIController@getSparesWriteOff');
        Route::get('/torque-wrench', 'API\ReportAPIController@getSparesTorqueWrench');

        Route::post('/spares-expiring/write-off', 'API\ReportAPIController@writeOffSpare');
        Route::post('/spares-expiring/unwrite-off', 'API\ReportAPIController@unwriteOffSpare');

        Route::post('/spares-expiring/by-mail', 'API\ReportAPIController@sendSparesExpiringReport');
        Route::post('/yet-return-spares/by-mail', 'API\ReportAPIController@sendYetToReturnSparesReport');
        Route::post('/spares-wo/by-mail', 'API\ReportAPIController@sendSparesReportByWo');
        Route::post('/spares-expired/by-mail', 'API\ReportAPIController@sendSparesReportByExpired');
        Route::post('/spares-loan/by-mail', 'API\ReportAPIController@sendSparesReportByLoan');
        Route::post('/spares-tnx/by-mail', 'API\ReportAPIController@sendSparesReportByTnx');
        Route::post('/spares-tnx-notification/by-mail', 'API\ReportAPIController@sendTnxReportNotification');
        Route::post('/spares-returns/by-mail', 'API\ReportAPIController@sendSparesReportByReturns');
        Route::post('/write-off/by-mail', 'API\ReportAPIController@sendSparesWriteOffReport');
        Route::post('/torque-wrench/by-mail', 'API\ReportAPIController@sendSparesTorqueWrenchReport');
        Route::post('/weighing-system/by-mail', 'API\ReportAPIController@sendWeighingSystemTransactionReport');

        Route::post('/compartment-damaged', 'API\ReportAPIController@reportCompartmentDamaged');
        Route::get('/spares-returned-issued', 'API\ReportAPIController@reportSparesReturnedAndIssued');
    });

    Route::group(['prefix' => 'settings'], function() {
        Route::post('senders-email', 'API\SettingAPIController@saveSenderEmail');
        Route::post('receivers-email', 'API\SettingAPIController@saveReceiverEmail');
        Route::get('/schedule', 'API\SettingAPIController@getScheduleSettings');
        Route::post('cycle-schedule', 'API\SettingAPIController@saveCycleCountSchedule');
        Route::post('inventory-schedule', 'API\SettingAPIController@saveInventoryCountSchedule');
        Route::post('alert-weighing-system-schedule', 'API\SettingAPIController@saveAlertWeighingSystemSchedule');
        Route::post('save-by-key', 'API\SettingAPIController@saveByKey');
        Route::get('get-by-key', 'API\SettingAPIController@getByKey');
    });

    Route::group(['prefix' => 'torque-areas'], function() {
        Route::get('/', 'API\AdminAPIController@getTorqueWrenchAreas');
        Route::post('/create', 'API\AdminAPIController@createTorqueWrenchArea');
        Route::delete('/delete', 'API\AdminAPIController@deleteTorqueWrenchArea');
        Route::put('/update', 'API\AdminAPIController@updateTorqueWrenchArea');
    });

    Route::group(['prefix' => 'configure'], function() {
        Route::group(['prefix' => 'clusters'], function() {
            Route::get('/', 'API\WarehouseAPIController@getClusters');
            Route::get('/info', 'API\WarehouseAPIController@getClusterInfo');
            Route::post('/create', 'API\WarehouseAPIController@createCluster');
            Route::put('/update', 'API\WarehouseAPIController@updateCluster');
            Route::put('/update-virtual', 'API\WarehouseAPIController@updateVirtualCluster');
            Route::delete('/delete', 'API\WarehouseAPIController@deleteCluster');
        });

        Route::group(['prefix' => 'shelfs'], function() {
            Route::get('/', 'API\AdminAPIController@getShelfs');
            Route::get('/info', 'API\AdminAPIController@getShelfInfo');
            Route::post('/create', 'API\AdminAPIController@createShelf');
            Route::put('/update', 'API\AdminAPIController@updateShelf');
            Route::delete('/delete', 'API\AdminAPIController@deleteShelf');
        });

        Route::group(['prefix' => 'spares'], function() {
            Route::get('/', 'API\AdminAPIController@getSpares');
            Route::get('/by-mpn', 'API\AdminAPIController@getSpareByMpn');
            Route::get('/by-pn', 'API\AdminAPIController@getSpareByPartNo');
            Route::get('/unassigned', 'API\AdminAPIController@getSparesUnassigned');
            Route::get('/assigned-bin', 'API\AdminAPIController@getSparesAssignedBin');
            Route::get('/issuing', 'API\AdminAPIController@getItemsForIssuing');
            Route::post('/create', 'API\AdminAPIController@createSpare');
            Route::post('/update', 'API\AdminAPIController@updateSpare');
            Route::delete('/delete', 'API\AdminAPIController@deleteSpare');
            Route::post('/import', 'API\AdminAPIController@importSpares');
            Route::get('/types', 'API\AdminAPIController@getSpareTypes');
            Route::get('/export', 'API\AdminAPIController@exportSpares');
        });

        Route::group(['prefix' => 'bins'], function() {
            Route::get('/', 'API\AdminAPIController@getBins');
             Route::get('/{id}', 'API\AdminAPIController@getBinId');
            Route::get('/dashboard', 'API\AdminAPIController@getBinsSummary');
            Route::put('/update', 'API\AdminAPIController@updateBin');
            Route::put('/unassigned', 'API\AdminAPIController@unassignedBin');
            Route::put('/patch', 'API\AdminAPIController@patchBin');
        });
    });

    Route::group(['prefix' => 'vehicles'], function() {
        Route::get('/', 'API\AdminAPIController@getVehicles');
        Route::get('/export-excel-vehicles', 'API\AdminAPIController@exportExcelVehicles');
        Route::get('/info', 'API\AdminAPIController@getVehicleInfo');
        Route::get('/statistic', 'API\AdminAPIController@getVehicleStatistic');
        Route::get('/statistic/monthly', 'API\AdminAPIController@getVehicleStatisticMonthly');
        Route::post('/create', 'API\AdminAPIController@createVehicle');
        Route::put('/update', 'API\AdminAPIController@updateVehicle');
        Route::post('/revert', 'API\AdminAPIController@revertVehicle');
        Route::delete('/delete', 'API\AdminAPIController@deleteVehicle');
    });

    Route::group(['prefix' => 'vehicle-types'], function() {
        Route::get('/', 'API\AdminAPIController@getVehicleTypes');
        Route::get('/info', 'API\AdminAPIController@getVehicleTypeInfo');
        Route::post('/create', 'API\AdminAPIController@createVehicleType');
        Route::put('/update', 'API\AdminAPIController@updateVehicleType');
        Route::delete('/delete', 'API\AdminAPIController@deleteVehicleType');
    });

    Route::group(['prefix' => 'job-cards'], function() {
        Route::get('/', 'API\AdminAPIController@getJobCards');
        Route::get('/closed-job-cards', 'API\AdminAPIController@getClosedJobCards');
        Route::get('/info', 'API\AdminAPIController@getJobCardInfo');
        Route::get('/by-card-num', 'API\AdminAPIController@getJobCardByCardNo');
        Route::post('/create', 'API\AdminAPIController@createJobCard');
        Route::put('/update', 'API\AdminAPIController@updateJobCard');
        Route::delete('/delete', 'API\AdminAPIController@deleteJobCard');
        Route::put('/closed', 'API\AdminAPIController@closedJobCard');
        Route::post('/scan-barcode', 'API\ScanBarcodeController@scanBarcode');
        Route::post('/finished-scan-barcode', 'API\ScanBarcodeController@finishedScanBarcode');
    });

    Route::group(['prefix' => 'euc-lists'], function() {
        Route::get('/', 'API\AdminAPIController@getEucList');
        Route::post('/create', 'API\AdminAPIController@createEuc');
        Route::post('/create-uec', 'API\AdminAPIController@createOnlyEuc');
        Route::put('/update', 'API\AdminAPIController@updateEuc');
        Route::put('/update-items-euc/{eucBoxId}', 'API\AdminAPIController@updateItemsEuc');
        Route::put('/update-uec', 'API\AdminAPIController@updateOnlyEuc');
        Route::delete('/delete', 'API\AdminAPIController@deleteEuc');
    });

    Route::group(['prefix' => 'issue-card'], function() {
        Route::post('/', 'API\IssueAPIController@issueCard');
        Route::post('/create-link-mo', 'API\IssueAPIController@createLinkMO');
        Route::put('/update-link-mo', 'API\IssueAPIController@updateLinkMO');
        Route::delete('/delete-link-mo', 'API\IssueAPIController@deleteLinkMO');
        // Route::get('/histories', 'API\IssueAPIController@getIssueCardHistories');
    });

    Route::group(['prefix' => 'issue-spares'], function() {
        Route::post('/', 'API\IssueAPIController@issueSpares');
    });

    Route::group(['prefix' => 'returns'], function() {
        Route::get('/spares', 'API\ReturnAPIController@getSparesReturn');
        Route::put('/store', 'API\ReturnAPIController@returnToStore');
        Route::put('/store/auto-bin', 'API\ReturnAPIController@returnToStoreAutoBin');
        Route::put('/handover', 'API\ReturnAPIController@handOverSpares');
    });

    Route::group(['prefix' => 'replenish'], function() {
        Route::get('/spares', 'API\ReplenishAPIController@getReplenishSpares');
        Route::post('/manual', 'API\ReplenishAPIController@replenishManual');
        Route::post('/manual/auto-bin', 'API\ReplenishAPIController@replenishManualAutoBin');
        Route::post('/manual-euc', 'API\ReplenishAPIController@replenishManualForEuc');
        Route::get('/auto', 'API\ReplenishAPIController@getReplenishAutoList');
        Route::get('/auto/info', 'API\ReplenishAPIController@getReplenishAutoByUuid');
        Route::post('/auto/create', 'API\ReplenishAPIController@replenishAuto');
        Route::put('/auto/confirm', 'API\ReplenishAPIController@confirmReplenishAuto');
        Route::put('/auto/confirm-tablet', 'API\ReplenishAPIController@confirmReplenishAutoTablet');
        Route::delete('/auto/delete', 'API\ReplenishAPIController@deleteReplenishAuto');
        Route::delete('/auto/remove-spare-by-bin', 'API\ReplenishAPIController@removeSpareByBinReplenishAuto');
    });

    Route::group(['prefix' => 'taking-transaction'], function() {
        Route::post('/', 'API\TakingTransactionAPIController@takingTransaction');
        Route::post('/create-weighing-trans', 'API\TakingTransactionAPIController@createWeighingTransaction');
    });

    Route::group(['prefix' => 'cycle-count'], function() {
        Route::post('/generate', 'API\PhysicalAPIController@generateCycleCount');
    });

    Route::get('/euc-histories', 'API\ReplenishAPIController@getEucItemHistories');

    Route::group(['prefix' => 'pol-management'], function() {
        Route::get('/', 'API\PolManagementAPIController@getPolManagements');
        Route::get('/info', 'API\PolManagementAPIController@getPolManagementInfo');
        Route::post('/create', 'API\PolManagementAPIController@createPolManagement');
        Route::put('/update', 'API\PolManagementAPIController@updatePolManagement');
        Route::delete('/delete', 'API\PolManagementAPIController@deletePolManagements');
        Route::post('/issue', 'API\PolManagementAPIController@issuePol');
        Route::post('/replenish', 'API\PolManagementAPIController@replenishPol');
        Route::get('/histories', 'API\PolManagementAPIController@getPolHistories');
    });

    Route::group(['prefix' => 'weight'], function() {
        Route::get('/list-sites', 'API\WeightSystemAPIController@getListSites');
        Route::get('/get-bins/{shelfId}', 'API\WeightSystemAPIController@getBinsOfShelf');
        Route::put('/update-bin', 'API\WeightSystemAPIController@updateBin');
        Route::get('/transactions-weighing-system', 'API\WeightSystemAPIController@transactionsWeighingSystem');
    });

    Route::get('/get-admin-all-data', 'API\AdminAPIController@getAdminCabinetData');
    Route::get('/search-job-card', 'API\IssueAPIController@searchToolRoomJobCard');
    Route::post('/save-job-card', 'API\IssueAPIController@addRoomJobCard');
    Route::get('/get-issue-data-list', 'API\IssueAPIController@getIssueItemData');
    Route::get('/search-return-product-item', 'API\ReturnAPIController@searchReturnProductItem');
    Route::get('/notify-return-item-info', 'API\ReturnAPIController@getReturnItemInfo');
    Route::post('/check-processing-bin', 'API\AdminAPIController@checkProcessingBin');
    Route::post('/unlock-processing-bin', 'API\AdminAPIController@unlockProcessingBin');
});