<?php
namespace App\Http\Controllers\API\V3; 
use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Video;
use App\Models\CustomerVideo;
use App\Rules\YoutubeURL;
use Artisan;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Tymon\JWTAuth\Middleware\GetUserFromToken;
use Validator;

class VideosApiController extends Controller
{
    
    public function getAllVideoList(Request $request)
    {   
        // 🔐 Auth Check
        $user = auth('customer-api')->user();

        if (!$user) {
            return response()->json([
                'status_code' => 401,
                'message' => 'User not authenticated',
            ], 401);
        }

        // ⛔ Password Expired Check
        if (!empty($user->is_expired) && $user->is_expired == 1) {
            return response()->json([
                'status_code' => 407,
                'message' => 'Password expired',
                'is_expired' => 1
            ], 407);
        }

        if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
            return response()->json([
                'status'  => 423,
                'status_code' => 423,
                'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
            ]);
        }
       
        // ================= VALIDATION ================= //
        
        $rules = [ 
            'is_trending' => 'nullable|in:0,1',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

         
        $videoQuery = Video::latest()->where('enabled', 1)->orderByRaw('order_by IS NULL')   // NULL last
                        ->orderBy('order_by', 'asc');

        if ($request->filled('is_trending')) {
            $videoQuery->where('is_trending', $request->is_trending);
        } 
 

        $all_video_count = $videoQuery->count();
        $video_data = $videoQuery->paginate(8);

        return response()->json([
            'status_code' => 200,
            'message' => 'Video List',
            'all_video_count' => $all_video_count,
            'data' => $video_data,
        ], 200);            
    }

    public function getVideoData(Video $video)
    {
        $user = auth('customer-api')->user();
        if (count((array)$user) > 0) {
            if (!empty($user->is_account_block) && $user->is_account_block == 1) { 
                return response()->json([
                    'status'  => 423,
                    'status_code' => 423,
                    'message' => 'Your account is blocked due to pending KYC. Please contact your supervisor.'
                ]);
            }

            if($user->is_expired == 0){
                return $video;
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

    // public function customerWatchedVideo(Request $request, Video $video, Customers $customer)
    // {
    //     $video->customers()->syncWithoutDetaching([$customer->id]);
    //     return response()->json(['status_code'=>'success']);
    // } 

    public function customerWatchedVideo(Request $request)
    {
        // Validate input
        // $validator = Validator::make(
        //     $request->all(),
        //     ['video_id' => 'required|exists:videos,id'],
        //     ['video_id.exists' => 'promailer not match.']
        // );
        $validator = Validator::make(
            $request->all(),
            [
                'video_id' => 'required|exists:videos,id',
            ],
            [
                'video_id.required' => 'Video id is required.',
                'video_id.exists' => 'Video is not available.',
            ]
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

        /* ================= MARK VIDEO AS VIEWED ================= */

        $video = CustomerVideo::firstOrCreate(
            [
                'video_id' => $request->video_id,
                'customer_id'  => $user->id,
            ]
        );

        return response()->json([
            'status_code' => 200,
            'status' => 200,
            'message' => "Success",
            'data' => $video
        ]);
    }
     
}
