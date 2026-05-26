<?php

namespace Database\Support;

use Illuminate\Console\Command;

class MLHUBSetIdSequenceCommand extends Command
{
    protected $signature = 'MLHUB:set-id-sequence';

    protected $description = 'Set MySQL AUTO_INCREMENT on all eligible tables to >= MLHUB starting ID (147123468).';

    public function handle(): int
    {
        IdSequence::apply();
        $this->info('MLHUB ID sequence applied (starting ID: '.IdSequence::startingId().').');

        return self::SUCCESS;
    }
}
