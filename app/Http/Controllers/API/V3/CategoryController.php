<?php

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function categoryList(Request $request)
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

            // 📂 Fetch Categories
            $category_list_query = Category::whereNull('parent_id')
                ->where('status', 1)
                ->orderByRaw('orderby IS NULL')   // NULL last
                ->orderBy('orderby', 'asc')       // Then proper sorting
                ->select([
                    'id as category_id',
                    'categories_name',
                    'categories_image',
                    'categories_image_url'
                ]);

            $category_list_count = $category_list_query->count();

            $category_list = $category_list_query->paginate($category_list_count); 

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'total_category_count' => $category_list_count, 
                'data' => $category_list
            ], 200);

        } catch (\Exception $e) {

            Log::error('Category List API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong',
            ], 200);
        }
    }

    public function subCategoryList(Request $request)
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
                'category_id' => 'required|integer|exists:categories,id'
            ];

            $messages = [
                'category_id.required' => 'Category id is required.',
                'category_id.integer'  => 'Category id must be a valid number.',
                'category_id.exists'   => 'Category not found.',
            ];

            $validator = \Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $category_id = $request->category_id;

            /* ================= CHECK PARENT ACTIVE ================= */

            $parentCategory = Category::where('id', $category_id)
                ->where('status', 1)
                ->first();

            if (!$parentCategory) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Parent category not found or inactive.',
                ], 404);
            }

            /* ================= FETCH SUB CATEGORIES ================= */

            $sub_category_list_query = Category::where('parent_id', $category_id)
                ->where('status', 1)
                ->orderByRaw('orderby IS NULL')
                ->orderBy('orderby', 'asc')
                ->select([
                    'parent_id as category_id', 
                    'id as sub_category_id',
                    'categories_name',
                    'categories_image',
                    'categories_image_url'
                ]);

            $sub_category_list_count = $sub_category_list_query->count();

            $sub_category_list = $sub_category_list_query->paginate($sub_category_list_count); 
                 
            /* ================= DATA NOT FOUND ================= */

            if ($sub_category_list->isEmpty()) {
                return response()->json([
                    'status_code' => 200,
                    'message' => 'No subcategories found.',
                    'data' => null
                ], 200);
            }

            /* ================= SUCCESS ================= */

            return response()->json([
                'status_code' => 200,
                'message' => 'Success',
                'total_sub_category_count' => $sub_category_list_count, 
                'data' => $sub_category_list
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Sub Category API Error: ' . $e->getMessage());

            return response()->json([
                'status_code' => 500,
                'message' => 'Something went wrong.',
            ], 200);
        }
    }
}
