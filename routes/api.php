<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerDataController;
use App\Http\Controllers\CustomersApiController;
use App\Http\Controllers\DepartmentsApiController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\PromailerController;
use App\Http\Controllers\VideosController;
use App\Http\Controllers\SFDCController;

use App\Http\Controllers\AutoEmailImportController;


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

// v3 controllers
use App\Http\Controllers\API\V3\CustomersApiController as V3CustomersApiController;
use App\Http\Controllers\API\V3\DepartmentsApiController as V3DepartmentsApiController;
use App\Http\Controllers\API\V3\ServiceRequestController as V3ServiceRequestController;
use App\Http\Controllers\API\V3\PromailerController as V3PromailerController;
use App\Http\Controllers\API\V3\VideosController as V3VideosController;
use App\Http\Controllers\API\V3\SFDCController as V3SFDCController;
use App\Http\Controllers\API\V3\TestingController as V3TestingController;
use App\Http\Controllers\API\V3\DuplicateCallController as V3DuplicateCallController;
use App\Http\Controllers\API\V3\RequestAcknowledgement as V3RequestAcknowledgement;
use App\Http\Controllers\API\V3\SFDCDataUpdateAPIController as V3SFDCDataUpdateAPIController;
use App\Http\Controllers\API\V3\CustomerController as V3CustomerController;
use App\Http\Controllers\API\V3\CustomerCommonController as V3CustomerCommonController;
use App\Http\Controllers\API\V3\PromailerApiController as V3PromailerApiController;
use App\Http\Controllers\API\V3\VideosApiController as V3VideosApiController;
use App\Http\Controllers\API\V3\CategoryController as V3CategoryController;
use App\Http\Controllers\API\V3\SpecialityController as V3SpecialityController;
use App\Http\Controllers\API\V3\ProductController as V3ProductController;
use App\Http\Controllers\API\V3\HomeControllerAPI as V3HomeControllerAPI;
use App\Http\Controllers\API\V3\TestimonialController as V3TestimonialController;
use App\Http\Controllers\API\V3\RequestAPIController as V3RequestAPIController;
use App\Http\Controllers\API\V3\ROICalculatorController as V3ROICalculatorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/auto-email-import-update', [AutoEmailImportController::class, 'updateFromCsv']);


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



/*
|--------------------------------------------------------------------------
| v3 Routes
|--------------------------------------------------------------------------
*/

//----------------------------------------Version 3 API -----------------------------------------------------------------//

// Route::prefix('v3')->middleware(['jwt.verify'])->group(function () {
//     Route::post('customer/delete_account', [V3CustomersApiController::class, 'customerDeleteAccount']);
// });

/*Route::prefix('v3')->group(function () {
    //New API
        Route::post('customer-sign-up', [V3CustomerController::class, 'customerSignUp']);
        Route::post('customer/customer-sign-up-resend-otp', [V3CustomerController::class, 'customerSignUpResendOtp']);
        Route::post('customer/account-verification', [V3CustomerController::class, 'accountVerification']);
        Route::post('customer/login', [V3CustomerController::class, 'login']);
        Route::post('customer/send-otp-before-login', [V3CustomerController::class, 'sendOtpBeforeLogin']);
        Route::post('departments', [V3CustomerController::class, 'departmentsList']);
        Route::post('customer/update-password', [V3CustomerController::class, 'customerPasswordUpdate']);
        Route::post('get-sfdc-otp-code', [V3RequestAPIController::class, 'sendRequestAcknowledgementOtp']); 
        
        //Route::resource('departments', V3DepartmentsApiController::class)->only(['index']);
        //Route::resource('customer', V3CustomersApiController::class)->only(['store']);    
        //Route::post('customer/login', [V3CustomersApiController::class, 'login']);
        //Route::post('customer/forgetpwd_send_otp', [V3CustomersApiController::class, 'forgetpwd_send_otp']);
        //Route::post('customer/forgetpwd_otp_verify', [V3CustomersApiController::class, 'forgetpwd_otp_verify']);
        //Route::post('customer/password_update', [V3CustomersApiController::class, 'password_update']);
        //Route::post('testing_password_status_change', [V3CustomersApiController::class, 'testingPasswordStatusChange']);
        //Route::post('testing_fcm_token', [TestingController::class, 'testingFcmToken']);
        //Route::post('customer/temp-resend-pwd-otp', [V3CustomersApiController::class, 'temp_resend_pwd_otp']);
        //Route::post('testing_password_status_change_web', [V3CustomersApiController::class, 'testingPasswordStatusChangeWeb']);
        
        //Route::post('check_password_validation', [V3CustomersApiController::class, 'checkPasswordValidation']);
        
        //Route::post('get-open-service-request-list', [DuplicateCallController::class, 'getOpenServiceRequestList']);
        
        //Route::post('customer-submit-service-request-reminder', [DuplicateCallController::class, 'customerSubmitServiceRequestReminder']);
        
        //Route::post('get-sfdc-otp-code', [RequestAcknowledgement::class, 'sendRequestAcknowledgementOtp']); 
});*/

/*Route::prefix('v3')->middleware(['jwt.verify'])->group(function () {

    //NEW API
        Route::post('mpin-biometric-update', [V3CustomerController::class, 'mpinBiometricUpdate']);
        Route::post('customer/update-customer-type', [V3CustomerController::class, 'customerUpdateCustomerType']);
        Route::post('customer/update-profile', [V3CustomerController::class, 'customerUpdateProfile']);
        Route::post('check-password-expired-status', [V3CustomerController::class, 'checkPasswordExpiredStatus']);
        Route::post('customer/logout', [V3CustomerController::class, 'customerLogout']);
        Route::post('history-count', [V3CustomerController::class, 'historyCount']);
        Route::post('get-all-promailers-list', [V3PromailerApiController::class, 'getAllPromailersList']);
        Route::post('get-promailer-data', [V3PromailerApiController::class, 'getPromailerData']);
        Route::post('customer-view-promailer', [V3PromailerApiController::class, 'customerViewPromailer']);
        Route::post('get-all-video-list', [V3VideosApiController::class, 'getAllVideoList']);
        Route::post('get-video-data/{video}', [V3VideosApiController::class, 'getVideoData']);
        Route::post('customer-watched-video/{video}/{customer}', [V3VideosApiController::class, 'customerWatchedVideo']); 

        Route::post('customer-delete-account', [V3CustomerController::class, 'customerDeleteAccount']);

        Route::post('get-category-list', [V3CategoryController::class, 'categoryList']);
        Route::post('get-sub-category-list', [V3CategoryController::class, 'subCategoryList']);
        

        Route::post('get-speciality-list', [V3SpecialityController::class, 'specialityList']);
        Route::post('get-sub-speciality-list', [V3SpecialityController::class, 'subSpecialityList']);


        Route::post('get-category-list-by-speciality', [V3SpecialityController::class, 'categoryListBySpecialityAndSubSpeciality']);
        
        Route::post('get-product-list', [V3ProductController::class, 'productList']);
        Route::post('get-product-details', [V3ProductController::class, 'productDetails']);

        Route::post('home-page-api', [V3HomeControllerAPI::class, 'homePageAPI']);

        Route::post('get-testimonial-list', [V3TestimonialController::class, 'testimonialList']);

        Route::post('create-request-ticket', [V3RequestAPIController::class, 'createRequestTicket']);
        
        Route::post('get-request-history', [V3RequestAPIController::class, 'getRequestHistory']);
        Route::post('get-request-history-detail', [V3RequestAPIController::class, 'getRequestHistoryDetail']);

        Route::post('submit-request-feedback', [V3RequestAPIController::class, 'submitRequestFeedback']);

        Route::post('get-open-service-request-list', [V3RequestAPIController::class, 'getOpenServiceRequestList']);

        Route::post('customer-submit-service-request-reminder', [V3RequestAPIController::class, 'customerSubmitServiceRequestReminder']);

        Route::post('customer-request-acknowledge', [V3RequestAPIController::class, 'customerRequestAcknowledgement']);

        Route::post('verify-request-acknowledgement-happy-code', [V3RequestAPIController::class, 'verifyRequestAcknowledgementHappyCode']);

        //Route::post('password_status', [V3CustomersApiController::class, 'password_status']);
        //Route::resource('customer', V3CustomersApiController::class)->only(['show', 'update']);
        //Route::post('promailersLatest', [V3PromailerController::class, 'promailersLatest']);
        //Route::post('getPromailer', [V3PromailerController::class, 'getPromailer']);
        //Route::get('historyCount/{id}', [V3ServiceRequestController::class, 'history_count']);
        //Route::get('videos', [V3VideosController::class, 'index_api']);
        //Route::get('videos/{video}', [V3VideosController::class, 'show_api']);
        //Route::get('video/{video}/{customer}', [V3VideosController::class, 'watched']);
        //Route::post('customer/logout', [V3ServiceRequestController::class, 'logout']);
        //Route::post('promailer-show', [V3PromailerController::class, 'showPromailer']);


        //Route::resource('service', V3ServiceRequestController::class)->except(['create', 'edit', 'destroy']);
        // Route::post('getRequestHistory', [V3ServiceRequestController::class, 'get_request_history']);
        // Route::post('getRequestsHistory', [V3ServiceRequestController::class, 'get_requests_history']);

        //Route::post('customer/otp_resend', [V3CustomersApiController::class, 'otp_resend']);
        //Route::post('customer/send_otp', [V3CustomersApiController::class, 'send_otp']);
        //Route::post('customer/password_opt_verify', [V3CustomersApiController::class, 'password_opt_verify']);
        
        Route::post('service/escalate', [V3ServiceRequestController::class, 'escalate']);
        //Route::post('service/feedback', [V3ServiceRequestController::class, 'feedback']);
        Route::post('sfdc/updatestatus', [V3SFDCController::class, 'updateStatus']);
        //Route::post('request-acknowledge', [V3ServiceRequestController::class, 'customerRequestAcknowledgement']);
        //Route::post('verify-request-acknowledgement-happy-code', [RequestAcknowledgement::class, 'verifyRequestAcknowledgementHappyCode']);
});*/



Route::prefix('v3')->group(function () { 
    Route::post('customer-sign-up', [V3CustomerController::class, 'customerSignUp']);
    Route::post('customer/customer-sign-up-resend-otp', [V3CustomerController::class, 'customerSignUpResendOtp']);
    Route::post('customer/account-verification', [V3CustomerController::class, 'accountVerification']);
    Route::post('customer/login', [V3CustomerController::class, 'login']);
    Route::post('customer/send-otp-before-login', [V3CustomerController::class, 'sendOtpBeforeLogin']);
    Route::post('departments', [V3CustomerController::class, 'departmentsList']);
    Route::post('customer/update-password', [V3CustomerController::class, 'customerPasswordUpdate']);
    Route::post('get-sfdc-otp-code', [V3RequestAPIController::class, 'sendRequestAcknowledgementOtp']); 
});

Route::prefix('v3')->middleware(['api_v3_auth'])->group(function () { 
    
    Route::post('get-category-list', [V3CategoryController::class, 'categoryList']);
    Route::post('get-sub-category-list', [V3CategoryController::class, 'subCategoryList']);
    Route::post('get-speciality-list', [V3SpecialityController::class, 'specialityList']);
    Route::post('get-sub-speciality-list', [V3SpecialityController::class, 'subSpecialityList']);
    Route::post('get-category-list-by-speciality', [V3SpecialityController::class, 'categoryListBySpecialityAndSubSpeciality']);
    Route::post('get-product-list', [V3ProductController::class, 'productList']);
    Route::post('get-product-details', [V3ProductController::class, 'productDetails']);
    Route::post('home-page-open-api', [V3HomeControllerAPI::class, 'homePageOpenAPI']);
    Route::post('get-testimonial-list', [V3TestimonialController::class, 'testimonialList']);

    Route::post('submit-roi-calculator-details', [V3ROICalculatorController::class, 'submitROICalculatorDetails']); 
    Route::post('verify-roi-otp', [V3ROICalculatorController::class, 'verifyROIOtp']); 
    Route::post('roi-calculator-section', [V3ROICalculatorController::class,'getSectionData']);
    
});

Route::prefix('v3')->middleware(['jwt.verify'])->group(function () { 
    Route::post('customer/kyc-account-verification', [V3CustomerController::class, 'kycAccountVerification']);
    Route::post('customer/kyc-send-otp', [V3CustomerController::class, 'kycSendOtp']);


    Route::post('home-page-api', [V3HomeControllerAPI::class, 'homePageAPI']);
    Route::post('mpin-biometric-update', [V3CustomerController::class, 'mpinBiometricUpdate']);
    Route::post('customer/update-customer-type', [V3CustomerController::class, 'customerUpdateCustomerType']);
    Route::post('customer/update-profile', [V3CustomerController::class, 'customerUpdateProfile']);
    Route::post('check-password-expired-status', [V3CustomerController::class, 'checkPasswordExpiredStatus']);
    Route::post('customer/logout', [V3CustomerController::class, 'customerLogout']);
    Route::post('history-count', [V3CustomerController::class, 'historyCount']);
    Route::post('get-all-promailers-list', [V3PromailerApiController::class, 'getAllPromailersList']);
    Route::post('get-promailer-data', [V3PromailerApiController::class, 'getPromailerData']);
    Route::post('customer-view-promailer', [V3PromailerApiController::class, 'customerViewPromailer']);
    Route::post('get-all-video-list', [V3VideosApiController::class, 'getAllVideoList']);
    Route::post('get-video-data/{video}', [V3VideosApiController::class, 'getVideoData']);
    Route::post('customer-watched-video', [V3VideosApiController::class, 'customerWatchedVideo']); 
    Route::post('customer-delete-account', [V3CustomerController::class, 'customerDeleteAccount']);
    Route::post('create-request-ticket', [V3RequestAPIController::class, 'createRequestTicket']);
    Route::post('get-request-history', [V3RequestAPIController::class, 'getRequestHistory']);
    Route::post('get-request-history-detail', [V3RequestAPIController::class, 'getRequestHistoryDetail']);
    Route::post('submit-request-feedback', [V3RequestAPIController::class, 'submitRequestFeedback']);
    Route::post('get-open-service-request-list', [V3RequestAPIController::class, 'getOpenServiceRequestList']);
    Route::post('customer-submit-service-request-reminder', [V3RequestAPIController::class, 'customerSubmitServiceRequestReminder']);
    Route::post('customer-request-acknowledge', [V3RequestAPIController::class, 'customerRequestAcknowledgement']);
    Route::post('verify-request-acknowledgement-happy-code', [V3RequestAPIController::class, 'verifyRequestAcknowledgementHappyCode']); 
    
    Route::post('request-escalate', [V3RequestAPIController::class, 'requestEscalate']); 
    Route::post('sfdc/updatestatus', [V3SFDCController::class, 'updateStatus']); 

});

/*Route::prefix('v3')->middleware(['jwt.verify'])->group(function () { 
    Route::post('mpin-biometric-update', [V3CustomerController::class, 'mpinBiometricUpdate']);
    Route::post('customer/update-customer-type', [V3CustomerController::class, 'customerUpdateCustomerType']);
    Route::post('customer/update-profile', [V3CustomerController::class, 'customerUpdateProfile']);
    Route::post('check-password-expired-status', [V3CustomerController::class, 'checkPasswordExpiredStatus']);
    Route::post('customer/logout', [V3CustomerController::class, 'customerLogout']);
    Route::post('history-count', [V3CustomerController::class, 'historyCount']);
    Route::post('get-all-promailers-list', [V3PromailerApiController::class, 'getAllPromailersList']);
    Route::post('get-promailer-data', [V3PromailerApiController::class, 'getPromailerData']);
    Route::post('customer-view-promailer', [V3PromailerApiController::class, 'customerViewPromailer']);
    Route::post('get-all-video-list', [V3VideosApiController::class, 'getAllVideoList']);
    Route::post('get-video-data/{video}', [V3VideosApiController::class, 'getVideoData']);
    Route::post('customer-watched-video', [V3VideosApiController::class, 'customerWatchedVideo']); 
    Route::post('customer-delete-account', [V3CustomerController::class, 'customerDeleteAccount']);
    Route::post('get-category-list', [V3CategoryController::class, 'categoryList']);
    Route::post('get-sub-category-list', [V3CategoryController::class, 'subCategoryList']);
    Route::post('get-speciality-list', [V3SpecialityController::class, 'specialityList']);
    Route::post('get-sub-speciality-list', [V3SpecialityController::class, 'subSpecialityList']);
    Route::post('get-category-list-by-speciality', [V3SpecialityController::class, 'categoryListBySpecialityAndSubSpeciality']);
    Route::post('get-product-list', [V3ProductController::class, 'productList']);
    Route::post('get-product-details', [V3ProductController::class, 'productDetails']);
    Route::post('home-page-api', [V3HomeControllerAPI::class, 'homePageAPI']);
    Route::post('get-testimonial-list', [V3TestimonialController::class, 'testimonialList']);
    Route::post('create-request-ticket', [V3RequestAPIController::class, 'createRequestTicket']);
    Route::post('get-request-history', [V3RequestAPIController::class, 'getRequestHistory']);
    Route::post('get-request-history-detail', [V3RequestAPIController::class, 'getRequestHistoryDetail']);
    Route::post('submit-request-feedback', [V3RequestAPIController::class, 'submitRequestFeedback']);
    Route::post('get-open-service-request-list', [V3RequestAPIController::class, 'getOpenServiceRequestList']);
    Route::post('customer-submit-service-request-reminder', [V3RequestAPIController::class, 'customerSubmitServiceRequestReminder']);
    Route::post('customer-request-acknowledge', [V3RequestAPIController::class, 'customerRequestAcknowledgement']);
    Route::post('verify-request-acknowledgement-happy-code', [V3RequestAPIController::class, 'verifyRequestAcknowledgementHappyCode']); 
    
    Route::post('request-escalate', [V3RequestAPIController::class, 'requestEscalate']); 
    Route::post('sfdc/updatestatus', [V3SFDCController::class, 'updateStatus']); 

    Route::post('submit-roi-calculator-details', [V3ROICalculatorController::class, 'submitROICalculatorDetails']); 
    Route::post('roi-calculator-section', [V3ROICalculatorController::class,'getSectionData']);
    
});*/


//common api
Route::prefix('v3')->group(function () {
    Route::post('get-otp', [V3CustomerCommonController::class, 'getOtp']);
    Route::post('customer-delete-data', [V3CustomerCommonController::class, 'customerDeleteData']);
    Route::post('customer-password-expired', [V3CustomerCommonController::class, 'customerPasswordExpired']);
});

    Route::post('video-store', [V3HomeControllerAPI::class, 'videoStore']);
