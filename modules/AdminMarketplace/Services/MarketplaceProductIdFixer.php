<?php

namespace Modules\AdminMarketplace\Services;

use Illuminate\Support\Facades\Schema;
use Modules\AdminMarketplace\Models\MarketplacePackage;

class MarketplaceProductIdFixer
{
    protected const PRODUCT_IDS = [
        'AppGoogleBusiness' => 52112026,
        'AppAdvancedCustomerCrm' => 99052026,
    ];

    public function sync(): void
    {
        try {
            if (! Schema::hasTable('marketplace_packages')) {
                return;
            }

            foreach (self::PRODUCT_IDS as $moduleName => $productId) {
                MarketplacePackage::query()
                    ->where(function ($query) use ($moduleName): void {
                        $query
                            ->where('module_name', $moduleName)
                            ->orWhere('package_key', strtolower($moduleName));
                    })
                    ->get()
                    ->each(function (MarketplacePackage $package) use ($productId): void {
                        $meta = (array) ($package->meta ?? []);

                        if ((int) $package->product_id === $productId && (int) ($meta['product_id'] ?? 0) === $productId) {
                            return;
                        }

                        $meta['product_id'] = $productId;

                        $package->forceFill([
                            'product_id' => $productId,
                            'meta' => $meta,
                        ])->save();
                    });
            }
        } catch (\Throwable) {
            return;
        }
    }
}
