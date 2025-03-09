<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatusEnum;
use App\Helper\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private const EXPIRED_RANGE = 15;
    private readonly string $EXPRESS_ENDPOINT;
    private readonly string $EXPRESS_VERSION1;

    public function __construct() {
        $this->EXPRESS_ENDPOINT = env('EXPRESS_ENDPOINT', 'http://127.0.0.1:3000/api');
        $this->EXPRESS_VERSION1 = env('EXPRESS_VERSION1', 'v1');
    }

    private function generateTransactionId(): string {
        $datetime = now()->format('YmdHisv');
        return "TRX_" . $datetime;
    }

    private function createNotificationLog($newOrder): void {
        try {
            $endpoint = $this->EXPRESS_ENDPOINT . '/' . $this->EXPRESS_VERSION1 . '/notifications/notify-orders';

            Http::post($endpoint, $newOrder);
        } catch (\Throwable $th) {
            Log::channel('notification')->error("Failed to send notification: " . $th->getMessage());
        }
    }

    private function calculateDiscount($request) {
        try {
            $endpoint = $this->EXPRESS_ENDPOINT . '/' . $this->EXPRESS_VERSION1 . '/orders/calculate-discounts';

            $response = Http::post($endpoint, [
                'customer_id' => $request["customer_id"],
                'product_id'  => $request["product_id"],
                'quantity'    => $request["quantity"],
            ]);

            return $response["data"];
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    private function createOrderPayload($request) {
        $productDetail = Product::find($request['product_id']);
        $calculatedDiscount = $this->calculateDiscount($request);

        return [
            'trx_id' => $this->generateTransactionId(),
            'customer_id' => $request["customer_id"],
            'product_id' => $request["product_id"],
            'quantity' => $request["quantity"],
            'order_date' => now(),
            'expired_date' => now()->addMinutes(self::EXPIRED_RANGE),
            'total_price' => $calculatedDiscount["final_price"],
            'product_name' => $productDetail->name,
            'product_price' => $productDetail->price,
            'product_discount' => $productDetail->discount,
        ];
    }

    private function validateOrders($request) {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'order_id' => [
                'required',
                'exists:orders,id',
                function ($attribute, $value, $fail) {
                    $order = Order::where('id', $value)
                            ->where('status', OrderStatusEnum::PENDING)
                            ->with('customer')
                            ->first();

                    if (!$order) {
                        return $fail('The selected order is either invalid or not in pending status.');
                    }

                    if (!$order->customer || $order->customer->id != request('customer_id')) {
                        return $fail('The selected order is does not belong to the specified customer.');
                    }
                },
            ],
        ];

        $validRequest = \Validator::make($request->all(), $rules);

        if ($validRequest->fails()) {
        }
        return [
            "is_valid_condition" => $validRequest->fails() ? false : true,
            "message" => $validRequest->fails() ? $validRequest->errors() : null
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $orders = Order::with('customer')->get();

            return ResponseFormatter::success($orders);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'customer_id' => 'required|exists:customers,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ];


            $validRequest = \Validator::make($request->all(), $rules);

            if ($validRequest->fails()) {
                return ResponseFormatter::error(ResponseAlias::HTTP_BAD_REQUEST, $validRequest->errors());
            }

            $orderPayload = $this->createOrderPayload($request->all());

            $newOrder = Order::create($orderPayload);

            $this->createNotificationLog($newOrder);

            return ResponseFormatter::success($newOrder);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        try {
            return ResponseFormatter::success($order);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    public function markAsPaid(Request $request) {
        try {
            $validatedOrder = $this->validateOrders($request);

            if (!$validatedOrder["is_valid_condition"]) {
                return ResponseFormatter::error(ResponseAlias::HTTP_BAD_REQUEST, $validatedOrder["message"]);
            }

            $completedOrder = Order::find($request["order_id"])->update([ "status" =>  OrderStatusEnum::COMPLETED->value]);

            return ResponseFormatter::success($completedOrder);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }

    public function markAsCanceled(Request $request) {
        try {
            $validatedOrder = $this->validateOrders($request);

            if (!$validatedOrder["is_valid_condition"]) {
                return ResponseFormatter::error(ResponseAlias::HTTP_BAD_REQUEST, $validatedOrder["message"]);
            }

            $canceleddOrder = Order::findOrFail($request["order_id"])->update([ "status" =>  OrderStatusEnum::CANCELED->value]);

            return ResponseFormatter::success($canceleddOrder);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
    }
}
