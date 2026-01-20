<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class DailySalesReportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $salesData
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
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

        // Send daily sales report email
        Mail::to($admin->email)->send(new DailySalesReport($this->salesData));
    }
}
