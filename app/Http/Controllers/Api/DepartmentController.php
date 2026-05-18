<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $departments = Department::query()
            ->orderBy('name', 'asc')
            ->get()
            ->map(function (Department $department): array {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'description' => $department->description,
                ];
            });

        return response()->json([
            'message' => 'Departments retrieved successfully.',
            'data' => $departments,
        ]);
    }
}
