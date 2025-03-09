<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update expired orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::where('status', OrderStatusEnum::PENDING->value)
            ->where('expired_date', '<', Carbon::now())
            ->update(['status' => OrderStatusEnum::EXPIRED->value]);

        $this->info('Expired orders updated successfully.');
    }
}
