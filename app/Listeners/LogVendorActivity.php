<?php

namespace App\Listeners;

use App\Events\VendorApplied;
use App\Events\VendorApproved;
use App\Events\VendorRejected;
use Illuminate\Support\Facades\Log;

class LogVendorActivity
{
    public function handle(VendorApplied|VendorApproved|VendorRejected $event): void
    {
        Log::info('Vendor lifecycle event', [
            'event' => class_basename($event),
            'vendor_id' => $event->vendor->id,
            'user_id' => $event->vendor->user_id,
            'status' => $event->vendor->status->value,
        ]);
    }
}
