<?php

namespace Modules\CustomAdminCache\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Session\DatabaseSessionHandler;
use Modules\AdminCache\Actions\ClearSessionsAction;
use RuntimeException;

class SafeClearSessionsAction extends ClearSessionsAction
{
    /**
     * The core action deletes every row in `sessions`, including the row for the
     * current Livewire request. Laravel's DatabaseSessionHandler still believes
     * the session exists and issues an UPDATE that affects zero rows, which can
     * break the Livewire /update response with HTTP 500.
     *
     * After wiping the table we reset the handler and rotate the session id so
     * the current request can finish cleanly.
     */
    protected function clearDatabaseSessions(): void
    {
        $table = (string) config('session.table', 'sessions');

        if (! Schema::hasTable($table)) {
            throw new RuntimeException(__('The session table [:table] does not exist.', ['table' => $table]));
        }

        DB::table($table)->delete();

        $this->resetDatabaseSessionHandler();
    }

    protected function resetDatabaseSessionHandler(): void
    {
        $handler = session()->getHandler();

        if ($handler instanceof DatabaseSessionHandler) {
            $handler->setExists(false);
        }

        session()->regenerate(true);
    }
}
