<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        $descriptions = [
            'starter-monthly' => 'For new local businesses starting campaign pages, QR codes, and review growth.',
            'growth-monthly' => 'For growing local businesses running recurring campaigns, bookings, and reports.',
            'agency-monthly' => 'For teams managing multiple brands, campaigns, and full marketing pipelines.',
            'starter-yearly' => 'Annual plan for local businesses that want stable costs and steady marketing growth.',
            'growth-yearly' => 'Yearly plan for teams scaling campaigns, automation, and AI-driven local workflows.',
            'agency-yearly' => 'Full-year plan for agencies running many workspaces and large-scale marketing ops.',
            'starter-lifetime' => 'One-time payment for small shops building a long-term MLHUB local growth base.',
            'growth-lifetime' => 'Lifetime access for active businesses needing AI, automation, and higher volume.',
            'agency-lifetime' => 'Lifetime plan for operators managing many clients, assets, and automations.',
        ];

        foreach ($descriptions as $slug => $desc) {
            DB::table('plans')->where('slug', $slug)->update(['desc' => $desc]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        $descriptions = [
            'starter-monthly' => 'For new businesses starting to automate Facebook and Instagram posts.',
            'growth-monthly' => 'For businesses that need recurring campaigns, post queues, and smart content workflows.',
            'agency-monthly' => 'For teams managing multiple brands, campaigns, and large marketing pipelines.',
            'starter-yearly' => 'Annual savings for businesses that want predictable, steady marketing costs.',
            'growth-yearly' => 'Yearly value for teams scaling automation, publishing schedules, and AI workflows.',
            'agency-yearly' => 'Full-year agency capacity for many workspaces, channels, and large-scale marketing ops.',
            'starter-lifetime' => 'One-time payment for small businesses that need a long-term MLHUB foundation.',
            'growth-lifetime' => 'Lifetime access for active businesses that need AI, automation, and high publishing throughput.',
            'agency-lifetime' => 'Premium lifetime package for operators managing many clients, assets, and automations.',
        ];

        foreach ($descriptions as $slug => $desc) {
            DB::table('plans')->where('slug', $slug)->update(['desc' => $desc]);
        }
    }
};
