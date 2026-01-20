<?php

namespace App\Console\Commands;

use App\Jobs\DailySalesReportJob;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDailySalesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales report to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating daily sales report...');

        // Get today's date range
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        // Get all carts that were ordered today
        $orderedCarts = ShoppingCart::where('state', 'ordered')
            ->whereBetween('updatedAt', [$startOfDay, $endOfDay])
            ->get();

        $totalRevenue = 0;
        $totalItemsSold = 0;
        $productsSold = [];

        foreach ($orderedCarts as $cart) {
            $totalRevenue += (float) $cart->sum;

            // Get products from this cart
            $cartProducts = ShoppingCartProduct::where('shoppingCartId', $cart->id)->get();

            foreach ($cartProducts as $cartProduct) {
                $product = Product::find($cartProduct->productId);
                if ($product) {
                    $totalItemsSold += $cartProduct->quantity;

                    $productId = $product->id;
                    if (!isset($productsSold[$productId])) {
                        $productsSold[$productId] = [
                            'name' => $product->name,
                            'unit' => $product->unit,
                            'quantity' => 0,
                            'revenue' => 0,
                        ];
                    }

                    $productsSold[$productId]['quantity'] += $cartProduct->quantity;
                    $productsSold[$productId]['revenue'] += $cartProduct->quantity * (float) $product->price;
                }
            }
        }

        // Prepare sales data
        $salesData = [
            'date' => now()->format('Y-m-d'),
            'totalRevenue' => $totalRevenue,
            'totalItemsSold' => $totalItemsSold,
            'totalOrders' => $orderedCarts->count(),
            'products' => array_values($productsSold),
        ];

        // Dispatch job to send email
        DailySalesReportJob::dispatch($salesData);

        $this->info('Daily sales report job dispatched successfully!');
        $this->info('Total Revenue: $' . number_format($totalRevenue, 2));
        $this->info('Total Items Sold: ' . $totalItemsSold);
        $this->info('Total Orders: ' . $orderedCarts->count());

        return Command::SUCCESS;
    }
}
