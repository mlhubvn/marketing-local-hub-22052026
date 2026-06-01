<?php

namespace Modules\AdminFaker\Support;

use Symfony\Component\Console\Output\OutputInterface;

final class MLHUBDemoSeedProgress
{
    private static ?OutputInterface $output = null;

    public static function bind(?OutputInterface $output): void
    {
        self::$output = $output;
    }

    public static function line(string $message): void
    {
        if (self::$output === null) {
            return;
        }

        self::$output->writeln('<info>'.date('H:i:s').'</info> '.$message);
    }

    public static function step(string $message): void
    {
        self::line($message);
    }
}
