<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::all();

            return ResponseFormatter::success($products);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:150',
                'price' => 'required|integer|min:1000',
                'discount' => 'required|integer|min:0',
            ];

            $validRequest = \Validator::make($request->all(), $rules);

            if ($validRequest->fails()) {
                return ResponseFormatter::error(ResponseAlias::HTTP_BAD_REQUEST, $validRequest->errors());
            }

            $newProduct = Product::create($request->all());

            return ResponseFormatter::success($newProduct);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        try {
            return ResponseFormatter::success($product);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $rules = [
                'name' => 'sometimes|string|max:150',
                'price' => 'sometimes|integer|min:1000',
                'discount' => 'sometimes|integer|min:0',
            ];

            $validRequest = \Validator::make($request->all(), $rules);

            if ($validRequest->fails()) {
                return ResponseFormatter::error(ResponseAlias::HTTP_BAD_REQUEST, $validRequest->errors());
            }

            $product->update($request->only(array_keys($rules)));

            return ResponseFormatter::success($product);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return ResponseFormatter::success(null, 'delete success');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }
}
