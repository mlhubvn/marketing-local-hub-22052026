<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Modules\AdminPlans\Livewire\PlanForm;
use Modules\AdminPlans\Models\AdminPlan;

function createPlanFormTestTables(): void
{
    Schema::create('plans', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->unique();
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

    Schema::create('audit_logs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('causer_user_id')->nullable();
        $table->string('event');
        $table->string('description')->nullable();
        $table->string('subject_type')->nullable();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->string('route_name')->nullable();
        $table->string('area')->default('admin');
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });
}

test('editing a plan preserves permission keys outside the admin form registry', function (): void {
    createPlanFormTestTables();

    $plan = AdminPlan::query()->create([
        'name' => 'Permission Preservation Plan',
        'slug' => 'permission-preservation-plan',
        'status' => true,
        'currency' => 'USD',
        'type' => 1,
        'permissions' => [
            'mlhub' => true,
            'max_businesses' => 3,
            'channels' => true,
            'max_channels' => 12,
            'publishing' => true,
            'max_posts_per_month' => 120,
            'ai_publishing' => true,
            'max_ai_publishing_posts_per_month' => 25,
        ],
    ]);

    Livewire::test(PlanForm::class, ['plan' => $plan])
        ->call('save')
        ->assertHasNoErrors();

    expect($plan->refresh()->permissions)
        ->channels->toBeTrue()
        ->max_channels->toBe(12)
        ->publishing->toBeTrue()
        ->max_posts_per_month->toBe(120)
        ->ai_publishing->toBeTrue()
        ->max_ai_publishing_posts_per_month->toBe(25);
});
