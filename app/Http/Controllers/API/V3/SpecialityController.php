<?php

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use App\Models\Speciality;
use App\Models\Category;
use App\Models\SpecialityCategory;
use Illuminate\Support\Facades\Log;

class SpecialityController extends Controller
{
    public function specialityList(Request $request)
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

            // 📂 Fetch Specialities
            $speciality_list_query = Speciality::whereNull('parent_id')
                ->where('status', 1)
                ->orderByRaw('orderby IS NULL')   // NULL last
                ->orderBy('orderby', 'asc')       // Then proper sorting
                ->select([
                    'id as speciality_id',
                    'specialities_name',
                    'specialities_image',
                    'specialities_image_url'
                ]); 

            $speciality_list_count = $speciality_list_query->count();

            $speciality_list = $speciality_list_query->paginate($speciality_list_count); 

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'total_speciality_count' => $speciality_list_count, 
                'data' => $speciality_list
            ], 200);

        } catch (\Exception $e) {

            Log::error('Speciality List API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 200);
        }
    }

    public function subSpecialityList(Request $request)
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

            /* ================= VALIDATION ================= */

            $rules = [
                'speciality_id' => 'required|integer|exists:specialities,id'
            ];

            $messages = [
                'speciality_id.required' => 'Speciality id is required.',
                'speciality_id.integer'  => 'Speciality id must be a valid number.',
                'speciality_id.exists'   => 'Speciality not found.',
            ];

            $validator = \Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $speciality_id = $request->speciality_id;

            /* ================= CHECK PARENT ACTIVE ================= */

            $parentSpeciality = Speciality::where('id', $speciality_id)
                ->where('status', 1)
                ->first();

            if (!$parentSpeciality) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Parent speciality not found or inactive.',
                ], 404);
            }

            /* ================= FETCH SUB Speciality ================= */

            $sub_specialities_list_query = Speciality::where('parent_id', $speciality_id)
                ->where('status', 1)
                ->orderByRaw('orderby IS NULL')
                ->orderBy('orderby', 'asc')
                ->select([
                    'parent_id as speciality_id', 
                    'id as sub_speciality_id',
                    'specialities_name',
                    'specialities_image',
                    'specialities_image_url'
                ]); 

            $sub_specialities_list_count = $sub_specialities_list_query->count();

            $sub_specialities_list = $sub_specialities_list_query->paginate($sub_specialities_list_count); 

            /* ================= DATA NOT FOUND ================= */

            if ($sub_specialities_list->isEmpty()) {
                return response()->json([
                    'status_code' => 200,
                    'message' => 'No subspecialities found.',
                    'data' => null
                ], 200);
            }

            /* ================= SUCCESS ================= */

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $sub_specialities_list
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Sub Speciality API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong.',
            ], 200);
        }
    } 
    
    public function categoryListBySpecialityAndSubSpeciality(Request $request)
    {
        try {

            /* ================= AUTH CHECK ================= */

            /*$user = auth('customer-api')->user();

            if (!$user) {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // ================= PASSWORD EXPIRED CHECK ================= //

            if (!empty($user->is_expired) && $user->is_expired == 1) {
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Password expired',
                    'is_expired' => 1
                ], 403);
            }*/

            /* ================= VALIDATION ================= */

            $rules = [
                'speciality_id'     => 'required|integer|exists:specialities,id',
                'sub_speciality_id' => 'required|integer|exists:specialities,id',
            ];

            // If category_id exists then validate it
            if ($request->filled('category_id')) {
                $rules['category_id'] = 'integer|exists:categories,id';
            }

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $speciality_id     = $request->speciality_id;
            $sub_speciality_id = $request->sub_speciality_id;
            $category_id       = $request->category_id;

            /* ================= CHECK SUB SPECIALITY BELONGS TO SPECIALITY ================= */

            $validSubSpeciality = Speciality::where('id', $sub_speciality_id)
                ->where('parent_id', $speciality_id)
                ->exists();

            if (!$validSubSpeciality) {
                return response()->json([
                    'status_code' => 422,
                    'message' => 'Sub Speciality does not belong to selected Speciality'
                ], 422);
            }

            /* ========================================================= */
            /* ===================== IF CATEGORY_ID EXISTS ============== */
            /* ========================================================= */

            if (!empty($category_id)) {

                $categoryIds = SpecialityCategory::where([
                        'category_id'      => $category_id,
                        'speciality_id'    => $speciality_id,
                        'sub_speciality_id'=> $sub_speciality_id
                    ])
                    ->where('status', 1)
                    ->pluck('sub_category_id')
                    ->toArray();

                if (empty($categoryIds)) {
                    return response()->json([
                        'status_code' => 200,
                        'message' => 'No sub categories found',
                        'data' => []
                    ], 200);
                }

                $category_list = Category::whereIn('id', $categoryIds)
                    ->whereNotNull('parent_id')
                    ->where('status', 1)
                    ->orderByRaw('orderby IS NULL')
                    ->orderBy('orderby', 'asc')
                    ->select([
                        'parent_id as category_id',
                        'id as sub_category_id',
                        'categories_name',
                        'categories_image',
                        'categories_image_url'
                    ])
                    ->paginate(4);

                if ($category_list->isEmpty()) {
                    return response()->json([
                        'status_code' => 200,
                        'message' => 'No active sub categories found',
                        'data' => []
                    ], 200);
                }

            } 
            /* ========================================================= */
            /* ===================== IF CATEGORY_ID NOT EXISTS ========= */
            /* ========================================================= */
            else {

                $categoryIds = SpecialityCategory::where([
                        'speciality_id'    => $speciality_id,
                        'sub_speciality_id'=> $sub_speciality_id
                    ])
                    ->where('status', 1)
                    ->pluck('category_id')
                    ->toArray();

                if (empty($categoryIds)) {
                    return response()->json([
                        'status_code' => 200,
                        'message' => 'No categories found',
                        'data' => []
                    ], 200);
                }

                $category_list = Category::whereIn('id', $categoryIds)
                    ->whereNull('parent_id')
                    ->where('status', 1)
                    ->orderByRaw('orderby IS NULL')
                    ->orderBy('orderby', 'asc')
                    ->select([
                        'id as category_id',
                        'categories_name',
                        'categories_image',
                        'categories_image_url'
                    ])
                    ->paginate(4);

                if ($category_list->isEmpty()) {
                    return response()->json([
                        'status_code' => 200,
                        'message' => 'No active categories found',
                        'data' => []
                    ], 200);
                }
            }

            /* ================= COMMON RESPONSE MODIFIER ================= */

            foreach ($category_list as $item) {
                $item->speciality_id = $speciality_id;
                $item->sub_speciality_id = $sub_speciality_id;
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'data' => $category_list
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Category List API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

}
