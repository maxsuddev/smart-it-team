<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Products;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::get();
        if(count($products) > 0){
            return ProductResource::collection($products);
        }else{
            return response()->json(['message' => 'No data found.'], 404);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
                'name' => 'required|string|:255',
                'description' => 'required|string',
                'price' => 'required|integer',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 'image'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Product validation error',
                    'errors' => $validator->messages()
                ], 422);
            }
            if (request()->hasFile('image')) {
                $name = $request->file('image')->getClientOriginalName();
                $path = $request->file('image')->storeAs('products-photos', $name);
            }

            $product = Products::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' => $path ?? ''
            ]);
            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product
            ], 200);
        }catch (Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Product creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Products $product)
    {
        try {
            return new ProductResource($product);
        }catch (Exception $e) {
            return response()->json([
                'message' => 'Product show failed',
                'error' => $e->getMessage()
            ],500);
        }
    }


    public function update(Request $request, Products $product)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
                'name' => 'required|string|:255',
                'description' => 'required|string',
                'price' => 'required|integer',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 'image'
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
                $name = $request->file('image')->getClientOriginalName();
                $path = $request->file('image')->storeAs('products-photos', $name);
            }

            $product->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' => $path ?? $product->image,
            ]);
            return response()->json([
                'message' => 'Product update successfully',
                'data' => $product
            ], 200);
        }catch (Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Product update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $product)
    {
        if(isset($product->image)) {
            Storage::delete($product->image);
        }
        $product->delete();
        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
