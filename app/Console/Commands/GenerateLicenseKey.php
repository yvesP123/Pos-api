<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use App\Models\License;
use Carbon\Carbon;

class GenerateLicenseKey extends Command
{
    protected $signature = 'license:generate {company_id} {renewal_date} {duration_days}';
    protected $description = 'Generate encrypted license key';

    public function handle()
    {
        try {
            // Parse and validate dates
            $renewalDate = Carbon::createFromFormat('Y-m-d', $this->argument('renewal_date'));
            $durationDays = (int) $this->argument('duration_days');
            $expiryDate = $renewalDate->copy()->addDays($durationDays);
            // Build key string
            $keyString = sprintf("*%s* - *%s* - *%s*",
                $this->argument('company_id'),
                $renewalDate->format('dmy'),
                $expiryDate->format('dmy')
            );

            // Store in database (delete existing first)
            License::where('company_id', $this->argument('company_id'))->delete();
            
            $license = License::create([
                'company_id' => $this->argument('company_id'),
                'encrypted_key' => Crypt::encrypt($keyString),
                'renewal_date' => $renewalDate,
                'expiry_date' => $expiryDate
            ]);

            $this->info("✅ License generated successfully!");
            $this->info("Company ID: " . $this->argument('company_id'));
            $this->info("Renewal Date: " . $renewalDate->format('Y-m-d'));
            $this->info("Expiry Date: " . $expiryDate->format('Y-m-d'));
            $this->info("Days until expiry: " . Carbon::now()->diffInDays($expiryDate, false));
            
            // Show if license is already expired
            if (Carbon::now()->gt($expiryDate)) {
                $this->error("⚠️  WARNING: This license is already EXPIRED!");
            }

        } catch (\Exception $e) {
            $this->error("Error generating license: " . $e->getMessage());
        }
    }
}
