<?php

use Database\Seeders\PlanSeeder;
use Database\Support\IdSequence;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPlans\Support\DefaultSignupPlanResolver;

function createPlansTableForSeederTests(): void
{
    Schema::create('plans', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('status')->default('active');
        $table->boolean('featured')->default(false);
        $table->string('currency')->default('USD');
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
}

test('free plan does not gain google business access from the generic business limit', function (): void {
    $seeder = new class extends PlanSeeder
    {
        public function normalizedFreePermissions(): array
        {
            return $this->normalizePermissions($this->freePermissions());
        }
    };

    expect($seeder->normalizedFreePermissions()['google_business'])->toBeFalse();
});

test('seeds the official 13 plan MKT catalog with one default signup plan', function (): void {
    createPlansTableForSeederTests();

    (new PlanSeeder)->run();

    $expected = [
        'mlhub-free-da-nang' => [0, 3, true, true, true, 0, 0],
        'mlhub-starter-monthly' => [199000, 1, false, false, false, 10, 7],
        'mlhub-starter-yearly' => [1990000, 2, false, false, false, 11, 14],
        'mlhub-starter-lifetime' => [5990000, 3, false, false, false, 12, 0],
        'mlhub-growth-monthly' => [349000, 1, false, false, true, 20, 10],
        'mlhub-growth-yearly' => [3490000, 2, false, false, false, 21, 21],
        'mlhub-growth-lifetime' => [9990000, 3, false, false, false, 22, 0],
        'mlhub-pro-monthly' => [749000, 1, false, false, true, 30, 14],
        'mlhub-pro-yearly' => [7490000, 2, false, false, false, 31, 30],
        'mlhub-pro-lifetime' => [21990000, 3, false, false, false, 32, 0],
        'mlhub-partner-monthly' => [1249000, 1, false, false, false, 40, 14],
        'mlhub-partner-yearly' => [12490000, 2, false, false, false, 41, 30],
        'mlhub-partner-lifetime' => [36990000, 3, false, false, false, 42, 0],
    ];

    expect(DB::table('plans')->count())->toBe(13)
        ->and(DB::table('plans')->where('default_signup_plan', true)->count())->toBe(1)
        ->and(DB::table('plans')->where('free_plan', true)->count())->toBe(1);

    foreach ($expected as $slug => [$price, $type, $free, $default, $featured, $position, $trialDay]) {
        $plan = DB::table('plans')->where('slug', $slug)->first();

        expect($plan)->not->toBeNull()
            ->and((string) $plan->status)->toBe('1')
            ->and((float) $plan->price)->toBe((float) $price)
            ->and((int) $plan->type)->toBe($type)
            ->and((bool) $plan->free_plan)->toBe($free)
            ->and((bool) $plan->default_signup_plan)->toBe($default)
            ->and((bool) $plan->featured)->toBe($featured)
            ->and((int) $plan->position)->toBe($position)
            ->and((int) $plan->trial_day)->toBe($trialDay)
            ->and($plan->currency)->toBe('VND');
    }
});

test('seeding is idempotent by slug', function (): void {
    createPlansTableForSeederTests();

    (new PlanSeeder)->run();
    (new PlanSeeder)->run();

    expect(DB::table('plans')->count())->toBe(13)
        ->and(DB::table('plans')->distinct()->count('slug'))->toBe(13);
});

test('seeding on a legacy catalog does not collide with deterministic ids', function (): void {
    createPlansTableForSeederTests();

    DB::table('plans')->insert([
        'id' => IdSequence::at(0),
        'name' => 'Legacy Starter',
        'slug' => 'starter-monthly',
        'status' => true,
        'permissions' => json_encode([]),
    ]);

    (new PlanSeeder)->run();

    expect(DB::table('plans')->count())->toBe(14)
        ->and(DB::table('plans')->where('slug', 'starter-monthly')->exists())->toBeTrue()
        ->and(DB::table('plans')->where('slug', 'mlhub-free-da-nang')->exists())->toBeTrue();
});

test('update mode updates official plans without deleting custom plans', function (): void {
    createPlansTableForSeederTests();

    DB::table('plans')->insert([
        'name' => 'Custom Plan',
        'slug' => 'custom-plan',
        'status' => false,
        'price' => 123,
        'permissions' => json_encode(['custom_permission' => true]),
    ]);

    DB::table('plans')->insert([
        'name' => 'Outdated Starter',
        'slug' => 'mlhub-starter-monthly',
        'status' => false,
        'currency' => 'VND',
        'price' => 123,
        'default_signup_plan' => true,
        'permissions' => json_encode(['google_business' => false]),
    ]);

    config(['mlhub.seeding_mode' => 'update']);

    (new PlanSeeder)->run();

    expect(DB::table('plans')->where('slug', 'custom-plan')->exists())->toBeTrue()
        ->and(DB::table('plans')->where('slug', 'mlhub-starter-monthly')->first())
        ->name->toBe('MKT Starter Monthly')
        ->status->toBe('1')
        ->currency->toBe('VND')
        ->price->toBe(199000)
        ->default_signup_plan->toBe(0)
        ->and(DB::table('plans')->where('slug', 'mlhub-free-da-nang')->value('default_signup_plan'))->toBe(1);
});

test('default signup resolver selects MKT Free Da Nang', function (): void {
    createPlansTableForSeederTests();

    (new PlanSeeder)->run();

    expect(app(DefaultSignupPlanResolver::class)->resolve()?->slug)->toBe('mlhub-free-da-nang');
});

test('all official plans use the shared MKT permission baseline and AI credit costs', function (): void {
    createPlansTableForSeederTests();

    (new PlanSeeder)->run();

    foreach (DB::table('plans')->get() as $plan) {
        $permissions = json_decode((string) $plan->permissions, true);

        expect($permissions)
            ->credits_usage->toBeTrue()
            ->mlhub->toBeTrue()
            ->files->toBeTrue()
            ->support->toBeTrue()
            ->image_editor->toBeTrue()
            ->search_media_online->toBeTrue()
            ->credit_cost_ai_studio_generate_captions->toBe(1)
            ->credit_cost_ai_studio_repurpose_content->toBe(2)
            ->credit_cost_ai_studio_plan_calendar->toBe(2)
            ->credit_cost_ai_studio_review_reply->toBe(1)
            ->credit_cost_ai_studio_generate_image->toBe(10);
    }
});

test('official plans apply the required tier limits', function (): void {
    createPlansTableForSeederTests();

    (new PlanSeeder)->run();

    $tierPermissions = [
        'free' => [
            'max_businesses' => 1,
            'max_campaigns' => 5,
            'max_landing_pages' => 5,
            'max_qr_codes' => 15,
            'max_templates' => 5,
            'max_storage_size_mb' => 512,
            'max_file_size_mb' => 32,
            'max_team_members' => 1,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_repurpose' => false,
            'ai_studio_content_planner' => false,
            'ai_studio_image' => false,
            'email_automation' => true,
            'max_email_automations' => 1,
            'max_email_templates' => 3,
            'emails_per_month' => 200,
            'automation_delay' => true,
            'automation_conditions' => false,
            'google_business' => false,
            'max_google_business_connections' => 0,
            'max_google_business_locations' => 0,
            'whatsapp_notification' => false,
            'webhook_automation' => false,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 1,
            'max_loyalty_customers' => 100,
            'max_referral_campaigns' => 0,
            'loyalty_rewards' => false,
            'loyalty_staff_redeem' => false,
            'advanced_crm' => false,
            'customer_tags' => 0,
            'customer_segments' => 0,
            'customer_tasks' => 0,
            'crm_automations' => 0,
            'crm_activity_retention_days' => 180,
            'qr_custom_domains' => false,
            'max_custom_domains' => 0,
            'remove_branding' => false,
            'affiliate' => false,
        ],
        'starter' => [
            'max_businesses' => 1,
            'max_campaigns' => 20,
            'max_landing_pages' => 10,
            'max_qr_codes' => 50,
            'max_templates' => 20,
            'max_storage_size_mb' => 512,
            'max_file_size_mb' => 50,
            'max_team_members' => 2,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_repurpose' => false,
            'ai_studio_content_planner' => false,
            'ai_studio_image' => false,
            'email_automation' => true,
            'max_email_automations' => 2,
            'max_email_templates' => 5,
            'emails_per_month' => 500,
            'automation_delay' => true,
            'automation_conditions' => false,
            'google_business' => true,
            'max_google_business_connections' => 1,
            'max_google_business_locations' => 1,
            'google_review_sync' => true,
            'google_review_reply' => false,
            'google_business_insights' => true,
            'google_business_posts' => false,
            'whatsapp_notification' => false,
            'webhook_automation' => false,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 1,
            'max_loyalty_customers' => 200,
            'max_referral_campaigns' => 0,
            'loyalty_rewards' => false,
            'loyalty_staff_redeem' => false,
            'advanced_crm' => true,
            'customer_tags' => 10,
            'customer_segments' => 3,
            'customer_tasks' => 20,
            'crm_automations' => 0,
            'crm_activity_retention_days' => 365,
            'qr_custom_domains' => false,
            'max_custom_domains' => 0,
            'remove_branding' => false,
            'affiliate' => false,
        ],
        'growth' => [
            'max_businesses' => 3,
            'max_campaigns' => 75,
            'max_landing_pages' => 50,
            'max_qr_codes' => 200,
            'max_templates' => 100,
            'max_storage_size_mb' => 2048,
            'max_file_size_mb' => 128,
            'max_team_members' => 5,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_repurpose' => true,
            'ai_studio_content_planner' => true,
            'ai_studio_image' => false,
            'email_automation' => true,
            'max_email_automations' => 10,
            'max_email_templates' => 20,
            'emails_per_month' => 3000,
            'automation_delay' => true,
            'automation_conditions' => true,
            'google_business' => true,
            'max_google_business_connections' => 2,
            'max_google_business_locations' => 3,
            'google_review_sync' => true,
            'google_review_reply' => true,
            'google_business_insights' => true,
            'google_business_posts' => true,
            'whatsapp_notification' => true,
            'max_whatsapp_notifications' => 3,
            'max_whatsapp_templates' => 10,
            'whatsapp_messages_per_month' => 1000,
            'whatsapp_cloud_api' => true,
            'whatsapp_template_messages' => true,
            'webhook_automation' => true,
            'max_webhook_automations' => 5,
            'webhooks_per_month' => 5000,
            'webhook_custom_headers' => false,
            'webhook_retry' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 5,
            'max_loyalty_customers' => 1000,
            'max_referral_campaigns' => 3,
            'loyalty_rewards' => true,
            'loyalty_staff_redeem' => false,
            'advanced_crm' => true,
            'customer_tags' => 50,
            'customer_segments' => 20,
            'customer_tasks' => 200,
            'crm_automations' => 10,
            'crm_activity_retention_days' => 365,
            'qr_custom_domains' => true,
            'max_custom_domains' => 1,
            'remove_branding' => false,
            'affiliate' => true,
        ],
        'pro' => [
            'max_businesses' => 10,
            'max_campaigns' => 300,
            'max_landing_pages' => 200,
            'max_qr_codes' => 1000,
            'max_templates' => 300,
            'max_storage_size_mb' => 10240,
            'max_file_size_mb' => 512,
            'max_team_members' => 20,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_repurpose' => true,
            'ai_studio_content_planner' => true,
            'ai_studio_image' => true,
            'email_automation' => true,
            'max_email_automations' => 50,
            'max_email_templates' => 200,
            'emails_per_month' => 20000,
            'automation_delay' => true,
            'automation_conditions' => true,
            'google_business' => true,
            'max_google_business_connections' => 5,
            'max_google_business_locations' => 20,
            'google_review_sync' => true,
            'google_review_reply' => true,
            'google_business_insights' => true,
            'google_business_posts' => true,
            'whatsapp_notification' => true,
            'max_whatsapp_notifications' => 30,
            'max_whatsapp_templates' => 100,
            'whatsapp_messages_per_month' => 10000,
            'whatsapp_cloud_api' => true,
            'whatsapp_template_messages' => true,
            'webhook_automation' => true,
            'max_webhook_automations' => 30,
            'webhooks_per_month' => 50000,
            'webhook_custom_headers' => true,
            'webhook_retry' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 30,
            'max_loyalty_customers' => 10000,
            'max_referral_campaigns' => 20,
            'loyalty_rewards' => true,
            'loyalty_staff_redeem' => true,
            'advanced_crm' => true,
            'customer_tags' => 500,
            'customer_segments' => 200,
            'customer_tasks' => 2000,
            'crm_automations' => 100,
            'crm_activity_retention_days' => 730,
            'qr_custom_domains' => true,
            'max_custom_domains' => 5,
            'remove_branding' => true,
            'affiliate' => true,
        ],
        'partner' => [
            'max_businesses' => 100,
            'max_campaigns' => 2000,
            'max_landing_pages' => 1000,
            'max_qr_codes' => 5000,
            'max_templates' => 2000,
            'max_storage_size_mb' => 51200,
            'max_file_size_mb' => 1024,
            'max_team_members' => 100,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_repurpose' => true,
            'ai_studio_content_planner' => true,
            'ai_studio_image' => true,
            'email_automation' => true,
            'max_email_automations' => 300,
            'max_email_templates' => 1000,
            'emails_per_month' => 200000,
            'automation_delay' => true,
            'automation_conditions' => true,
            'google_business' => true,
            'max_google_business_connections' => 30,
            'max_google_business_locations' => 150,
            'google_review_sync' => true,
            'google_review_reply' => true,
            'google_business_insights' => true,
            'google_business_posts' => true,
            'whatsapp_notification' => true,
            'max_whatsapp_notifications' => 200,
            'max_whatsapp_templates' => 500,
            'whatsapp_messages_per_month' => 100000,
            'whatsapp_cloud_api' => true,
            'whatsapp_template_messages' => true,
            'webhook_automation' => true,
            'max_webhook_automations' => 300,
            'webhooks_per_month' => 500000,
            'webhook_custom_headers' => true,
            'webhook_retry' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 300,
            'max_loyalty_customers' => 100000,
            'max_referral_campaigns' => 300,
            'loyalty_rewards' => true,
            'loyalty_staff_redeem' => true,
            'advanced_crm' => true,
            'customer_tags' => 5000,
            'customer_segments' => 2000,
            'customer_tasks' => 50000,
            'crm_automations' => 500,
            'crm_activity_retention_days' => 1095,
            'qr_custom_domains' => true,
            'max_custom_domains' => 50,
            'remove_branding' => true,
            'affiliate' => true,
        ],
    ];

    $expectedPlans = [
        'mlhub-free-da-nang' => ['free', 100],
        'mlhub-starter-monthly' => ['starter', 300],
        'mlhub-starter-yearly' => ['starter', 3600],
        'mlhub-starter-lifetime' => ['starter', 300],
        'mlhub-growth-monthly' => ['growth', 1000],
        'mlhub-growth-yearly' => ['growth', 12000],
        'mlhub-growth-lifetime' => ['growth', 1000],
        'mlhub-pro-monthly' => ['pro', 5000],
        'mlhub-pro-yearly' => ['pro', 60000],
        'mlhub-pro-lifetime' => ['pro', 5000],
        'mlhub-partner-monthly' => ['partner', 20000],
        'mlhub-partner-yearly' => ['partner', 240000],
        'mlhub-partner-lifetime' => ['partner', 20000],
    ];

    foreach ($expectedPlans as $slug => [$tier, $credits]) {
        $permissions = json_decode((string) DB::table('plans')->where('slug', $slug)->value('permissions'), true);
        $expectedPermissions = array_replace($tierPermissions[$tier], ['credits_usage_limit' => $credits]);

        foreach ($expectedPermissions as $key => $value) {
            expect($permissions[$key] ?? null)->toBe($value);
        }
    }
});
