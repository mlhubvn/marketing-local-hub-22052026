<?php

namespace Modules\AdminUser\Exceptions;

use RuntimeException;

class SharedTeamOwnershipException extends RuntimeException
{
    /**
     * @param  list<int>  $teamIds
     */
    public function __construct(public readonly array $teamIds)
    {
        parent::__construct(__(
            'This user owns a shared workspace. Transfer ownership of team(s) :teams before deleting the account.',
            ['teams' => implode(', ', $teamIds)]
        ));
    }
}
