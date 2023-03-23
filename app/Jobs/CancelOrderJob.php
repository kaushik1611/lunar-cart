<?php

namespace App\Jobs;

use Throwable;
use Carbon\Carbon;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lunar\Models\CartLine;

class CancelOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $timeBeforeOneHour = date('Y-m-d H:i', strtotime("-1 hour"));
        $carts =  Cart::whereNull('order_id')->select(['id', 'created_at'])->get();

        foreach ($carts as $cart) {
            $cartDate = $cart->created_at->format('Y-m-d H:i');
            $cart_id = $cart->id;

            $cartProductCancle = CartLine::where('cart_id', $cart_id)->with(['cart'])->get();
            if ($cartDate == $timeBeforeOneHour) {
                foreach ($cartProductCancle as $cartProduct) {
                    $cartProduct->delete();
                }
                Cart::whereNull('order_id')->has('lines', '=', 0)->delete();
            }
        }
    }

    public function failed(Throwable $e)
    {
        Log::error('Cart delete error :' . $e->getMessage());
    }
}
