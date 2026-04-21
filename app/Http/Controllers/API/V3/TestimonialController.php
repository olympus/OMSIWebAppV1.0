<?php

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Log;

class TestimonialController extends Controller
{
    
    public function testimonialList(Request $request)
    {
        try {

            // 🔐 Auth Check
            /*$user = auth('customer-api')->user();

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
            }*/

            // 📂 Fetch All Testimonial
            
            $testimonial_list_query = Testimonial::where('status', 1)
                ->orderByRaw('order_by  IS NULL')   // NULL last
                ->orderBy('order_by', 'asc');       // Then proper sorting
            
            $testimonial_list_count = $testimonial_list_query->count();

            $testimonial_list = $testimonial_list_query->paginate($testimonial_list_count);

            // 📂 Fetch Text Testimonial
            
            $testimonial_text_list_query = Testimonial::where('status', 1)
                ->where('type', 'text')
                ->orderByRaw('order_by  IS NULL')   // NULL last
                ->orderBy('order_by', 'asc');       // Then proper sorting
            
            $testimonial_text_list_count = $testimonial_text_list_query->count();

            $testimonial_text_list = $testimonial_text_list_query->paginate($testimonial_text_list_count);

            // 📂 Fetch Video Testimonial
            
            $testimonial_video_list_query = Testimonial::where('status', 1)
                ->where('type', 'video')
                ->orderByRaw('order_by  IS NULL')   // NULL last
                ->orderBy('order_by', 'asc');       // Then proper sorting
            
            $testimonial_video_list_count = $testimonial_video_list_query->count();

            $testimonial_video_list = $testimonial_video_list_query->paginate($testimonial_video_list_count);

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                
                'total_testimonial_count' => $testimonial_list_count, 
                'all_testimonial' => $testimonial_list,

                'total_text_testimonial_count' => $testimonial_text_list_count, 
                'all_text_testimonial' => $testimonial_text_list,

                'total_video_testimonial_count' => $testimonial_video_list_count,  
                'all_video_testimonial' => $testimonial_video_list
            ], 200);

        } catch (\Exception $e) {

            Log::error('Testimonial List API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }
}
