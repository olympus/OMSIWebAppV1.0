<?php

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoiCalculator;
use App\Models\RoiCalculatorSection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\RoiOtp;
use Carbon\Carbon;
class ROICalculatorController extends Controller
{

    public function submitROICalculatorDetails(Request $request)
    {
        //Log::info('ROI Calculator API Payload', $request->all());

        /*
        |--------------------------------------------------------------------------
        | Auth Check
        |--------------------------------------------------------------------------
        */

        /*$user = auth('customer-api')->user();

        if (!$user) {

            return response()->json([
                'status_code' => 401,
                'message' => 'User not authenticated'
            ], 401);

        }*/


        /*
        |--------------------------------------------------------------------------
        | Password Expired Check
        |--------------------------------------------------------------------------
        */

        /*if (!empty($user->is_expired)) {
            return response()->json([
                'status_code' => 407,
                'message' => 'Password expired'
            ], 407);
        }*/


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'mobile' => [
                'required',
                'regex:/^[0-9]{10}$/'
            ], 
        ], [
            //
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if($request->type == "send_otp"){
            $otp = rand(100000,999999);

            RoiOtp::updateOrCreate(
                ['mobile' => $request->mobile],
                [
                    'otp' => $otp,
                    'created_at' => now()
                ]
            );

            $otp_data = (object)array(
                "mobile_number" => $request->mobile,
                "mobile_otp" => $otp,
            );

            // Here you call SMS API
            
            sendCustomerMobileSms('send_otp', $otp_data, "", "");  

            return response()->json([
                'status_code' => 200,
                'message' => 'OTP sent successfully', 
            ], 200); 

        }elseif($request->type == "send_data"){ 
           
            $validator = Validator::make($request->all(), [
                'type' => 'required',
                'name' => [
                    'bail',
                     'required',
                    'regex:/^[A-Za-z ]+$/',
                    'max:255'
                ],
                'email' => [
                    'required',
                    'email',
                    'max:255'
                ],
                'mobile' => [
                    'required',
                    'regex:/^[0-9]{10}$/'
                ],
                'otp' => 'required|digits:6',

                // 'hospital_name' => 'required|string|max:255',
                // 'speciality' => 'required|string|max:255',
                // 'state' => 'required|string|max:255',
                // 'city' => 'required|string|max:255',
                // 'pincode' => [
                //     'required',
                //     'digits:6'
                // ],
                // 'customer_status' => 'required|string|max:255',
                // 'processor_profile' => 'required|string|max:255',
                // 'endoscopy_suite' => 'required|string|max:255',
                // 'procedure_performer' => 'required|string|max:255',
                // 'procedures_performed' => 'required|string|max:255',
            ], [
                'name.required' => 'Name is required',
                'name.regex' => 'Name must contain only letters',
                'email.required' => 'Email is required',
                'email.email' => 'Invalid email',
                'mobile.required' => 'Mobile number is required',
                'mobile.regex' => 'Mobile must be 10 digits',
                // 'pincode.required' => 'Pincode is required',
                // 'pincode.digits' => 'Pincode must be 6 digits',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()->first()
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Store Data
            |--------------------------------------------------------------------------
            */
        
            try {

                $otpData = RoiOtp::where('mobile',$request->mobile)->where('otp',$request->otp)->first();

                if(!$otpData){
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'Invalid OTP', 
                    ], 200);  
                }

                // check expiry (5 minutes)
                if(Carbon::parse($otpData->created_at)->addMinutes(10)->isPast()){
                    
                    return response()->json([
                        'status_code' => 203,
                        'message' => 'OTP expired', 
                    ], 200);  

                }

                $roi = RoiCalculator::create([
                    //'customer_id' => $user->id,
                    'name' => trim($request->name),
                    'email' => trim($request->email),
                    'mobile' => trim($request->mobile),
                    'hospital_name' => trim($request->hospital_name),
                    'speciality' => trim($request->speciality),
                    'state' => trim($request->state),
                    'city' => trim($request->city),
                    'pincode' => trim($request->pincode),
                    'customer_status' => trim($request->customer_status),
                    'processor_profile' => trim($request->processor_profile),
                    'endoscopy_suite' => trim($request->endoscopy_suite),
                    'procedure_performer' => trim($request->procedure_performer),
                    'procedures_performed' => trim($request->procedures_performed),
                ]);

                return response()->json([
                    'status_code' => 200,
                    'message' => 'ROI Calculator submitted successfully', 
                ], 200);

            } catch (\Exception $e) {
                Log::error('ROI Calculator Error', [
                    'message' => $e->getMessage()
                ]);
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Something went wrong'
                ], 500);
            }
        }
    }

    // VERIFY OTP
    public function verifyROIOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp' => 'required|digits:6'
        ]);

        $otpData = RoiOtp::where('mobile',$request->mobile)
                        ->where('otp',$request->otp)
                        ->first();

        if(!$otpData){
            return response()->json([
                'status_code' => 203,
                'message' => 'Invalid OTP', 
            ], 200);  
        }

        // check expiry (5 minutes)
        if(Carbon::parse($otpData->created_at)->addMinutes(10)->isPast()){
            
            return response()->json([
                'status_code' => 203,
                'message' => 'OTP expired', 
            ], 200);  

        }

        return response()->json([
            'status_code' => 200,
            'message' => 'OTP verified successfully', 
        ], 200);   
    }

    public function getSectionData()
    {   
        /*
        |--------------------------------------------------------------------------
        | Auth Check
        |--------------------------------------------------------------------------
        */

        /*$user = auth('customer-api')->user();

        if (!$user) {

            return response()->json([
                'status_code' => 401,
                'message' => 'User not authenticated'
            ], 401);

        }*/


        /*
        |--------------------------------------------------------------------------
        | Password Expired Check
        |--------------------------------------------------------------------------
        */

        /*if (!empty($user->is_expired)) {
            return response()->json([
                'status_code' => 407,
                'message' => 'Password expired'
            ], 407);
        }*/
        
        $data = RoiCalculatorSection::where('status',1)->latest()->first();

        $speciality = [
            'Gastroenterology', 
            'Pulmonology', 
            'Gastroenterology & Pulmonology'
        ];

        $customer_status = [
            'New Customer', 
            'Existing Olympus Customer'
        ];

        $processor_profile  = [
            'CV190',
            'CV170',
            'CV1500',
            'Axeon CV-V1',
            'CV1500 + CV190'
        ];
        
        $processor_performed  = [
            'Upper GI',
            'Advanced UGI',
            'Lower GI', 
            'Advanced LGI',
            'ERCP',
            'EUS',
            'Enteroscopy',
            'ESD/POEM',
            'Bariatric endoscopy',
            'Others',
            'Basic Scopy',
            'Advanced Scopy',
            'Critical Care / Intrubation',
            'EBUS',
            'Thoracoscopy' 
        ];

        $no_of_endoscopy_suites = [
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10,
            11,
            12
        ];

        $no_of_procedure_performed = [
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10,
            11,
            12
        ];

        if(!$data){
            return response()->json([
                'status_code' => 404,
                'message' => 'No Data Found'
            ]); 
        } 
        return response()->json([
            'status_code'=>200,
            'message' => 'Data found successfully',
            'speciality'=> $speciality,
            'customer_status'=> $customer_status,
            'processor_profile'=> $processor_profile,
            'processor_performed'=> $processor_performed,
            'no_of_endoscopy_suites'=> $no_of_endoscopy_suites,
            'no_of_procedure_performed'=> $no_of_procedure_performed,
            'data'=> $data,
        ]);

    }

}
