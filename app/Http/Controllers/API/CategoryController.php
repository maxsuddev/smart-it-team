<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::get();

        if (count($categories) > 0) {
            return CategoryResource::collection($categories);
        } else {
            return response()->json(['message' => 'No data found'], 404);
        }
    }

    public function store(Request $request)
    {
        if (Gate::denies('create', auth()->user())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Category validation error',
                    'errors' => $validator->messages(),
                ], 422);
            }
            $time = microtime(true);
            $path = $time;
            if (request()->hasFile('image')) {
                $name = md5($request->file('image')->getClientOriginalName());
                $path = $request->file('image')->storeAs('category-photos', $name . '.' . $request->file('image')->getClientOriginalExtension());
            }

            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' =>  'storage/'. $path ?? ''
            ]);

            return response()->json([
                'message' => 'Category created successfully',
                'data' => $category,
            ], 200);
        } catch (Exception $e) {
            Log::error('Category creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Category creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Category $category)
    {
        try {
            return new CategoryResource($category);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Category show failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Category $category )
    {
        if (Gate::denies('update', auth()->user())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Category validation error',
                    'errors' => $validator->messages(),
                ], 422);
            }

            if (request()->hasFile('image')) {
                if (isset($category->image)) {
                    Storage::delete($category->image);
                }
                $time = microtime(true);
                $name = md5($request->file('image')->getClientOriginalName());
                $path = $request->file('image')->storeAs('storage/category-photos', $time . $name . '.' . $request->file('image')->getClientOriginalExtension());
            }

            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'image' =>  $path ?? $category->image,
            ]);

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $category,
            ], 200);
        } catch (Exception $e) {
            Log::error('Category update failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Category update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Category $category)
    {
        if (Gate::denies('delete', auth()->user())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (isset($category->image)) {
            Storage::delete($category->image);
        }
        $category->delete();
        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
