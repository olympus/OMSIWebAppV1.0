<?php

namespace App\Http\Controllers;

use App\Mail\SendPasswordChanged;
use App\Models\Customers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mail;
use Response;
use Validator;

class CustomerDataController extends Controller
{

    public function customerDeleteAccount(Request $request){
        $rules = [
            'email' => 'required|exists:customers,email',
        ];
        $messages = [
            'email.exists' => 'Sorry, no account with this email exists.',
        ];
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() )
        {
            return response()->json(['message' => $validator->errors()->first(),  'status' => 203 ]);
        }else{
            $customer = Customers::where('email', $request->email)->whereNull('deleted_at')->first();
            if($customer) {
                Customers::where('id', $customer->id)->update([
                    'is_deleted' => 1,
                    'deleted_at' => Carbon::now()
                ]);
                return response()->json([
                    'status_code' => 200,
                    'status' => 200,
                    'message' => 'success',
                ]);
            }else {
                return response()->json([
                    'status_code' => 202,
                    'status' => 202,
                    'message' => 'Not found',
                ]);
            }
        }
    }

    public function customerPasswordUpdate(){
        $customer = Customers::where(['is_password_changed' => 0, 'email' => 'ritik.bansal@lyxelandflamingo.com'])->whereNull('deleted_at')->get();
        foreach($customer as $customers){
            if($customers) {
                $old_pass = $customers->password;
                Customers::where('id', $customers->id)->update([
                    'old_password' => $old_pass,
                    'password' => Hash::make('Test@12356'),
                    'is_password_changed' => 1
                ]);

                try{
                    Mail::to($customers->email)->send(new SendPasswordChanged($customers));
                }catch (Exception $e) {
                        return $e;
                }
            }
        }

        return 'success';
    }
}
