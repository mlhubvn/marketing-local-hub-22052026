<?php

namespace Modules\AdminUser\Exceptions;

use RuntimeException;

class LastSuperAdminDeletionException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('The final super administrator account cannot be deleted.'));
    }
}
