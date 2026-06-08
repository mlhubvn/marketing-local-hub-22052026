<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Modules\AdminMarketplace\Models\MarketplacePackage;

class MLHUBMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        if (! class_exists(MarketplacePackage::class)) {
            return;
        }

        $now = Carbon::now();

        foreach ((array) require database_path('seeders/data/mlhub_marketplace_packages.php') as $row) {
            $package = MarketplacePackage::query()->firstOrNew([
                'package_key' => $row['package_key'],
            ]);

            if (! $package->exists && ($id = IdSequence::idForNewSeed((int) $row['offset'])) !== null) {
                $package->id = $id;
            }

            $package->fill([
                'id_secure' => $row['id_secure'],
                'module_name' => $row['module_name'],
                'title' => $row['title'],
                'description' => $row['description'] ?? '',
                'version' => $row['version'],
                'source_type' => $row['source_type'],
                'product_id' => $row['product_id'],
                'purchase_code' => $row['purchase_code'],
                'product_slug' => $row['product_slug'],
                'license_type' => $row['license_type'],
                'licensed_domain' => $row['licensed_domain'],
                'install_path' => $row['install_path'],
                'providers' => $row['providers'] ?? [],
                'meta' => $row['meta'] ?? [],
                'is_active' => true,
                'installed_at' => $package->installed_at ?? $now,
                'last_synced_at' => $now,
            ])->save();
        }
    }
}
