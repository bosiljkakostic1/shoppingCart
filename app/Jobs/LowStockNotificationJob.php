<?php

namespace App\Jobs;

use App\Mail\LowStockNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class LowStockNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Prevent duplicate notifications within 1 hour
        $cacheKey = 'low_stock_notification_' . $this->product->id;
        if (Cache::has($cacheKey)) {
            return; // Already notified recently
        }

        // Get admin user (first user or create dummy admin)
        $admin = User::where('email', 'admin@example.com')->first();
        
        if (!$admin) {
            // Create dummy admin user if doesn't exist
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'), // Dummy password
            ]);
        }

        // Refresh product to get latest stock
        $this->product->refresh();
        $availableQuantity = $this->product->getAvailableQuantity();

        // Double-check stock is still low before sending
        if ($availableQuantity <= $this->product->minStockQuantity) {
            // Send low stock notification email
            Mail::to($admin->email)->send(new LowStockNotification($this->product));
            
            // Cache notification for 1 hour to prevent duplicates
            Cache::put($cacheKey, true, now()->addHour());
        }
    }
}
