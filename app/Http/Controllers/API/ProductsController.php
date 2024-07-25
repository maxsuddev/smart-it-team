<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Products;
use Exception;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $products = Products::all();
        if (count($products) > 0) {
            return ProductResource::collection($products);
        } else {
            return response()->json(['message' => 'No data found.'], 404);
        }
    }

    public function store(Request $request)
    {
        if (Gate::denies('create', auth()->user())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|integer',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Product validation error',
                    'errors' => $validator->messages()
                ], 422);
            }

            $time = microtime(true);
            $path = $time;
            if(request()->hasFile('image')){
                $name = md5($request->file('image')->getClientOriginalName());
                $path = $request->file('image')->storeAs('products-photos', $name.'.'.$request->file('image')->getClientOriginalExtension());
            }

            $product = Products::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' =>  'storage/'. $path ?? ''
            ]);

            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product
            ], 200);
        } catch (Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Product creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Products $product)
    {
        try {
            return new ProductResource($product);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Product show failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Products $product)
    {
        if (Gate::denies('update', auth()->user())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|integer',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Product validation error',
                    'errors' => $validator->messages()
                ], 422);
            }


            if (request()->hasFile('image')) {
                if (isset($product->image)) {
                    Storage::delete($product->image);
                }
                $time = microtime(true);
                $name = md5($request->file('image')->getClientOriginalName());
                $path = $request->file('image')->storeAs('storage/products-photos', $time.$name.'.'.$request->file('image')->getClientOriginalExtension());
            }

            $product->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' =>   $path ?? $product->image,
            ]);

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
        } catch (Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Product update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Products $product)
    {
        if (Gate::denies('delete', auth()->user())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (isset($product->image)) {
            Storage::delete($product->image);
        }
        $product->delete();
        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
