<?php

namespace App\Http\Controllers\API\V2;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Departments;

/**
 * @resource Departments
 *
 * All Endpoints related to Departments
 */

class DepartmentsApiController extends Controller
{
    /**
     * Display a listing of the departments.
     *
     * @return Response
     */
    public function index()
    {
        $departments = Departments::orderBy('sort_order', 'ASC')->get();
        return $departments;
    }
}
