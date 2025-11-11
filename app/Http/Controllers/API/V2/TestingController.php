<?php
namespace App\Http\Controllers\API\V2;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
use App\NotifyCustomer;
use App\Services\FCMService;
use Auth;
use Illuminate\Http\Request;
use JWTAuth;
use Mail;
use Response;
use Tymon\JWTAuth\Middleware\GetUserFromToken;
use Validator;

class TestingController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    /**
     * Display All Customers
     */
    public function testingFcmToken(Request $request)
    {
        $customer = Customers::where('mobile_number', $request->mobile_number)->first();
        if($customer) {
            NotifyCustomer::send_notification('password_expired','', $customer);
            return response()->json([
                'status_code' => 200,
                'message' => 'Notification Send successfully!'
            ]);
        } else {
            return response()->json([
                'status_code' => 403,
                'message' => 'Incorrect Mobile Number!'
            ]);
        }
    }

    public function sendNotification2()
    {
        $servicerequest = ArchiveServiceRequests::where('id' ,1)->first();
        $customers = Customers::where('id' , 8987)->first();

        NotifyCustomer::send_notification('send_app_update_notification', $servicerequest, $customers);

        /*die;
        $deviceToken ='cmPPuNxGJKM:APA91bGS0RZIAWnoS1CicnDWVWlKZXevhFezgNRB3yRWDvaW522KXPSsDVStP_xGhki6x2LVkq2umzYmT7gzqmHgvLhDGqquGf6X2Yk5BGf_35F_RQJ8274OFenCxnHotLlkUwNeTb77';

        $notification = array(
            'title' => "Test Notification",
            'body' => 'Test payload',
            //'image' => 'https://app.tacobell.co.in/storage/free_crispy_potato_taco.jpeg',
        );

        $data = [];

        $data['noti_type'] = 'order_status';
        $data['message'] = "Test Notification";
        $data['title'] = 'Test payload';

        $responses = $this->fcmService->sendMessage($deviceToken, $notification, $data);

        foreach ($responses as $response) {
            if ($response['state'] === 'fulfilled') {
                return response()->json([
                    'message' => 'Message sent successfully: ' . $response['value']->getBody() . PHP_EOL
                ]);
                //return 'Message sent successfully: ' . $response['value']->getBody() . PHP_EOL;
            } else {
                $reason = $response['reason'];
                if ($reason instanceof \GuzzleHttp\Exception\RequestException) {
                    return response()->json([
                        'message' => 'Request failed: ' . $reason->getMessage() . PHP_EOL
                    ]);
                    //return 'Request failed: ' . $reason->getMessage() . PHP_EOL;
                    if ($reason->hasResponse()) {
                        return response()->json([
                            'message' => 'Response: ' . $reason->getResponse()->getBody() . PHP_EOL
                        ]);
                        //return 'Response: ' . $reason->getResponse()->getBody() . PHP_EOL;
                    }
                } else {
                    return response()->json([
                        'message' => 'Failed to send message: ' . $reason->getMessage() . PHP_EOL
                    ]);
                    //return 'Failed to send message: ' . $reason->getMessage() . PHP_EOL;
                }
            }
        }
        //dd($response->json()['name']);
        //return $response;

        //return response()->json($response);*/
    }
}
