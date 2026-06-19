<?php

use Database\Seeders\PlanSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function createMLHUBDemoBoardTestSchema(): void
{
    foreach ([
        'blog_tag_maps',
        'blogs',
        'blog_tags',
        'blog_categories',
        'faqs',
        'lb_qr_scans',
        'lb_campaigns',
        'lb_locations',
        'lb_customers',
        'lb_businesses',
        'users',
        'plans',
    ] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('plans', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->boolean('status')->default(true);
        $table->boolean('featured')->default(false);
        $table->string('currency')->default('VND');
        $table->decimal('price', 16, 2)->default(0);
        $table->unsignedTinyInteger('type')->default(1);
        $table->boolean('free_plan')->default(false);
        $table->boolean('default_signup_plan')->default(false);
        $table->unsignedInteger('trial_day')->default(0);
        $table->integer('position')->default(0);
        $table->text('desc')->nullable();
        $table->json('permissions')->nullable();
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('locale', 12)->nullable();
        $table->string('timezone')->nullable();
        $table->boolean('is_super_admin')->default(false);
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->unsignedBigInteger('next_plan_id')->nullable();
        $table->timestamp('plan_started_at')->nullable();
        $table->timestamp('plan_expires_at')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('name');
        $table->string('type', 60)->default('Other');
        $table->string('industry_group_code')->nullable();
        $table->string('industry_category_code')->nullable();
        $table->string('industry_taxonomy_version')->nullable();
        $table->json('industry_metadata')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->text('address')->nullable();
        $table->text('google_maps_url')->nullable();
        $table->json('social_links')->nullable();
        $table->json('opening_hours')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_locations', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('business_id');
        $table->string('name');
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->text('address')->nullable();
        $table->text('google_maps_url')->nullable();
        $table->json('opening_hours')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    Schema::create('lb_campaigns', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('business_id');
        $table->string('slug')->unique();
        $table->string('name');
        $table->string('type', 40);
        $table->string('status', 30)->default('active');
        $table->text('destination_url')->nullable();
        $table->json('settings')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_qr_scans', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->string('device', 40)->nullable();
        $table->string('country', 80)->nullable();
        $table->string('city', 120)->nullable();
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('lb_customers', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('business_id')->nullable();
        $table->string('name');
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->json('tags')->nullable();
        $table->text('note')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamp('first_seen_at')->nullable();
        $table->timestamp('last_activity_at')->nullable();
        $table->timestamp('last_contacted_at')->nullable();
        $table->unsignedInteger('score')->default(0);
        $table->decimal('lifetime_value', 12, 2)->nullable();
        $table->timestamps();
    });

    Schema::create('blog_categories', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 64);
        $table->string('name');
        $table->json('name_translations')->nullable();
        $table->text('description')->nullable();
        $table->json('description_translations')->nullable();
        $table->string('slug')->unique();
        $table->string('icon')->nullable();
        $table->string('color', 32)->default('#0f766e');
        $table->boolean('status')->default(true);
        $table->integer('sort_order')->default(0);
        $table->unsignedBigInteger('changed')->default(0);
        $table->unsignedBigInteger('created')->default(0);
    });

    Schema::create('blog_tags', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 64);
        $table->string('name');
        $table->json('name_translations')->nullable();
        $table->text('description')->nullable();
        $table->json('description_translations')->nullable();
        $table->string('slug')->unique();
        $table->boolean('status')->default(true);
        $table->unsignedBigInteger('changed')->default(0);
        $table->unsignedBigInteger('created')->default(0);
    });

    Schema::create('blogs', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 64)->unique();
        $table->unsignedBigInteger('blog_category_id')->nullable();
        $table->string('title');
        $table->json('title_translations')->nullable();
        $table->text('excerpt')->nullable();
        $table->json('excerpt_translations')->nullable();
        $table->longText('content');
        $table->json('content_translations')->nullable();
        $table->string('slug')->unique();
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        $table->string('canonical_url', 2000)->nullable();
        $table->text('og_image')->nullable();
        $table->text('thumbnail')->nullable();
        $table->boolean('status')->default(true);
        $table->unsignedBigInteger('published_at')->nullable();
        $table->unsignedBigInteger('changed')->default(0);
        $table->unsignedBigInteger('created')->default(0);
    });

    Schema::create('blog_tag_maps', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('blog_id');
        $table->unsignedBigInteger('blog_tag_id');
        $table->unique(['blog_id', 'blog_tag_id']);
    });

    Schema::create('faqs', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 32)->nullable();
        $table->string('slug')->unique();
        $table->string('title');
        $table->json('title_translations')->nullable();
        $table->longText('content');
        $table->json('content_translations')->nullable();
        $table->boolean('status')->default(true);
        $table->unsignedBigInteger('changed')->default(0);
        $table->unsignedBigInteger('created')->default(0);
    });
}

test('demo board seeder creates bounded tenant-scoped demo accounts and public content', function (): void {
    $seederClass = 'Modules\\CustomMLHUB\\Database\\Seeders\\MLHUBDemoBoardSeeder';

    expect(class_exists($seederClass))->toBeTrue();

    createMLHUBDemoBoardTestSchema();
    (new PlanSeeder)->run();

    (new $seederClass)->run();

    $expectedPlans = [
        'mlhubfree' => 'mlhub-free-da-nang',
        'mlhubstarter' => 'mlhub-starter-monthly',
        'mlhubgrowth' => 'mlhub-growth-monthly',
        'mlhubpro' => 'mlhub-pro-monthly',
        'mlhubpartner' => 'mlhub-partner-monthly',
    ];

    foreach ($expectedPlans as $username => $planSlug) {
        $user = DB::table('users')->where('username', $username)->first();
        $plan = DB::table('plans')->where('slug', $planSlug)->first();
        $permissions = json_decode((string) $plan->permissions, true);

        expect($user)->not->toBeNull()
            ->and((int) $user->plan_id)->toBe((int) $plan->id)
            ->and(DB::table('lb_businesses')->where('user_id', $user->id)->count())->toBeLessThanOrEqual((int) $permissions['max_businesses'])
            ->and(DB::table('lb_campaigns')->where('user_id', $user->id)->count())->toBeLessThanOrEqual((int) $permissions['max_campaigns'])
            ->and(DB::table('lb_customers')->where('user_id', $user->id)->count())->toBeGreaterThan(0)
            ->and(DB::table('lb_qr_scans')->where('user_id', $user->id)->where('created_at', '>=', Carbon::now()->subDays(7))->count())->toBeGreaterThan(0)
            ->and(DB::table('lb_qr_scans')->where('user_id', $user->id)->where('created_at', '>=', Carbon::now()->subDays(30))->count())->toBeGreaterThan(0)
            ->and(DB::table('lb_qr_scans')->where('user_id', $user->id)->where('created_at', '>=', Carbon::now()->subDays(90))->count())->toBeGreaterThan(0);
    }

    $demoUserIds = DB::table('users')->whereIn('username', array_keys($expectedPlans))->pluck('id');

    expect(DB::table('lb_businesses')->whereNotIn('user_id', $demoUserIds)->count())->toBe(0)
        ->and(DB::table('lb_campaigns')->whereNotIn('user_id', $demoUserIds)->count())->toBe(0)
        ->and(DB::table('lb_customers')->whereNotIn('user_id', $demoUserIds)->count())->toBe(0)
        ->and(DB::table('blogs')->count())->toBe(86)
        ->and(DB::table('faqs')->count())->toBe(68);
});

