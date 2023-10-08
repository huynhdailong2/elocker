<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route::get('/mqtt/publish/{topic}/{message}', 'MqttExampleController@SendMsgViaMqtt');
// Route::get('/mqtt/publish/{topic}', 'MqttExampleController@SubscribetoTopic');

// Route::post('/mqtt/publish/{topic}/{message}', 'PCBController@SendMsgViaMqtt');
// Route::get('/mqtt/publish/{topic}', 'PCBController@subscribeTopic');

Route::get('/spares-request/export/{sparesRequestId}', 'SparesRequestController@exportSparesRequest');

Route::get('/system-logs/download', 'SystemLogAPIController@downloadSystemLogs');

Route::get('/spares-tnx/export', 'API\ReportAPIController@exportSparesReportByTnx');

Route::get('/spares-expiring/export', 'API\ReportAPIController@exportSparesReportByExpiring');

Route::get('/spares-loan/export', 'API\ReportAPIController@exportSparesReportByLoan');

Route::get('/spares-returns/export', 'API\ReportAPIController@exportSparesReportByReturns');

Route::get('/write-off/export', 'API\ReportAPIController@exportSparesReportByWriteOff');

Route::get('/torque-wrench/export', 'API\ReportAPIController@exportSparesReportByTorqueWrench');

Route::get('/weighing-system/export', 'API\ReportAPIController@exportWeighingSystemTransaction');

Route::get('/spare-list-refs/export', 'SparesRequestController@exportSparesListRef');

Route::get('/{view?}', 'HomeController@index')->where('view', '(.*)');
