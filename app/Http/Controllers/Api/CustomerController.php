<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $customers = Customer::all();

            return ResponseFormatter::success($customers);
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
                'email' => 'required|email|unique:customers|max:100',
                'phone' => 'required|string|regex:/^628[0-9]{8,12}$/',
            ];

            $customMessages = [
                'phone.regex' => 'The phone number must start with 628 and contain 8-14 digits only.',
            ];


            $validRequest = \Validator::make($request->all(), $rules, $customMessages);

            if ($validRequest->fails()) {
                return ResponseFormatter::error(ResponseAlias::HTTP_BAD_REQUEST, $validRequest->errors());
            }

            $newCustomer = Customer::create($request->all());

            return ResponseFormatter::success($newCustomer);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        try {
            return ResponseFormatter::success($customer);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
