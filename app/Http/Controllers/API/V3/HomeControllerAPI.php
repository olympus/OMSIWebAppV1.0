<?php

namespace App\Http\Controllers\API\V3;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\Helper;
use App\Mail\SendForgotOtp;
use App\Mail\SendOtp;
use App\Mail\SendResendOtp;
use Auth;
use Carbon\Carbon;
use JWTAuth;
use Log;
use Mail;
use Response;
use Tymon\JWTAuth\Middleware\GetUserFromToken;
use Validator;
use App\Models\Departments;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\User;
use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\Promailer;
use App\Models\CustomerShowPromailer;
use App\CustomerTemp;
use App\HospitalTemp;
use App\NotifyCustomer;
use App\PasswordHistory;
use App\UserLoginAttemptCount;
use App\Models\Speciality;
use App\Models\Category;
use App\Models\SpecialityCategory;
use App\Models\Product;
use App\Models\Video;
use App\Models\Testimonial;
use App\Models\AutoEmails; 
use App\Models\EmployeeTeam;
use App\Models\Feedback; 
class HomeControllerAPI extends Controller
{
    public function homePageAPI(){
        try {

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
            // Page Heading List

                $page_title = array(
                    'recent_tickets_section' => "Recent Tickets",
                    'category_section' => "Product Category",
                    'product_section' => "New In",
                    'speciality_section' => "By Medical Specialty",
                    'e_journal_section' => "E-Journal",
                    'trending_videos_section' => "Trending Videos",
                    'testimonial_section' => "Testimonial",
                );


            // 📂 Fetch Open Tickets

                $open_ticket_data = ServiceRequests::where('customer_id', $user->id)->where('status', '!=', 'Closed')->select('id', 'cvm_id', 'request_type', 'sub_type', 'customer_id', 'remarks', 'status', 'is_escalated', 'escalation_count','escalation_remarks','created_at')->latest()->first();

       
            // 📂 Fetch Categories
                $category_list = Category::whereNull('parent_id')
                    ->where('status', 1)
                    ->orderByRaw('orderby IS NULL')   // NULL last
                    ->orderBy('orderby', 'asc')       // Then proper sorting
                    ->select([
                        'id as category_id',
                        'categories_name',
                        'categories_image',
                        'categories_image_url'
                    ])
                    ->take(6)->get();

            // 📂 Fetch Specialities
                $speciality_list = Speciality::whereNull('parent_id')
                    ->where('status', 1)
                    ->orderByRaw('orderby IS NULL')   // NULL last
                    ->orderBy('orderby', 'asc')       // Then proper sorting
                    ->select([
                        'id as speciality_id',
                        'specialities_name',
                        'specialities_image',
                        'specialities_image_url'
                    ])
                    ->take(6)->get();

            // 📂 Fetch Popup Products
                $popup_product_list = Product::where('status', 1)->where('latest_product_show_in_popup', 1) 
                    ->orderByRaw('orderby IS NULL')
                    ->orderBy('orderby', 'asc')
                    ->select([
                        'id',
                        'product_name',
                        'product_sku',
                        'is_trending',
                        'is_new',
                        'status', 
                        'product_image',
                        'product_image_url'
                    ])
                    ->take(6)->get();
                 
                $is_popup_show = $popup_product_list->count() > 0 ? 1 : 0;

            // 📂 Fetch Products
                $product_list = Product::where('status', 1)->where('is_new', 1) 
                    ->orderByRaw('orderby IS NULL')
                    ->orderBy('orderby', 'asc')
                    ->select([
                        'id',
                        'product_name',
                        'product_sku',
                        'is_trending',
                        'is_new',
                        'status', 
                        'product_image',
                        'product_image_url'
                    ])
                    ->take(6)->get();

            // 📂 Fetch Trending Videos
                
                $trending_video = Video::where('enabled', 1)->where('is_trending', 1)->take(5)->select('id', 'title', 'is_trending', 'videos_thumbnail_image', 'video_type', 'url','created_at')->get(); 

            // 📂 Fetch Testimonial Data 
                $testimonial_text_data = Testimonial::where('status', 1)->where('is_trending',  1) 
                    ->where('type', 'text')
                    ->orderByRaw('order_by  IS NULL')   // NULL last
                    ->orderBy('order_by', 'asc')
                    ->take(6)->get();  

                $testimonial_video_data = Testimonial::where('status', 1)->where('is_trending',  1) 
                    ->where('type', 'video')
                    ->orderByRaw('order_by  IS NULL')   // NULL last
                    ->orderBy('order_by', 'asc')
                    ->take(6)->get();  

                 

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => [
                    'page_title' => $page_title,
                    'open_ticket_data' => $open_ticket_data,
                    'categories_data' => $category_list,
                    'specialities_data' => $speciality_list,
                    'is_popup_show' => $is_popup_show,
                    'popup_product_list' => $popup_product_list,
                    'products_data' => $product_list,
                    'trending_video_data' => $trending_video, 
                    'testimonial_text_data' => $testimonial_text_data,
                    'testimonial_video_data' => $testimonial_video_data,
                ]
            ], 200);

        } catch (\Exception $e) {

            Log::error('Category List API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function homePageOpenAPI(){
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

            // Page Heading List

                $page_title = array(
                    'recent_tickets_section' => "Recent Tickets",
                    'category_section' => "Product Category",
                    'product_section' => "New In",
                    'speciality_section' => "By Medical Specialty",
                    'e_journal_section' => "E-Journal",
                    'trending_videos_section' => "Trending Videos",
                    'testimonial_section' => "Testimonial",
                );


            // 📂 Fetch Open Tickets

                $open_ticket_data = [];

       
            // 📂 Fetch Categories
                $category_list = Category::whereNull('parent_id')
                    ->where('status', 1)
                    ->orderByRaw('orderby IS NULL')   // NULL last
                    ->orderBy('orderby', 'asc')       // Then proper sorting
                    ->select([
                        'id as category_id',
                        'categories_name',
                        'categories_image',
                        'categories_image_url'
                    ])
                    ->take(6)->get();

            // 📂 Fetch Specialities
                $speciality_list = Speciality::whereNull('parent_id')
                    ->where('status', 1)
                    ->orderByRaw('orderby IS NULL')   // NULL last
                    ->orderBy('orderby', 'asc')       // Then proper sorting
                    ->select([
                        'id as speciality_id',
                        'specialities_name',
                        'specialities_image',
                        'specialities_image_url'
                    ])
                    ->take(6)->get();

            // 📂 Fetch Popup Products
                $popup_product_list = Product::where('status', 1)->where('latest_product_show_in_popup', 1) 
                    ->orderByRaw('orderby IS NULL')
                    ->orderBy('orderby', 'asc')
                    ->select([
                        'id',
                        'product_name',
                        'product_sku',
                        'is_trending',
                        'is_new',
                        'status', 
                        'product_image',
                        'product_image_url'
                    ])
                    ->take(6)->get();
                 
                $is_popup_show = $popup_product_list->count() > 0 ? 1 : 0;

            // 📂 Fetch Products
                $product_list = Product::where('status', 1)->where('is_new', 1) 
                    ->orderByRaw('orderby IS NULL')
                    ->orderBy('orderby', 'asc')
                    ->select([
                        'id',
                        'product_name',
                        'product_sku',
                        'is_trending',
                        'is_new',
                        'status', 
                        'product_image',
                        'product_image_url'
                    ])
                    ->take(6)->get();

            // 📂 Fetch Trending Videos
                
                $trending_video = Video::where('enabled', 1)->where('is_trending', 1)->take(5)->select('id', 'title', 'is_trending', 'videos_thumbnail_image', 'video_type', 'url','created_at')->get(); 

            // 📂 Fetch Testimonial Data 
                $testimonial_text_data = Testimonial::where('status', 1)->where('is_trending',  1) 
                    ->where('type', 'text')
                    ->orderByRaw('order_by  IS NULL')   // NULL last
                    ->orderBy('order_by', 'asc')
                    ->take(6)->get();  

                $testimonial_video_data = Testimonial::where('status', 1)->where('is_trending',  1) 
                    ->where('type', 'video')
                    ->orderByRaw('order_by  IS NULL')   // NULL last
                    ->orderBy('order_by', 'asc')
                    ->take(6)->get();   

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => [
                    'page_title' => $page_title,
                    'open_ticket_data' => null,
                    'categories_data' => $category_list,
                    'specialities_data' => $speciality_list,
                    'is_popup_show' => $is_popup_show,
                    'popup_product_list' => $popup_product_list,
                    'products_data' => $product_list,
                    'trending_video_data' => $trending_video, 
                    'testimonial_text_data' => $testimonial_text_data,
                    'testimonial_video_data' => $testimonial_video_data,
                ]
            ], 200);

        } catch (\Exception $e) {

            Log::error('Category List API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function videoStore(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:51200' // 50MB
        ]);

        if ($request->hasFile('video')) {

            $video = $request->file('video');

            $filename = time().'_'.$video->getClientOriginalName();

            $path = $video->storeAs('videos', $filename, 'public');

            return response()->json([
                'message' => 'Video uploaded successfully',
                'path' => $path
            ]);
        }

        return response()->json([
            'message' => 'No video uploaded'
        ], 400);
    }
}
