<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiRequests;
use App\Autoemail_Setting;
use App\Models\Departments;
use App\Models\AutoEmails;
use App\Models\Customers;
use App\Models\EmployeeTeam;
use App\Models\Hospitals;
use App\Models\ServiceRequests;
use App\Models\User;
use App\NotifyCustomer;
use App\Reportsetting;
use App\SettingModel;
use Cookie;
use Excel;
use Exception;
use Mobile_Detect;
use Validator;
use App\Jobs\SendKycNotificationJob;
class TestNotificationController extends Controller
{
    // public function testnotifyToUpdate()
    // {
    //     $customers = Customers::where('id', 7085)->get();
    //     $servicerequest = ServiceRequests::find(61);
    //     foreach ($customers as $customer) {
    //         NotifyCustomer::send_new_notification('app_update_available', null, $customer);
    //     }
    //     return 'success';
    // }

    public function testnotifyToUpdate()
    {
        $customers = Customers::whereIn('id', [7085, 7053])->get();
        foreach ($customers as $customer) {
            //NotifyCustomer::send_final_notification('app_update_available', null, $customer);
            //NotifyCustomer::send_final_notification('product', null, $customer);
            //NotifyCustomer::send_final_notification('new_product', null, $customer);
            //NotifyCustomer::send_final_notification('category', null, $customer);
            NotifyCustomer::send_final_notification('sub_category', null, $customer);
            // NotifyCustomer::send_final_notification('speciality', null, $customer);
            // NotifyCustomer::send_final_notification('sub_speciality', null, $customer);
            // NotifyCustomer::send_final_notification('video', null, $customer);
            // NotifyCustomer::send_final_notification('video_detail', null, $customer);
            // NotifyCustomer::send_final_notification('roi_calculator', null, $customer);
        }
        return 'success';
    }


    public function testNotification(Request $request)
    {
        // ✅ Required validation
        $request->validate([
            'type' => 'required|string',
            'customer_ids' => 'required'
        ]);

        $type = $request->type;
        $customerIds = $request->customer_ids;

        // string → array (7085,7053)
        if (is_string($customerIds)) {
            $customerIds = explode(',', $customerIds);
        }

        // ensure array
        if (!is_array($customerIds)) {
            return response()->json([
                'error' => 'customer_ids must be array or comma separated'
            ], 400);
        }

        // fetch customers
        $customers = Customers::whereIn('id', $customerIds)->get();

        if ($customers->isEmpty()) {
            return response()->json([
                'error' => 'No customers found'
            ], 404);
        }

        // send notification
        foreach ($customers as $customer) {
            NotifyCustomer::send_final_notification($type, null, $customer);
        }

        return response()->json([
            'status' => 'success',
            'type' => $type,
            'customers' => $customers->pluck('id')
        ]);
    }

    public function testKycNotification(Request $request)
    {
        // ✅ validation
        $request->validate([
            'customer_id' => 'required|integer',
            'type' => 'required|string', // blocked OR reminder
            'days_left' => 'nullable|integer'
        ]);

        $customer = Customers::find($request->customer_id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // device token (adjust field name if different)
        $deviceToken = $customer->device_token ?? null;

        if (empty($deviceToken)) {
            return response()->json(['error' => 'Device token missing'], 400);
        }

        // default days
        $daysLeft = $request->days_left ?? 15;

        // ✅ dispatch job
        SendKycNotificationJob::dispatch(
            $customer->id,
            $deviceToken,
            $daysLeft,
            $request->type
        );

        return response()->json([
            'status' => 'success',
            'message' => 'KYC notification job dispatched',
            'customer_id' => $customer->id,
            'type' => $request->type,
            'days_left' => $daysLeft
        ]);
    }

    public function testAcknowledgementManual(Request $request)
    {
        // ✅ validation
        $request->validate([
            'customer_id' => 'required|integer',
            'type' => 'required|in:3_days,5_days',
            'service_request_id' => 'nullable|integer'
        ]);

        $customer = Customers::find($request->customer_id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // optional SR
        $serviceRequest = null;

        if ($request->service_request_id) {
            $serviceRequest = ServiceRequests::find($request->service_request_id);

            if (!$serviceRequest) {
                return response()->json(['error' => 'Service Request not found'], 404);
            }
        }

        // type mapping
        $notificationType = $request->type == '3_days'
            ? 'request_acknowledgement_after_3_days'
            : 'request_acknowledgement_after_5_days';

        // ✅ send notification
        NotifyCustomer::send_new_notification(
            $notificationType,
            $serviceRequest,
            $customer
        );

        return response()->json([
            'status' => 'success',
            'customer_id' => $customer->id,
            'service_request_id' => $serviceRequest?->id,
            'type' => $notificationType
        ]);
    }
}
