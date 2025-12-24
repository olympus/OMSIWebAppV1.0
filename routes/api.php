<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerDataController;
use App\Http\Controllers\CustomersApiController;
use App\Http\Controllers\DepartmentsApiController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\PromailerController;
use App\Http\Controllers\VideosController;
use App\Http\Controllers\SFDCController;

// v2 controllers
use App\Http\Controllers\API\V2\CustomersApiController as V2CustomersApiController;
use App\Http\Controllers\API\V2\DepartmentsApiController as V2DepartmentsApiController;
use App\Http\Controllers\API\V2\ServiceRequestController as V2ServiceRequestController;
use App\Http\Controllers\API\V2\PromailerController as V2PromailerController;
use App\Http\Controllers\API\V2\VideosController as V2VideosController;
use App\Http\Controllers\API\V2\SFDCController as V2SFDCController;
use App\Http\Controllers\API\V2\TestingController;
use App\Http\Controllers\API\V2\DuplicateCallController;
use App\Http\Controllers\API\V2\RequestAcknowledgement;
use App\Http\Controllers\API\V2\SFDCDataUpdateAPIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::get('customer/delete_account', [CustomerDataController::class, 'customerDeleteAccount']);
});

Route::middleware(['apiauth'])->prefix('v1')->group(function () {
    Route::resource('customer', CustomersApiController::class)->except(['create', 'edit', 'destroy']);
    Route::resource('departments', DepartmentsApiController::class)->only(['index']);
    Route::resource('service', ServiceRequestController::class)->except(['create', 'edit', 'destroy']);

    Route::post('getRequestHistory', [ServiceRequestController::class, 'get_request_history']);
    Route::post('getRequestsHistory', [ServiceRequestController::class, 'get_requests_history']);
    Route::post('promailersLatest', [PromailerController::class, 'promailersLatest']);
    Route::post('getPromailer', [PromailerController::class, 'getPromailer']);

    Route::get('historyCount/{id}', [ServiceRequestController::class, 'history_count']);
    Route::post('customer/login', [CustomersApiController::class, 'login']);
    Route::post('customer/otp_verify', [CustomersApiController::class, 'otp_verify']);
    Route::post('customer/otp_resend', [CustomersApiController::class, 'otp_resend']);
    Route::post('customer/send_otp', [CustomersApiController::class, 'send_otp']);
    Route::post('customer/password_update', [CustomersApiController::class, 'password_update']);
    Route::post('customer/password_opt_verify', [CustomersApiController::class, 'password_opt_verify']);
    Route::post('service/escalate', [ServiceRequestController::class, 'escalate']);
    Route::post('customer/logout', [ServiceRequestController::class, 'logout']);
    Route::post('service/feedback', [ServiceRequestController::class, 'feedback']);

    Route::get('videos', [VideosController::class, 'index_api']);
    Route::get('videos/{video}', [VideosController::class, 'show_api']);
    Route::get('video/{video}/{customer}', [VideosController::class, 'watched']);
    Route::post('sfdc/updatestatus', [SFDCController::class, 'updateStatus']);
});

/*
|--------------------------------------------------------------------------
| v2 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v2')->middleware(['jwt.verify'])->group(function () {
    Route::post('customer/delete_account', [V2CustomersApiController::class, 'customerDeleteAccount']);
});

Route::prefix('v2')->group(function () {
    Route::resource('departments', V2DepartmentsApiController::class)->only(['index']);
    Route::resource('customer', V2CustomersApiController::class)->only(['store']);

    Route::post('customer/login', [V2CustomersApiController::class, 'login']);
    Route::post('customer/forgetpwd_send_otp', [V2CustomersApiController::class, 'forgetpwd_send_otp']);
    Route::post('customer/forgetpwd_otp_verify', [V2CustomersApiController::class, 'forgetpwd_otp_verify']);
    Route::post('customer/password_update', [V2CustomersApiController::class, 'password_update']);
    Route::post('testing_password_status_change', [V2CustomersApiController::class, 'testingPasswordStatusChange']);
    Route::post('testing_fcm_token', [TestingController::class, 'testingFcmToken']);
    Route::post('customer/temp-resend-pwd-otp', [V2CustomersApiController::class, 'temp_resend_pwd_otp']);
    Route::post('testing_password_status_change_web', [V2CustomersApiController::class, 'testingPasswordStatusChangeWeb']);
    Route::post('check_password_validation', [V2CustomersApiController::class, 'checkPasswordValidation']);
    Route::post('get-open-service-request-list', [DuplicateCallController::class, 'getOpenServiceRequestList']);
    Route::post('customer-submit-service-request-reminder', [DuplicateCallController::class, 'customerSubmitServiceRequestReminder']);
    Route::post('get-sfdc-otp-code', [RequestAcknowledgement::class, 'sendRequestAcknowledgementOtp']);
    // Route::post('verify-request-acknowledgement-happy-code', [RequestAcknowledgement::class, 'verifyRequestAcknowledgementHappyCode']);
});

Route::prefix('v2')->middleware(['jwt.verify'])->group(function () {
    Route::post('password_status', [V2CustomersApiController::class, 'password_status']);
    Route::resource('customer', V2CustomersApiController::class)->only(['show', 'update']);
    Route::resource('service', V2ServiceRequestController::class)->except(['create', 'edit', 'destroy']);

    Route::post('getRequestHistory', [V2ServiceRequestController::class, 'get_request_history']);
    Route::post('getRequestsHistory', [V2ServiceRequestController::class, 'get_requests_history']);
    Route::post('promailersLatest', [V2PromailerController::class, 'promailersLatest']);
    Route::post('getPromailer', [V2PromailerController::class, 'getPromailer']);
    Route::get('historyCount/{id}', [V2ServiceRequestController::class, 'history_count']);
    Route::get('videos', [V2VideosController::class, 'index_api']);
    Route::get('videos/{video}', [V2VideosController::class, 'show_api']);
    Route::get('video/{video}/{customer}', [V2VideosController::class, 'watched']);

    Route::post('customer/otp_resend', [V2CustomersApiController::class, 'otp_resend']);
    Route::post('customer/send_otp', [V2CustomersApiController::class, 'send_otp']);
    Route::post('customer/password_opt_verify', [V2CustomersApiController::class, 'password_opt_verify']);
    Route::post('service/escalate', [V2ServiceRequestController::class, 'escalate']);
    Route::post('customer/logout', [V2ServiceRequestController::class, 'logout']);
    Route::post('service/feedback', [V2ServiceRequestController::class, 'feedback']);
    Route::post('sfdc/updatestatus', [V2SFDCController::class, 'updateStatus']);
    Route::post('promailer-show', [V2PromailerController::class, 'showPromailer']);
    Route::post('request-acknowledge', [V2ServiceRequestController::class, 'customerRequestAcknowledgement']);
    Route::post('verify-request-acknowledgement-happy-code', [RequestAcknowledgement::class, 'verifyRequestAcknowledgementHappyCode']);
});

/*
|--------------------------------------------------------------------------
| Global Routes
|--------------------------------------------------------------------------
*/

Route::post('send-notification', [TestingController::class, 'sendNotification2']);
Route::get('sfdc-data-update-api', [SFDCDataUpdateAPIController::class, 'sfdcDataUpdateAPI']);

Route::prefix('/v2')->middleware(['apiauth','jwt.verify'])->namespace('API\V2')->group(function (){
    Route::post('customer-list', 'CustomersApiController@customerList');
});