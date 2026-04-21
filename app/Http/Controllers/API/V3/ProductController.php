<?php

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use App\Models\Category;
use App\Models\Speciality;
use App\Models\Product;
use App\Models\ProductInformation;
use App\Models\ProductVideo;
use App\Models\RelatedProduct;
use App\Models\SpecialityCategory;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{  
    public function productList(Request $request)
    {
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

        // ================= VALIDATION ================= //

        $rules = [
            'category_id' => 'nullable|array',
            'category_id.*' => 'integer|exists:categories,id',

            'sub_category_id' => 'nullable|array',
            'sub_category_id.*' => 'integer|exists:categories,id',

            'speciality_id' => 'nullable|array',
            'speciality_id.*' => 'integer|exists:specialities,id',

            'sub_speciality_id' => 'nullable|array',
            'sub_speciality_id.*' => 'integer|exists:specialities,id',

            'is_new' => 'nullable|boolean',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // ================= FETCH CATEGORIES ================= //

            $all_category_list = Category::whereNull('parent_id')
                ->where('status', 1)
                ->orderByRaw('orderby IS NULL')
                ->orderBy('orderby', 'asc')
                ->select([
                    'id',
                    'categories_name',
                    'categories_image',
                    'categories_image_url'
                ])
                ->with(['subCategoriesData' => function ($query) {
                    $query->where('status', 1)
                        ->orderByRaw('orderby IS NULL')
                        ->orderBy('orderby', 'asc')
                        ->select([
                            'id',
                            'parent_id',
                            'categories_name',
                            'categories_image',
                            'categories_image_url'
                        ]);
                }])
            ->get();

        // ================= FETCH SPECIALITIES ================= //

            $all_speciality_list = Speciality::whereNull('parent_id')
                ->where('status', 1)
                ->orderByRaw('orderby IS NULL')
                ->orderBy('orderby', 'asc')
                ->select([
                    'id',
                    'specialities_name',
                    'specialities_image',
                    'specialities_image_url'
                ])
                ->with(['subSpecialityData' => function ($query) {
                    $query->where('status', 1)
                        ->orderByRaw('orderby IS NULL')
                        ->orderBy('orderby', 'asc')
                        ->select([
                            'id',
                            'parent_id',
                            'specialities_name',
                            'specialities_image',
                            'specialities_image_url'
                        ]);
                }])
            ->get();

        // ================= STEP 1: PRODUCT DATA ================= //

            $productQuery = Product::where('status', 1);

        // ================= STEP 2: CATEGORY FILTER ================= //
 
            if ($request->filled('category_id')) {
                $productQuery->whereHas('productCategories', function ($q) use ($request) {
                    $q->whereIn('category_id', $request->category_id)->whereNull('deleted_at')->where('status', 1);
                });
            }

        // ================= STEP 3: SUB CATEGORY FILTER ================= //
 
            if ($request->filled('sub_category_id')) {
                $productQuery->whereHas('productCategories', function ($q) use ($request) {
                    $q->whereIn('subcategory_id', $request->sub_category_id)->whereNull('deleted_at')->where('status', 1);
                });
            }

        // ================= STEP 4: SPECIALITY FILTER ================= //

            if ($request->filled('speciality_id')) {
                $productQuery->whereHas('productSpecialities', function ($q) use ($request) {
                    $q->whereIn('speciality_id', $request->speciality_id)->whereNull('deleted_at')->where('status', 1);
                });
            }

        // ================= STEP 5: SUB SPECIALITY FILTER ================= //

            if ($request->filled('sub_speciality_id')) {
                $productQuery->whereHas('productSpecialities', function ($q) use ($request) {
                    $q->whereIn('sub_speciality_id', $request->sub_speciality_id)->whereNull('deleted_at')->where('status', 1);
                });
            }


        // ================= STEP 6: IS NEW PRODUCT FILTER ================= //

            if ($request->filled('is_new')) {
                $productQuery->where('is_new', $request->is_new);
            }

        // ================= FINAL RESULT ================= //

        $productQuery
            ->orderByRaw('orderby IS NULL')
            ->orderBy('orderby', 'asc')
            ->select([
                'id',
                'product_name',
                'product_sku',
                'product_image',
                'product_image_url',
                'is_trending',
                'is_new',
                'status'
            ]);

        $all_product_count = $productQuery->count();
        $all_product_list = $productQuery->paginate(6);

        return response()->json([
            'status_code' => 200,
            'message' => 'Success',
            'data' => [
                'categories' => $all_category_list,
                'specialities' => $all_speciality_list,
                'products_count' => $all_product_count,
                'products' => $all_product_list,
            ]
        ], 200);
    }

    public function productDetails(Request $request)
    {
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

        // ================= VALIDATION ================= //

            $rules = [
                'product_id' => 'required|integer|exists:products,id'
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'message' => $validator->errors()->first()
                ], 422);
            }

        // ================= FETCH PRODUCT ================= //

       $product_data = Product::where('status', 1)
            ->where('id', $request->product_id)
            ->with([
                'productVideos' => fn($q) => $q->where('status', 1)->orderBy('orderby', 'asc'),
                'productInformations' => fn($q) => $q->where('status', 1)->orderBy('order', 'asc'),
                'productSpecialities' => function ($q) {
                    $q->whereNull('deleted_at')->where('status', 1)->with([
                        'speciality:id,specialities_name',
                        'subSpeciality:id,specialities_name'
                    ]);
                },
                 
                // OLD testimonial
                'productTestimonial' => fn($q) => $q->where('status', 1)->orderBy('order_by', 'asc'),

                // NEW: Text testimonial
                'productTextTestimonial' => fn($q) => $q->where('status', 1)->orderBy('order_by', 'asc'),

                // NEW: Video testimonial
                'productVideoTestimonial' => fn($q) => $q->where('status', 1)->orderBy('order_by', 'asc'),
                'productCompatible' => function ($q) {
                    $q->where('status', 1)->orderBy('orderby', 'asc')->select('id', 'product_id', 'compatible_product_id')->with([
                        'compatibleProduct' => function ($subQ) {
                            $subQ->where('status', 1)
                               ->select([
                                   'id',
                                   'product_name',
                                   'product_sku',
                                   'product_image',
                                   'product_image_url',
                                   'is_new',
                                   'is_trending',
                                   'status'
                               ]);
                        }
                    ]);
                },
            ])
            ->first();


        // ✅ NULL CHECK IMMEDIATELY
        if (!$product_data) {
            return response()->json([
                'status_code' => 404,
                'message' => 'Product not found or inactive',
            ], 404);
        }

        $allNames = collect();

        foreach ($product_data->productSpecialities ?? [] as $item) {
            $allNames->push($item->speciality?->specialities_name);
            $allNames->push($item->subSpeciality?->specialities_name);
        }

        $product_data->speciality_data = collect($product_data->productSpecialities)
            ->flatMap(fn($item) => [
                $item->speciality?->specialities_name,
                $item->subSpeciality?->specialities_name
            ])
            ->filter()
            ->unique()
            ->values()
            ->toArray(); 

        // Hide unwanted columns
            
            $product_data->makeHidden([
                'orderby',
                'created_at',
                'updated_at',
                'deleted_at'
            ]);

        return response()->json([
            'status_code' => 200,
            'message' => 'Success',
            'data' => $product_data,
        ], 200);
    }
}
