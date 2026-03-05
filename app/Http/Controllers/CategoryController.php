<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService)
    {}
    public function index()
    {
         return $this->successResponse(
            $this->categoryService->getAllCategories() ,
            'Categories retrieved successfully'
         );
    }
    public function store(StoreCategoryRequest $request)
    {
        Gate::authorize('create', Category::class);
        return $this->createResponse(
            $this->categoryService->createCategory($request->validated()),
            'Category created successfully'
        );
    }
}
