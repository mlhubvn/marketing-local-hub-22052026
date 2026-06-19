<?php

namespace Modules\CustomMLHUB\Support\DemoData;

use Carbon\CarbonImmutable;

class DemoTimeline
{
    public function at(int $index, int $total): CarbonImmutable
    {
        $now = CarbonImmutable::now();

        if ($index % 17 === 0) {
            return $now->subDays(($index % 7) + 1)->subHours($index % 11);
        }

        if ($index % 11 === 0) {
            return $now->subDays(($index % 23) + 8)->subHours($index % 19);
        }

        if ($index % 7 === 0) {
            return $now->subDays(($index % 59) + 31)->subHours($index % 13);
        }

        $spanDays = 724;
        $seasonalWave = (int) round(abs(sin(($index + 3) / 9)) * 37);
        $base = ($index * 13 + $seasonalWave + ($total % 29)) % $spanDays;

        return $now
            ->subDays(max(1, $base))
            ->subHours(($index * 5) % 23)
            ->subMinutes(($index * 7) % 53);
    }

    public function future(int $index, int $maxDays = 21): CarbonImmutable
    {
        return CarbonImmutable::now()
            ->addDays(($index % $maxDays) + 1)
            ->setTime(8 + ($index % 10), ($index * 7) % 60);
    }
}

