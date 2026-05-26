<?php

namespace Modules\CustomInstaller\Support;

use App\Installer\Support\InstallerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SafeInstallerService extends InstallerService
{
    /**
     * Replace the parent "throw if not empty" guard with a destructive
     * auto-wipe so the installer can re-run on a database that already
     * contains framework / leftover tables.
     *
     * Only safe to use when the operator explicitly wants a fresh install
     * (gated by CUSTOM_INSTALLER_AUTO_WIPE env var in the provider).
     */
    protected function ensureFreshDatabase(): void
    {
        $connection = DB::connection();

        try {
            $connection->statement('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($connection->select('SHOW TABLES') as $row) {
                $table = array_values((array) $row)[0] ?? null;

                if (! is_string($table) || $table === '') {
                    continue;
                }

                Schema::dropIfExists($table);
            }
        } catch (Throwable $e) {
            // Fall back to the parent guard so the installer still aborts
            // safely if the wipe failed (e.g. permission denied on DROP).
            try {
                $connection->statement('SET FOREIGN_KEY_CHECKS = 1');
            } catch (Throwable) {
                // ignore
            }

            parent::ensureFreshDatabase();

            throw $e;
        }

        try {
            $connection->statement('SET FOREIGN_KEY_CHECKS = 1');
        } catch (Throwable) {
            // ignore — some MySQL configs disallow this from app users.
        }
    }
}
