<?php
namespace App\Http\Controllers\API\V3;
use App\CustomerTemp;
use App\Models\Departments;
use App\Helpers\Helper;
use App\HospitalTemp;
use App\Http\Controllers\Controller;
use App\Mail\SendForgotOtp;
use App\Mail\SendOtp;
use App\Mail\SendResendOtp;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\User;
use App\Models\Promailer;
use App\Models\CustomerShowPromailer;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JWTAuth;
use Log;
use Mail;
use Response;
use Tymon\JWTAuth\Middleware\GetUserFromToken;
use Validator;

class PromailerApiController extends Controller
{   
       
    public function getAllPromailersList()
	{
	    // 🔐 Auth check
	    $user = auth('customer-api')->user();
	    if (!$user) {
	        return response()->json([
	            'status_code' => 400,
	            'message' => 'user not found',
	        ]);
	    }

	    // ⛔ Password expired
	    if ($user->is_expired == 1) {
	        return response()->json([
	            'status_code' => 407,
	            'message' => 'password expired',
	            'is_expired' => 1
	        ]);
	    }


        if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
            return response()->json([
                'status'  => 423,
                'status_code' => 423,
                'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
            ]);
        }

	    $customer_id = $user->id;

	    /* ================= PROMAILERS ================= */

	    $promailers = Promailer::where('status', 1)
	        ->orderBy('id', 'desc')
	        ->get(['id', 'title', 'frontimage', 'abbreviation', 'status', 'created_at', 'updated_at']);

	    // All read promailer IDs for customer (ONE QUERY)
	    $readPromailerIds = CustomerShowPromailer::where('customers_id', $customer_id)
	        ->pluck('promailers_id')
	        ->toArray();

	    // Count unread
	    $left_promailer = max($promailers->count() - count($readPromailerIds), 0);

	    /* ================= FORMAT RESPONSE ================= */

	    foreach ($promailers as $promailer) {

	        // Fix image URL safely
	        if (!empty($promailer->frontimage) && !filter_var($promailer->frontimage, FILTER_VALIDATE_URL)) {
	            $promailer->frontimage = asset('storage/' . ltrim($promailer->frontimage, '/'));
	        }

	        // Read flag
	        $promailer->is_read = in_array($promailer->id, $readPromailerIds) ? 1 : 0;
	    }

	    return response()->json([
	        'status_code' => 200,
	        'message' => 'success',
	        'count' => $left_promailer,
	        'data' => $promailers
	    ]);
	}


    /*public function getPromailerData(Request $request)
    {
        $rules = [  
            'id' => 'required|exists:promailers,id' 
        ]; 

        $messages = [ 
            'id.exists' => 'promailer not match.',
        ]; 
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() ) 
        {     
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);  
        }else{
            $user = auth('customer-api')->user();  
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){ 
                    $customers_id = $user->id; 
                    $promailer = Promailer::where('id', $request->id)->first();
                    if(is_null($promailer)){
                        return Response::json(['status_code'=>404,'data'=>'Data not found']);
                    }else{
                        $show_promailer = CustomerShowPromailer::where('promailers_id', $request->id )->where('customers_id', $customers_id)->first(); 
                        if(empty($show_promailer)){ 
                           $data = new CustomerShowPromailer();
                           $data->promailers_id = $request->id; 
                           $data->customers_id = $customers_id;
                           $data->save();  
                        } 
                        $promailer->frontimage = str_replace(storage_path('app/public'), config('app.url')."/storage/", $promailer->frontimage);

                        // Convert body only if created in new format
                        $cutOffDate = '2025-10-01'; // change as per your requirement

                        if ($promailer->created_at >= $cutOffDate) {

                            // Decode new JSON body
                            $bodyData = json_decode($promailer->body, true);

                            if (is_array($bodyData)) { 
                                $promailer->body = $this->convertPromailerToOldFormat($bodyData);
                            }
                        } else {
                            // Old promailer - decode to keep consistent output
                            $promailer->body = json_decode($promailer->body, true);
                        }

                        $promailer->body = json_encode($promailer->body, true); 

                        return Response::json(['status_code'=>200,'data'=>$promailer]);
                    }
                }else{
                    return response()->json([
                        'status_code' => 407, 
                        'message' => 'password expired', 
                        'is_expired' => $user->is_expired
                    ]);
                } 
            }else {
                return response()->json([
                    'status_code' => 400, 
                    'message' => 'user not found',  
                ]);
            } 
        }
    }*/

	public function getPromailerData(Request $request)
	{
	    /* ================= VALIDATION ================= */

	    $validator = Validator::make(
	        $request->all(),
	        ['promailer_id' => 'required|exists:promailers,id'],
	        ['promailer_id.exists' => 'promailer not match.']
	    );

	    if ($validator->fails()) {
	        return response()->json([
	            'status_code' => 203,
	            'message' => $validator->errors()->first()
	        ]);
	    }

	    /* ================= AUTH ================= */

	    $user = auth('customer-api')->user();
	    if (!$user) {
	        return response()->json([
	            'status_code' => 400,
	            'message' => 'user not found'
	        ]);
	    }

	    if ($user->is_expired == 1) {
	        return response()->json([
	            'status_code' => 407,
	            'message' => 'password expired',
	            'is_expired' => 1
	        ]);
	    }	


        if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
            return response()->json([
                'status'  => 423,
                'status_code' => 423,
                'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
            ]);
        }

	    $customer_id = $user->id;

	    /* ================= PROMAILER ================= */

	    $promailer = Promailer::find($request->promailer_id);

	    if (!$promailer) {
	        return response()->json([
	            'status_code' => 404,
	            'data' => 'Data not found'
	        ]);
	    }

	    /* ================= MARK AS READ ================= */

	    CustomerShowPromailer::firstOrCreate([
	        'promailers_id' => $request->promailer_id,
	        'customers_id'  => $customer_id
	    ]);

	    /* ================= IMAGE URL FIX ================= */

	    if (!empty($promailer->frontimage) &&
	        !filter_var($promailer->frontimage, FILTER_VALIDATE_URL)) {

	        $promailer->frontimage = asset(
	            'storage/' . ltrim($promailer->frontimage, '/')
	        );
	    }

	    /* ================= BODY FORMAT HANDLING ================= */

	    $cutOffDate = Carbon::create(2025, 10, 1);

	    // Normalize body to array safely
	    if (is_string($promailer->body)) {
	        $bodyArray = json_decode($promailer->body, true);
	    } elseif (is_array($promailer->body)) {
	        $bodyArray = $promailer->body;
	    } else {
	        $bodyArray = [];
	    }

	    if ($promailer->created_at && $promailer->created_at->gte($cutOffDate)) {
	        // New format → convert to old format
	        $promailer->body = is_array($bodyArray)
	            ? $this->convertPromailerToOldFormat($bodyArray)
	            : [];
	    } else {
	        // Old format → keep normalized
	        $promailer->body = is_array($bodyArray) ? $bodyArray : [];
	    }

	    // Always return body as JSON string
	    $promailer->body = json_encode(
	        $promailer->body,
	        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	    );

	    /* ================= RESPONSE ================= */

	    return response()->json([
	        'status_code' => 200,
	        'message' => 'success',
	        'data' => $promailer
	    ]);
	} 


    /*public function customerViewPromailer(Request $request)
    { 
        $rules = [  
            'promailer_id' => 'required|exists:promailers,id', 
        ]; 
        $messages = [ 
            'promailer_id.exists' => 'promailer not match.', 
        ]; 
        $validator = Validator::make( $request->all(), $rules,$messages);

        if ( $validator->fails() ) 
        {     
            return response()->json(['message' => $validator->errors()->first(),  'status_code' => 203 ]);  
        }else{
            $user = auth('customer-api')->user();  
            if (count((array)$user) > 0) {
                if($user->is_expired == 0){  
                    $promailer = CustomerShowPromailer::where('promailers_id', $request->promailer_id )->where('customers_id', $user->id )->first(); 
                    if(empty($promailer)){ 
                       $data = new CustomerShowPromailer();
                       $data->promailers_id = $request->promailer_id; 
                       $data->customers_id = $user->id;
                       $data->save();  
                       return Response::json(['status_code'=>200,'data'=>$data]);
                    }else{ 
                        return Response::json(['status_code'=>200,'data'=>$promailer]);
                    }
                }else{
                    return response()->json([
                        'status_code' => 407, 
                        'message' => 'password expired', 
                        'is_expired' => $user->is_expired
                    ]);
                } 
            }else {
                return response()->json([
                    'status_code' => 400, 
                    'message' => 'user not found',  
                ]);
            } 
        }
    }*/

    public function customerViewPromailer(Request $request)
	{
	    // Validate input
	    $validator = Validator::make(
	        $request->all(),
	        ['promailer_id' => 'required|exists:promailers,id'],
	        ['promailer_id.exists' => 'promailer not match.']
	    );

	    if ($validator->fails()) {
	        return response()->json([
	            'status_code' => 203,
	            'message' => $validator->errors()->first(),
	        ]);
	    }

	    // 🔐 Auth check
	    $user = auth('customer-api')->user();
	    if (!$user) {
	        return response()->json([
	            'status_code' => 400,
	            'message' => 'user not found',
	        ]);
	    }

	    if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
            return response()->json([
                'status'  => 423,
                'status_code' => 423,
                'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
            ]);
        }

	    // ⛔ Password expired
	    if ($user->is_expired == 1) {
	        return response()->json([
	            'status_code' => 407,
	            'message' => 'password expired',
	            'is_expired' => 1
	        ]);
	    }

	    /* ================= MARK PROMAILER AS VIEWED ================= */

	    $promailer = CustomerShowPromailer::firstOrCreate(
	        [
	            'promailers_id' => $request->promailer_id,
	            'customers_id'  => $user->id,
	        ]
	    );

	    return response()->json([
	        'status_code' => 200,
	        'data' => $promailer
	    ]);
	}

	  /**
     * Convert new Promailer JSON format to old API format.
     */
    private function convertPromailerToOldFormat(array $newData): array
    {
        $oldData = [];

        foreach ($newData as $item) {
            $fileName = '';

            if ($item['type'] === 'image') {
                $filePath = $item['data']['value'] ?? $item['value'] ?? '';
                $filePathValue = env('APP_URL', 'https://omsi-revamp.lyxelandflamingotech.in').'/storage/promailers/images/'.basename($filePath);

                $fileName = basename($filePath);
                $oldData[] = [
                    'type' => 'image',
                    'value' => $filePathValue,
                    'file_name' => $fileName,
                ];
                continue;
            }

            if ($item['type'] === 'url') {
                $url = $item['data']['value'] ?? $item['value'] ?? '';
                $oldData[] = [
                    'type' => 'url',
                    'value' => $url,
                    'file_name' => '',
                ];
                continue;
            }

            if ($item['type'] === 'paragraph') {
                $text = $item['data']['value'] ?? $item['value'] ?? '';
                $oldData[] = [
                    'type' => 'paragraph',
                    'value' => $text,
                    'file_name' => '',
                ];
            }
        }

        return $oldData;
    }
}
