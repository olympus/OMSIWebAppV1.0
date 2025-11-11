<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Departments;
use Validator;
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
