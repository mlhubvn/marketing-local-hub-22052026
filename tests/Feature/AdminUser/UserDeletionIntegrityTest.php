<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\AdminSettings\Livewire\DeleteUserForm;
use Modules\AdminUser\Actions\DeleteUser;
use Modules\AdminUser\Data\UserDeletionResult;
use Modules\AdminUser\Exceptions\LastSuperAdminDeletionException;
use Modules\AdminUser\Exceptions\SharedTeamOwnershipException;
use Modules\AdminUser\Livewire\UserForm;
use Modules\AdminUser\Livewire\UserIndex;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\UserDeletionResidueInspector;
use Modules\AdminUser\Support\UserDeletionStorageCleanup;

function createUserDeletionIntegrityTables(): void
{
    dropUserDeletionIntegrityTables();

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->boolean('is_super_admin')->default(false);
        $table->string('avatar_path')->nullable();
        $table->string('avatar_disk')->nullable();
        $table->string('remember_token')->nullable();
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('team_user', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('user_id');
        $table->json('managed_account_ids')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('name');
        $table->string('type')->default('other');
        $table->json('qr_design')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_campaigns', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('business_id');
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('lb_customers', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('business_id')->nullable();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('avatar')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_customer_tags', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id')->nullable()->index();
        $table->string('name');
        $table->string('slug');
        $table->boolean('is_system')->default(false);
        $table->timestamps();
        $table->unique(['team_id', 'slug']);
    });

    Schema::create('lb_customer_tag_maps', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id')->nullable()->index();
        $table->unsignedBigInteger('customer_id');
        $table->unsignedBigInteger('tag_id');
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('custom_domains', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('owner_user_id');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->string('domain');
        $table->timestamps();
    });

    foreach (['ai_content_plans', 'ai_image_jobs', 'ai_prompt_histories'] as $tableName) {
        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    Schema::create('files', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('owner_user_id');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->string('disk')->default('local');
        $table->string('path')->nullable();
        $table->boolean('is_folder')->default(false);
        $table->timestamps();
    });

    Schema::create('sessions', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->longText('payload');
        $table->integer('last_activity');
    });

    Schema::create('personal_access_tokens', function (Blueprint $table): void {
        $table->id();
        $table->string('tokenable_type');
        $table->unsignedBigInteger('tokenable_id');
        $table->string('name');
        $table->string('token', 64)->unique();
        $table->timestamps();
    });

    Schema::create('oauth_access_tokens', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->unsignedBigInteger('user_id')->nullable();
    });

    Schema::create('oauth_auth_codes', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->unsignedBigInteger('user_id')->nullable();
    });

    Schema::create('notifications', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('title')->nullable();
        $table->text('message')->nullable();
        $table->timestamps();
    });

    Schema::create('notification_manual', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->string('title')->nullable();
        $table->text('message')->nullable();
        $table->timestamps();
    });

    Schema::create('notification_manual_states', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('notification_manual_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
    });

    Schema::create('social_accounts', function (Blueprint $table): void {
        $table->id();
        $table->string('display_name');
        $table->unsignedBigInteger('created_by_user_id')->nullable();
        $table->string('avatar_disk')->nullable();
        $table->text('avatar_path')->nullable();
        $table->text('access_token')->nullable();
        $table->text('refresh_token')->nullable();
        $table->timestamps();
    });

    Schema::create('posts', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('account_id')->nullable();
        $table->text('data')->nullable();
        $table->timestamps();
    });

    Schema::create('team_conversations', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('created_by_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('team_messages', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('conversation_id');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->longText('body');
        $table->json('attachments')->nullable();
        $table->timestamps();
    });

    Schema::create('team_conversation_participants', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('conversation_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
    });

    Schema::create('team_invitations', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('invited_by_user_id')->nullable();
        $table->unsignedBigInteger('accepted_by_user_id')->nullable();
        $table->string('email')->nullable();
        $table->timestamps();
    });

    Schema::create('team_activity_logs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->unsignedBigInteger('actor_user_id')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    Schema::create('payment_history', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('uid')->nullable();
        $table->string('transaction_id');
        $table->json('meta')->nullable();
    });

    Schema::create('payment_manual', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('uid')->nullable();
        $table->string('payment_id');
        $table->string('payment_info')->nullable();
        $table->text('notes')->nullable();
    });

    Schema::create('payment_subscriptions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('uid')->nullable();
        $table->string('subscription_id')->nullable();
        $table->string('customer_id')->nullable();
        $table->unsignedTinyInteger('status')->default(1);
    });

    Schema::create('credit_usage_logs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('action_key');
        $table->json('metadata')->nullable();
    });

    Schema::create('audit_logs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('causer_user_id')->nullable();
        $table->string('event');
        $table->string('description')->nullable();
        $table->string('subject_type')->nullable();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table): void {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('user_deletion_storage_failures', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('deleted_user_id')->nullable()->index();
        $table->string('asset_fingerprint', 64)->unique();
        $table->string('disk', 80);
        $table->text('encrypted_path');
        $table->boolean('is_directory')->default(false);
        $table->unsignedInteger('attempts')->default(1);
        $table->string('last_error_class')->nullable();
        $table->timestamp('last_attempted_at')->nullable();
        $table->timestamps();
    });
}

function dropUserDeletionIntegrityTables(): void
{
    foreach ([
        'partner_webhook_outbox',
        'partner_api_logs',
        'lb_loyalty_stamps',
        'user_deletion_storage_failures',
        'lb_template_packs',
        'lb_template_imports',
        'password_reset_tokens',
        'audit_logs',
        'credit_usage_logs',
        'payment_subscriptions',
        'payment_manual',
        'payment_history',
        'team_activity_logs',
        'team_invitations',
        'team_conversation_participants',
        'team_messages',
        'team_conversations',
        'posts',
        'social_accounts',
        'notification_manual_states',
        'notification_manual',
        'notifications',
        'oauth_auth_codes',
        'oauth_access_tokens',
        'personal_access_tokens',
        'sessions',
        'files',
        'ai_prompt_histories',
        'ai_image_jobs',
        'ai_content_plans',
        'custom_domains',
        'lb_customer_tag_maps',
        'lb_customer_tags',
        'lb_customers',
        'lb_campaigns',
        'lb_businesses',
        'team_user',
        'teams',
        'users',
    ] as $table) {
        Schema::dropIfExists($table);
    }
}

function makeDeletionUser(string $key, bool $superAdmin = false): User
{
    return User::query()->create([
        'name' => "User {$key}",
        'username' => "user_{$key}",
        'email' => "{$key}@example.test",
        'password' => 'password-password-password-password',
        'is_super_admin' => $superAdmin,
    ]);
}

beforeEach(function (): void {
    Storage::fake('local');
    Storage::fake('public');
    Cache::flush();
    createUserDeletionIntegrityTables();
});

afterEach(function (): void {
    dropUserDeletionIntegrityTables();
});

test('UserDeletion removes owned operational records personal workspace and storage without touching another user', function (): void {
    $target = makeDeletionUser('target');
    $other = makeDeletionUser('other');

    $personalTeamId = DB::table('teams')->insertGetId([
        'name' => 'Target Personal',
        'slug' => 'target-personal',
        'owner_user_id' => $target->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('team_user')->insert([
        'team_id' => $personalTeamId,
        'user_id' => $target->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $otherTeamId = DB::table('teams')->insertGetId([
        'name' => 'Other Team',
        'slug' => 'other-team',
        'owner_user_id' => $other->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('team_user')->insert([
        ['team_id' => $otherTeamId, 'user_id' => $other->id, 'created_at' => now(), 'updated_at' => now()],
        ['team_id' => $otherTeamId, 'user_id' => $target->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $qrLogo = 'business-qr-logos/target.png';
    $customerAvatar = 'customers/target.png';
    $userAvatar = 'avatars/target.png';
    $managedFile = 'users/'.$target->id.'/asset.txt';
    $folderPath = 'users/'.$target->id.'/folder';
    $socialAvatar = 'social-accounts/avatars/target.png';
    $messageAttachment = 'teams/'.$personalTeamId.'/message.txt';

    foreach ([$userAvatar, $managedFile, $folderPath.'/nested.txt', $socialAvatar, $messageAttachment] as $path) {
        Storage::disk('local')->put($path, 'owned');
    }
    foreach ([$qrLogo, $customerAvatar] as $path) {
        Storage::disk('public')->put($path, 'owned');
    }

    $target->forceFill(['avatar_path' => $userAvatar, 'avatar_disk' => 'local'])->save();
    $businessId = DB::table('lb_businesses')->insertGetId([
        'user_id' => $target->id,
        'name' => 'Target Shop',
        'type' => 'other',
        'qr_design' => json_encode(['logo_path' => $qrLogo]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $campaignId = DB::table('lb_campaigns')->insertGetId([
        'user_id' => $target->id,
        'business_id' => $businessId,
        'name' => 'Target Campaign',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('lb_customers')->insert([
        'user_id' => $target->id,
        'team_id' => $personalTeamId,
        'business_id' => $businessId,
        'name' => 'Target Customer',
        'email' => 'customer@example.test',
        'phone' => '0900000000',
        'avatar' => $customerAvatar,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $folderId = DB::table('files')->insertGetId([
        'owner_user_id' => $target->id,
        'team_id' => $personalTeamId,
        'disk' => 'local',
        'path' => $folderPath,
        'is_folder' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $fileId = DB::table('files')->insertGetId([
        'owner_user_id' => $target->id,
        'team_id' => $personalTeamId,
        'parent_id' => $folderId,
        'disk' => 'local',
        'path' => $managedFile,
        'is_folder' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach (['ai_content_plans', 'ai_image_jobs', 'ai_prompt_histories'] as $table) {
        DB::table($table)->insert([
            'owner_user_id' => $target->id,
            'requested_by_user_id' => $target->id,
            'team_id' => $personalTeamId,
            'file_id' => $fileId,
            'metadata' => json_encode(['email' => $target->email]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('custom_domains')->insert([
        'owner_user_id' => $target->id,
        'team_id' => $personalTeamId,
        'domain' => 'target.example.test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('sessions')->insert(['id' => 'target-session', 'user_id' => $target->id, 'payload' => 'secret', 'last_activity' => time()]);
    DB::table('notifications')->insert(['user_id' => $target->id, 'title' => 'Private', 'message' => $target->email, 'created_at' => now(), 'updated_at' => now()]);
    $manualId = DB::table('notification_manual')->insertGetId(['created_by' => $other->id, 'title' => 'Global', 'message' => 'Keep', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('notification_manual_states')->insert(['notification_manual_id' => $manualId, 'user_id' => $target->id, 'created_at' => now(), 'updated_at' => now()]);
    $accountId = DB::table('social_accounts')->insertGetId([
        'display_name' => 'Target Social',
        'created_by_user_id' => $target->id,
        'avatar_disk' => 'local',
        'avatar_path' => $socialAvatar,
        'access_token' => 'access-secret',
        'refresh_token' => 'refresh-secret',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('posts')->insert(['user_id' => $target->id, 'team_id' => $personalTeamId, 'account_id' => $accountId, 'data' => $target->email, 'created_at' => now(), 'updated_at' => now()]);
    $conversationId = DB::table('team_conversations')->insertGetId(['team_id' => $personalTeamId, 'created_by_user_id' => $target->id, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('team_messages')->insert([
        'conversation_id' => $conversationId,
        'user_id' => $target->id,
        'body' => 'Private message',
        'attachments' => json_encode([['disk' => 'local', 'path' => $messageAttachment]]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('team_conversation_participants')->insert(['conversation_id' => $conversationId, 'user_id' => $target->id, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('team_invitations')->insert(['team_id' => $personalTeamId, 'invited_by_user_id' => $target->id, 'email' => 'invite@example.test', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('team_activity_logs')->insert(['team_id' => $personalTeamId, 'owner_user_id' => $target->id, 'actor_user_id' => $target->id, 'metadata' => json_encode(['email' => $target->email]), 'created_at' => now(), 'updated_at' => now()]);
    DB::table('password_reset_tokens')->insert(['email' => $target->email, 'token' => 'reset-secret', 'created_at' => now()]);

    Cache::put("portal.growth_metrics.v1.{$target->id}", ['private' => true], 600);
    Cache::put("portal.plan_usage.v0.{$target->id}.en", ['private' => true], 600);
    Cache::put("portal.plan_usage.v1.{$target->id}.vi", ['private' => true], 600);
    Cache::put("mlhub_ai_context:{$target->id}:team-{$personalTeamId}:en", ['private' => true], 600);
    Cache::put("mlhub_ai_context:{$target->id}:team-{$otherTeamId}:vi", ['private' => true], 600);

    $result = app(DeleteUser::class)->execute($target, $other->id);

    expect($result)->toBeInstanceOf(UserDeletionResult::class)
        ->and($result->deleted)->toBeTrue()
        ->and($result->userId)->toBe($target->id)
        ->and($result->businessesDeleted)->toBe(1)
        ->and($result->personalTeamsDeleted)->toBe(1);

    foreach ([
        ['users', 'id', $target->id],
        ['teams', 'id', $personalTeamId],
        ['lb_businesses', 'user_id', $target->id],
        ['lb_campaigns', 'id', $campaignId],
        ['lb_customers', 'user_id', $target->id],
        ['custom_domains', 'owner_user_id', $target->id],
        ['ai_content_plans', 'owner_user_id', $target->id],
        ['ai_image_jobs', 'owner_user_id', $target->id],
        ['ai_prompt_histories', 'owner_user_id', $target->id],
        ['files', 'owner_user_id', $target->id],
        ['sessions', 'user_id', $target->id],
        ['notifications', 'user_id', $target->id],
        ['notification_manual_states', 'user_id', $target->id],
        ['social_accounts', 'created_by_user_id', $target->id],
        ['posts', 'user_id', $target->id],
        ['team_user', 'user_id', $target->id],
        ['password_reset_tokens', 'email', $target->email],
    ] as [$table, $column, $value]) {
        expect(DB::table($table)->where($column, $value)->count(), "{$table}.{$column} residue")->toBe(0);
    }

    expect(DB::table('users')->where('id', $other->id)->exists())->toBeTrue()
        ->and(DB::table('teams')->where('id', $otherTeamId)->exists())->toBeTrue()
        ->and(DB::table('notification_manual')->where('id', $manualId)->exists())->toBeTrue()
        ->and(Cache::has("portal.growth_metrics.v1.{$target->id}"))->toBeFalse()
        ->and(Cache::has("portal.plan_usage.v0.{$target->id}.en"))->toBeFalse()
        ->and(Cache::has("portal.plan_usage.v1.{$target->id}.vi"))->toBeFalse()
        ->and(Cache::has("mlhub_ai_context:{$target->id}:team-{$personalTeamId}:en"))->toBeFalse()
        ->and(Cache::has("mlhub_ai_context:{$target->id}:team-{$otherTeamId}:vi"))->toBeFalse();

    foreach ([$userAvatar, $managedFile, $folderPath.'/nested.txt', $socialAvatar, $messageAttachment] as $path) {
        Storage::disk('local')->assertMissing($path);
    }
    foreach ([$qrLogo, $customerAvatar] as $path) {
        Storage::disk('public')->assertMissing($path);
    }

    $deletionAudit = json_decode((string) DB::table('audit_logs')
        ->where('event', 'admin.users.delete')
        ->value('metadata'), true);

    expect($result->status)->toBe('completed')
        ->and($result->storageAssetsDeleted)->toBe(7)
        ->and($result->storageAssetsMissing)->toBe(0)
        ->and($result->storageFailureCount)->toBe(0)
        ->and($deletionAudit['status'] ?? null)->toBe('completed')
        ->and($deletionAudit['storage_assets_deleted'] ?? null)->toBe(7)
        ->and($deletionAudit['storage_assets_missing'] ?? null)->toBe(0)
        ->and($deletionAudit['storage_failure_count'] ?? null)->toBe(0);
});

test('UserDeletion blocks an owned shared team before deleting any database row or storage asset', function (): void {
    $target = makeDeletionUser('shared-owner');
    $member = makeDeletionUser('shared-member');
    $avatarPath = 'avatars/shared-owner.png';
    Storage::disk('local')->put($avatarPath, 'avatar');
    $target->forceFill(['avatar_path' => $avatarPath, 'avatar_disk' => 'local'])->save();

    $teamId = DB::table('teams')->insertGetId([
        'name' => 'Shared Team',
        'slug' => 'shared-team',
        'owner_user_id' => $target->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('team_user')->insert([
        ['team_id' => $teamId, 'user_id' => $target->id, 'created_at' => now(), 'updated_at' => now()],
        ['team_id' => $teamId, 'user_id' => $member->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    expect(fn () => app(DeleteUser::class)->execute($target, $member->id))
        ->toThrow(SharedTeamOwnershipException::class);

    expect(DB::table('users')->where('id', $target->id)->exists())->toBeTrue()
        ->and(DB::table('teams')->where('id', $teamId)->value('owner_user_id'))->toBe($target->id);
    Storage::disk('local')->assertExists($avatarPath);
});

test('UserDeletion retains only anonymized accounting and audit records', function (): void {
    $target = makeDeletionUser('finance');
    $admin = makeDeletionUser('finance-admin', true);

    $paymentHistoryId = DB::table('payment_history')->insertGetId([
        'uid' => $target->id,
        'transaction_id' => 'txn-1',
        'meta' => json_encode(['email' => $target->email, 'name' => $target->name]),
    ]);
    $paymentManualId = DB::table('payment_manual')->insertGetId([
        'uid' => $target->id,
        'payment_id' => 'manual-1',
        'payment_info' => $target->email,
        'notes' => $target->name,
    ]);
    $subscriptionId = DB::table('payment_subscriptions')->insertGetId([
        'uid' => $target->id,
        'subscription_id' => 'sub-secret',
        'customer_id' => 'cus-secret',
        'status' => 1,
    ]);
    $creditLogId = DB::table('credit_usage_logs')->insertGetId([
        'user_id' => $target->id,
        'action_key' => 'ai.image',
        'metadata' => json_encode(['email' => $target->email]),
    ]);
    $auditId = DB::table('audit_logs')->insertGetId([
        'causer_user_id' => $target->id,
        'event' => 'profile.updated',
        'description' => $target->name.' updated '.$target->email,
        'subject_type' => User::class,
        'subject_id' => $target->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Secret agent',
        'metadata' => json_encode(['email' => $target->email, 'username' => $target->username]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app(DeleteUser::class)->execute($target, $admin->id);

    expect((array) DB::table('payment_history')->find($paymentHistoryId))
        ->toMatchArray(['uid' => null, 'meta' => null])
        ->and((array) DB::table('payment_manual')->find($paymentManualId))
        ->toMatchArray(['uid' => null, 'payment_info' => null, 'notes' => null])
        ->and((array) DB::table('payment_subscriptions')->find($subscriptionId))
        ->toMatchArray(['uid' => null, 'subscription_id' => null, 'customer_id' => null, 'status' => 0])
        ->and((array) DB::table('credit_usage_logs')->find($creditLogId))
        ->toMatchArray(['user_id' => null, 'metadata' => null])
        ->and((array) DB::table('audit_logs')->find($auditId))
        ->toMatchArray([
            'causer_user_id' => null,
            'description' => 'Retained anonymized audit event.',
            'subject_id' => null,
            'ip_address' => null,
            'user_agent' => null,
            'metadata' => null,
        ]);

    $auditJson = DB::table('audit_logs')->where('event', 'admin.users.delete')->value('metadata');
    expect((string) $auditJson)->not->toContain($target->email)
        ->not->toContain((string) $target->username);
});

test('UserDeletion does not delete storage when the database transaction rolls back', function (): void {
    $target = makeDeletionUser('rollback');
    $path = 'avatars/rollback.png';
    Storage::disk('local')->put($path, 'avatar');
    $target->forceFill(['avatar_path' => $path, 'avatar_disk' => 'local'])->save();

    DB::table('teams')->insert([
        'name' => 'Broken Shared Team',
        'slug' => 'broken-shared-team',
        'owner_user_id' => $target->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $teamId = (int) DB::getPdo()->lastInsertId();
    $other = makeDeletionUser('rollback-member');
    DB::table('team_user')->insert(['team_id' => $teamId, 'user_id' => $other->id, 'created_at' => now(), 'updated_at' => now()]);

    expect(fn () => app(DeleteUser::class)->execute($target, $other->id))
        ->toThrow(SharedTeamOwnershipException::class);

    Storage::disk('local')->assertExists($path);
    expect(DB::table('users')->where('id', $target->id)->exists())->toBeTrue();
});

test('UserDeletion deletes only personal access tokens belonging to the target User model', function (): void {
    $target = makeDeletionUser('token-target');
    $other = makeDeletionUser('token-other');

    DB::table('personal_access_tokens')->insert([
        [
            'tokenable_type' => User::class,
            'tokenable_id' => $target->id,
            'name' => 'target-token',
            'token' => hash('sha256', 'target-token'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'tokenable_type' => User::class,
            'tokenable_id' => $other->id,
            'name' => 'other-token',
            'token' => hash('sha256', 'other-token'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'tokenable_type' => 'App\\Models\\ServiceAccount',
            'tokenable_id' => $target->id,
            'name' => 'same-id-different-type',
            'token' => hash('sha256', 'same-id-different-type'),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    app(DeleteUser::class)->execute($target);

    expect(DB::table('personal_access_tokens')->where('name', 'target-token')->exists())->toBeFalse()
        ->and(DB::table('personal_access_tokens')->where('name', 'other-token')->exists())->toBeTrue()
        ->and(DB::table('personal_access_tokens')->where('name', 'same-id-different-type')->exists())->toBeTrue()
        ->and(User::query()->whereKey($other->id)->exists())->toBeTrue();
});

test('UserDeletion anonymizes audit subjects only when the subject is the target User', function (): void {
    $target = makeDeletionUser('audit-target');
    $other = makeDeletionUser('audit-other');

    $causedOtherSubjectId = DB::table('audit_logs')->insertGetId([
        'causer_user_id' => $target->id,
        'event' => 'business.updated',
        'description' => 'caused by target',
        'subject_type' => 'Modules\\AppBusiness\\Models\\Business',
        'subject_id' => 777,
        'metadata' => json_encode(['source' => 'target']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $targetUserSubjectId = DB::table('audit_logs')->insertGetId([
        'causer_user_id' => $other->id,
        'event' => 'user.updated',
        'description' => 'target user subject',
        'subject_type' => User::class,
        'subject_id' => $target->id,
        'metadata' => json_encode(['source' => 'other']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $sameNumericBusinessSubjectId = DB::table('audit_logs')->insertGetId([
        'causer_user_id' => $other->id,
        'event' => 'business.updated',
        'description' => 'business with same numeric id',
        'subject_type' => 'Modules\\AppBusiness\\Models\\Business',
        'subject_id' => $target->id,
        'metadata' => json_encode(['must' => 'remain']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app(DeleteUser::class)->execute($target);

    expect((array) DB::table('audit_logs')->find($causedOtherSubjectId))
        ->toMatchArray(['causer_user_id' => null, 'subject_id' => null])
        ->and((array) DB::table('audit_logs')->find($targetUserSubjectId))
        ->toMatchArray(['causer_user_id' => null, 'subject_id' => null])
        ->and((array) DB::table('audit_logs')->find($sameNumericBusinessSubjectId))
        ->toMatchArray([
            'causer_user_id' => $other->id,
            'description' => 'business with same numeric id',
            'subject_type' => 'Modules\\AppBusiness\\Models\\Business',
            'subject_id' => $target->id,
        ])
        ->and(json_decode((string) DB::table('audit_logs')
            ->where('id', $sameNumericBusinessSubjectId)
            ->value('metadata'), true))->toBe(['must' => 'remain'])
        ->and(User::query()->whereKey($other->id)->exists())->toBeTrue();
});

test('UserDeletion residue inspector reports exact table and storage leftovers', function (): void {
    $target = makeDeletionUser('residue');
    $teamId = DB::table('teams')->insertGetId([
        'name' => 'Residue Team',
        'slug' => 'residue-team',
        'owner_user_id' => $target->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('custom_domains')->insert([
        'owner_user_id' => $target->id,
        'team_id' => $teamId,
        'domain' => 'residue.example.test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $path = 'residue/asset.txt';
    Storage::disk('local')->put($path, 'residue');

    $inspectorClass = UserDeletionResidueInspector::class;
    $inspector = app($inspectorClass);
    $residue = $inspector->inspect([
        'user_id' => $target->id,
        'team_ids' => [$teamId],
        'business_ids' => [],
        'campaign_ids' => [],
        'customer_ids' => [],
        'external_business_ids' => [],
        'request_ids' => [],
        'storage_assets' => [['disk' => 'local', 'path' => $path, 'directory' => false]],
    ]);

    expect($residue['database'])->toHaveKey('custom_domains.owner_user_id')
        ->and($residue['database']['custom_domains.owner_user_id'])->toBe(1)
        ->and($residue['records'])->toContainEqual([
            'table' => 'custom_domains',
            'column' => 'owner_user_id',
            'record_id' => 1,
            'ownership_reason' => 'direct_user_reference',
            'remaining_reference' => (string) $target->id,
        ])
        ->and($residue['storage'])->toBe(['local:residue/asset.txt'])
        ->and($residue['clean'])->toBeFalse();
});

test('UserDeletion residue inspector catches authentication shared-record and retained PII residue', function (): void {
    $target = makeDeletionUser('deep-residue');
    $other = makeDeletionUser('deep-residue-other');
    $phone = '0909123456';

    DB::table('personal_access_tokens')->insert([
        'tokenable_type' => User::class,
        'tokenable_id' => $target->id,
        'name' => 'residue-token',
        'token' => hash('sha256', 'residue-token'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('oauth_access_tokens')->insert(['id' => 'access-residue', 'user_id' => $target->id]);
    DB::table('oauth_auth_codes')->insert(['id' => 'code-residue', 'user_id' => $target->id]);
    DB::table('password_reset_tokens')->insert([
        'email' => $target->email,
        'token' => 'reset-secret',
        'created_at' => now(),
    ]);
    $teamId = DB::table('teams')->insertGetId([
        'name' => 'Shared residue team',
        'slug' => 'shared-residue-team',
        'owner_user_id' => $other->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $conversationId = DB::table('team_conversations')->insertGetId([
        'team_id' => $teamId,
        'created_by_user_id' => $other->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('team_messages')->insert([
        'conversation_id' => $conversationId,
        'user_id' => $target->id,
        'body' => 'residue',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('social_accounts')->insert([
        'display_name' => 'Shared social',
        'created_by_user_id' => $target->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    Schema::create('lb_loyalty_stamps', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('staff_id')->nullable();
    });
    DB::table('lb_loyalty_stamps')->insert(['staff_id' => $target->id]);
    DB::table('audit_logs')->insert([
        'causer_user_id' => null,
        'event' => 'retained',
        'description' => 'Retained '.$target->email,
        'metadata' => json_encode(['username' => $target->username, 'phone' => $phone]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $residue = app(UserDeletionResidueInspector::class)->inspect([
        'user_id' => $target->id,
        'user_email' => $target->email,
        'user_username' => $target->username,
        'user_phone' => $phone,
        'team_ids' => [],
        'business_ids' => [],
        'campaign_ids' => [],
        'customer_ids' => [],
        'external_business_ids' => [],
        'request_ids' => [],
        'storage_assets' => [],
    ]);

    expect($residue['database'])
        ->toHaveKeys([
            'personal_access_tokens.tokenable_type+tokenable_id',
            'oauth_access_tokens.user_id',
            'oauth_auth_codes.user_id',
            'password_reset_tokens.email',
            'team_messages.user_id',
            'social_accounts.created_by_user_id',
            'lb_loyalty_stamps.staff_id',
        ])
        ->and($residue['json'])->toHaveKeys([
            'audit_logs.description',
            'audit_logs.metadata',
        ])
        ->and($residue['clean'])->toBeFalse()
        ->and(User::query()->whereKey($other->id)->exists())->toBeTrue();
});

test('UserDeletion residue inspector catches FizaHUB request and external business payload residue only in supplied scope', function (): void {
    // Real production schema (partner_code, endpoint, request_id, request_payload,
    // response_payload) — NOT a hand-rolled reduced schema — so the inspector's
    // Schema::hasColumn() checks and partner_code scoping are exercised for real.
    Schema::create('partner_api_logs', function (Blueprint $table): void {
        $table->id();
        $table->string('partner_code', 32);
        $table->string('method', 16);
        $table->string('endpoint', 255);
        $table->string('request_id')->nullable();
        $table->json('request_payload')->nullable();
        $table->json('response_payload')->nullable();
    });
    Schema::create('partner_webhook_outbox', function (Blueprint $table): void {
        $table->id();
        $table->string('partner_code', 32);
        $table->string('dedupe_key');
        $table->string('endpoint_path', 255);
        $table->json('payload')->nullable();
    });

    config()->set('modules.apipartnerfizahub.partner_code', 'fizahub');

    DB::table('partner_api_logs')->insert([
        [
            // Nested under `body`, exactly like HandlePartnerRequest::finalizeLog().
            'partner_code' => 'fizahub',
            'method' => 'POST',
            'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
            'request_id' => 'request-target',
            'request_payload' => json_encode(['body' => ['external_business_id' => 'external-target']]),
            'response_payload' => null,
        ],
        [
            // Endpoint-only match: no external_business_id anywhere in the payload.
            'partner_code' => 'fizahub',
            'method' => 'GET',
            'endpoint' => '/api/v1/partners/fizahub/businesses/external-target/dashboard',
            'request_id' => 'request-endpoint-only',
            'request_payload' => json_encode(['_request_hash' => 'h']),
            'response_payload' => null,
        ],
        [
            'partner_code' => 'fizahub',
            'method' => 'POST',
            'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
            'request_id' => 'request-other',
            'request_payload' => json_encode(['body' => ['external_business_id' => 'external-other']]),
            'response_payload' => null,
        ],
        [
            // Same business id but a different partner_code must never be reported.
            'partner_code' => 'another-partner',
            'method' => 'GET',
            'endpoint' => '/api/v1/partners/another-partner/businesses/external-target/dashboard',
            'request_id' => 'request-cross-partner',
            'request_payload' => null,
            'response_payload' => null,
        ],
    ]);
    DB::table('partner_webhook_outbox')->insert([
        [
            'partner_code' => 'fizahub',
            'dedupe_key' => 'request-target|event',
            'endpoint_path' => '/webhooks/onboarding',
            'payload' => json_encode(['external_business_id' => 'external-target']),
        ],
        [
            'partner_code' => 'fizahub',
            'dedupe_key' => 'request-other|event',
            'endpoint_path' => '/webhooks/onboarding',
            'payload' => json_encode(['external_business_id' => 'external-other']),
        ],
    ]);

    $residue = app(UserDeletionResidueInspector::class)->inspect([
        'user_id' => 99999,
        'team_ids' => [],
        'business_ids' => [],
        'campaign_ids' => [],
        'customer_ids' => [],
        'external_business_ids' => ['external-target'],
        'request_ids' => ['request-target'],
        'storage_assets' => [],
    ]);

    expect($residue['json'])->toMatchArray([
        'partner_api_logs.external_business_id_boundary_match' => 2,
        'partner_api_logs.request_id_boundary_match' => 1,
        'partner_webhook_outbox.external_business_id_boundary_match' => 1,
        'partner_webhook_outbox.request_id_boundary_match' => 1,
    ])
        ->and($residue['clean'])->toBeFalse()
        ->and(collect($residue['records'])->pluck('remaining_reference'))
        ->not->toContain('external-other')
        ->not->toContain('request-other');
});

test('UserDeletion residue inspector is clean after a complete deletion', function (): void {
    $target = makeDeletionUser('clean-inspection');
    $admin = makeDeletionUser('clean-inspection-admin', true);
    $teamId = DB::table('teams')->insertGetId([
        'name' => 'Clean Team',
        'slug' => 'clean-team',
        'owner_user_id' => $target->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('team_user')->insert([
        'team_id' => $teamId,
        'user_id' => $target->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $businessId = DB::table('lb_businesses')->insertGetId([
        'user_id' => $target->id,
        'name' => 'Clean Business',
        'type' => 'other',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $campaignId = DB::table('lb_campaigns')->insertGetId([
        'user_id' => $target->id,
        'business_id' => $businessId,
        'name' => 'Clean Campaign',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $customerId = DB::table('lb_customers')->insertGetId([
        'user_id' => $target->id,
        'team_id' => $teamId,
        'business_id' => $businessId,
        'name' => 'Clean Customer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $path = 'clean-inspection/private.txt';
    Storage::disk('local')->put($path, 'private');
    DB::table('files')->insert([
        'owner_user_id' => $target->id,
        'team_id' => $teamId,
        'disk' => 'local',
        'path' => $path,
        'is_folder' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app(DeleteUser::class)->execute($target, $admin->id);

    $residue = app(UserDeletionResidueInspector::class)->inspect([
        'user_id' => $target->id,
        'team_ids' => [$teamId],
        'business_ids' => [$businessId],
        'campaign_ids' => [$campaignId],
        'customer_ids' => [$customerId],
        'external_business_ids' => [],
        'request_ids' => [],
        'storage_assets' => [['disk' => 'local', 'path' => $path, 'directory' => false]],
    ]);

    expect($residue)->toMatchArray([
        'clean' => true,
        'database' => [],
        'json' => [],
        'storage' => [],
    ]);
});

test('all admin and self-service entrypoints delegate to the same deletion service', function (): void {
    $admin = makeDeletionUser('entry-admin', true);
    $listTarget = makeDeletionUser('entry-list');
    $bulkTargetA = makeDeletionUser('entry-bulk-a');
    $bulkTargetB = makeDeletionUser('entry-bulk-b');
    $formTarget = makeDeletionUser('entry-form');

    Auth::login($admin);

    $index = new UserIndex;
    $index->deleteConfirmation[$listTarget->id] = 'XOA USER '.$listTarget->id;
    $index->deleteUser($listTarget->id);
    $index->selectedUserIds = [$bulkTargetA->id, $bulkTargetB->id, $admin->id];
    $index->deleteSelectedUsers();

    $form = new UserForm;
    $form->userId = $formTarget->id;
    $form->deleteConfirmation = 'XOA USER '.$formTarget->id;
    $form->deleteUser();

    expect(User::query()->whereKey($listTarget->id)->exists())->toBeFalse()
        ->and(User::query()->whereKey($bulkTargetA->id)->exists())->toBeFalse()
        ->and(User::query()->whereKey($bulkTargetB->id)->exists())->toBeFalse()
        ->and(User::query()->whereKey($formTarget->id)->exists())->toBeFalse()
        ->and(User::query()->whereKey($admin->id)->exists())->toBeTrue();

    $selfTarget = makeDeletionUser('entry-self');

    Livewire::actingAs($selfTarget)
        ->test(DeleteUserForm::class)
        ->set('password', 'password-password-password-password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect(User::query()->whereKey($selfTarget->id)->exists())->toBeFalse();
});

test('admin user list reports completed storage cleanup warnings without claiming clean success', function (): void {
    $admin = makeDeletionUser('warning-ui-admin', true);
    $target = makeDeletionUser('warning-ui-target');
    $target->forceFill([
        'avatar_disk' => 'warning-ui-unsupported',
        'avatar_path' => 'avatars/warning-ui.png',
    ])->save();
    config(['filesystems.disks.warning-ui-unsupported' => ['driver' => 'unsupported-test-driver']]);
    Auth::login($admin);

    $index = new UserIndex;
    $index->deleteConfirmation[$target->id] = 'XOA USER '.$target->id;
    $index->deleteUser($target->id);

    expect(session('status'))->toBeNull()
        ->and((string) session('warning'))->toContain('storage')
        ->and(User::query()->whereKey($target->id)->exists())->toBeFalse();
});

test('bulk admin deletion categorizes clean warning verification already-deleted and blocked outcomes', function (): void {
    $admin = makeDeletionUser('bulk-outcome-admin', true);
    $clean = makeDeletionUser('bulk-outcome-clean');
    $warning = makeDeletionUser('bulk-outcome-warning');
    $verification = makeDeletionUser('bulk-outcome-verification');
    $missing = makeDeletionUser('bulk-outcome-missing');
    $blocked = makeDeletionUser('bulk-outcome-blocked');
    Auth::login($admin);

    $fake = new class($clean->id, $warning->id, $verification->id, $missing->id, $blocked->id) extends DeleteUser
    {
        public function __construct(
            private int $cleanId,
            private int $warningId,
            private int $verificationId,
            private int $missingId,
            private int $blockedId,
        ) {}

        public function execute(User $user, ?int $deletedByUserId = null): UserDeletionResult
        {
            if ((int) $user->id === $this->blockedId) {
                throw new RuntimeException('shared team ownership');
            }

            $result = new UserDeletionResult((int) $user->id);

            if ((int) $user->id === $this->cleanId) {
                $result->deleted = true;
                $result->status = 'completed';
            } elseif ((int) $user->id === $this->warningId) {
                $result->deleted = true;
                $result->status = 'completed_with_warnings';
                $result->storageFailureCount = 2;
            } elseif ((int) $user->id === $this->verificationId) {
                $result->deleted = true;
                $result->status = 'failed_verification';
                $result->databaseResidueCount = 3;
            } elseif ((int) $user->id === $this->missingId) {
                $result->status = 'already_deleted';
            }

            return $result;
        }
    };
    app()->instance(DeleteUser::class, $fake);

    $index = new UserIndex;
    $index->selectedUserIds = [
        $clean->id,
        $warning->id,
        $verification->id,
        $missing->id,
        $blocked->id,
    ];
    $index->deleteSelectedUsers();

    expect((string) session('status'))->toContain('1')
        ->and((string) session('warning'))->toContain('#'.$warning->id)
        ->and((string) session('error'))
        ->toContain('#'.$verification->id)
        ->toContain('#'.$missing->id)
        ->toContain('#'.$blocked->id);
});

test('UserDeletion protects the final super administrator', function (): void {
    $lastAdmin = makeDeletionUser('last-admin', true);
    $regularUser = makeDeletionUser('regular');

    expect(fn () => app(DeleteUser::class)->execute($lastAdmin, $regularUser->id))
        ->toThrow(LastSuperAdminDeletionException::class);

    expect(User::query()->whereKey($lastAdmin->id)->exists())->toBeTrue();
});

test('failed post-commit storage deletion is encrypted and can be retried', function (): void {
    $target = makeDeletionUser('storage-retry');
    $path = 'retry/private-asset.txt';
    Storage::disk('local')->put($path, 'private');

    config(['filesystems.disks.user-deletion-retry-test' => ['driver' => 'unsupported-test-driver']]);

    $firstAttempt = app(UserDeletionStorageCleanup::class)->delete($target->id, [
        'disk' => 'user-deletion-retry-test',
        'path' => $path,
        'directory' => false,
    ]);

    $failure = DB::table('user_deletion_storage_failures')->first();

    expect($firstAttempt['deleted'])->toBeFalse()
        ->and($failure)->not->toBeNull()
        ->and((string) $failure->encrypted_path)->not->toContain($path);

    config(['filesystems.disks.user-deletion-retry-test' => [
        'driver' => 'local',
        'root' => Storage::disk('local')->path(''),
        'throw' => false,
    ]]);
    Storage::forgetDisk('user-deletion-retry-test');

    $exitCode = Artisan::call('mlhub:user-deletion-storage-retry', ['--limit' => 10]);

    expect($exitCode)->toBe(0)
        ->and(DB::table('user_deletion_storage_failures')->count())->toBe(0);
    Storage::disk('local')->assertMissing($path);
});

test('UserDeletion records missing storage separately and remains idempotent', function (): void {
    $target = makeDeletionUser('missing-storage');
    $target->forceFill([
        'avatar_disk' => 'local',
        'avatar_path' => 'avatars/already-missing.png',
    ])->save();

    $result = app(DeleteUser::class)->execute($target, null);
    $secondResult = app(DeleteUser::class)->execute($target, null);
    $audit = json_decode((string) DB::table('audit_logs')
        ->where('event', 'admin.users.delete')
        ->value('metadata'), true);

    expect($result->status)->toBe('completed')
        ->and($result->storageAssetsScheduled)->toBe(1)
        ->and($result->storageAssetsDeleted)->toBe(0)
        ->and($result->storageAssetsMissing)->toBe(1)
        ->and($audit['storage_assets_missing'] ?? null)->toBe(1)
        ->and($secondResult->status)->toBe('already_deleted')
        ->and($secondResult->deleted)->toBeFalse();
});

test('UserDeletion audit exposes storage failure reason and pending retry status', function (): void {
    $target = makeDeletionUser('storage-audit-failure');
    $target->forceFill([
        'avatar_disk' => 'user-deletion-audit-failure',
        'avatar_path' => 'avatars/private.png',
    ])->save();
    config([
        'filesystems.disks.user-deletion-audit-failure' => [
            'driver' => 'unsupported-test-driver',
        ],
    ]);

    $result = app(DeleteUser::class)->execute($target, null);
    $audit = json_decode((string) DB::table('audit_logs')
        ->where('event', 'admin.users.delete')
        ->value('metadata'), true);

    expect($result->status)->toBe('completed_with_warnings')
        ->and($result->storageFailureCount)->toBe(1)
        ->and($audit['storage_failure_count'] ?? null)->toBe(1)
        ->and($audit['storage_failures'][0]['reason'] ?? null)->not->toBeNull()
        ->and($audit['storage_failures'][0]['retry_status'] ?? null)->toBe('pending')
        ->and(DB::table('user_deletion_storage_failures')->count())->toBe(1);
});

test('UserDeletion never removes another users file from a shared directory', function (): void {
    $target = makeDeletionUser('shared-storage-target');
    $other = makeDeletionUser('shared-storage-other');
    $targetPath = 'uploads/shared/target.txt';
    $otherPath = 'uploads/shared/other.txt';

    Storage::disk('local')->put($targetPath, 'target');
    Storage::disk('local')->put($otherPath, 'other');
    DB::table('files')->insert([
        [
            'owner_user_id' => $target->id,
            'disk' => 'local',
            'path' => 'uploads/shared',
            'is_folder' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'owner_user_id' => $target->id,
            'disk' => 'local',
            'path' => $targetPath,
            'is_folder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'owner_user_id' => $other->id,
            'disk' => 'local',
            'path' => $otherPath,
            'is_folder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $result = app(DeleteUser::class)->execute($target);

    Storage::disk('local')->assertMissing($targetPath);
    Storage::disk('local')->assertExists($otherPath);
    expect(DB::table('files')->where('owner_user_id', $other->id)->where('path', $otherPath)->exists())->toBeTrue()
        ->and($result->storageFailureCount)->toBe(1)
        ->and($result->storageFailures[0]['reason'] ?? null)->toBe('shared_storage_path');
});

test('UserDeletion refuses a broad top-level directory asset', function (): void {
    $target = makeDeletionUser('unsafe-storage-target');
    $other = makeDeletionUser('unsafe-storage-other');

    Storage::disk('local')->put('files/other.txt', 'other');
    DB::table('files')->insert([
        [
            'owner_user_id' => $target->id,
            'disk' => 'local',
            'path' => 'files',
            'is_folder' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'owner_user_id' => $other->id,
            'disk' => 'local',
            'path' => 'files/other.txt',
            'is_folder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $result = app(DeleteUser::class)->execute($target);

    Storage::disk('local')->assertExists('files/other.txt');
    expect(DB::table('files')->where('owner_user_id', $other->id)->exists())->toBeTrue()
        ->and($result->storageFailureCount)->toBe(1)
        ->and($result->storageFailures[0]['reason'] ?? null)->toBe('unsafe_asset_path');
});

test('template ownership migration makes new import records deletable by user', function (): void {
    Schema::create('lb_template_imports', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id')->nullable();
        $table->string('file_name');
        $table->string('status')->default('completed');
        $table->unsignedInteger('imported_count')->default(0);
        $table->unsignedInteger('failed_count')->default(0);
        $table->json('error_log')->nullable();
        $table->timestamps();
    });
    Schema::create('lb_template_packs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id')->nullable();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('preview_image')->nullable();
        $table->timestamps();
    });

    $migration = require base_path('modules/AppMarketingTemplates/Database/Migrations/2026_07_23_120000_add_user_ownership_to_template_import_tables.php');
    $migration->up();

    expect(Schema::hasColumn('lb_template_imports', 'user_id'))->toBeTrue()
        ->and(Schema::hasColumn('lb_template_packs', 'created_by_user_id'))->toBeTrue();

    $target = makeDeletionUser('template-owner');
    $admin = makeDeletionUser('template-admin', true);
    $previewPath = 'template-packs/private-pack.png';
    Storage::disk('public')->put($previewPath, 'private preview');
    DB::table('lb_template_imports')->insert([
        'user_id' => $target->id,
        'file_name' => 'private-import.json',
        'status' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('lb_template_packs')->insert([
        'created_by_user_id' => $target->id,
        'name' => 'Private pack',
        'slug' => 'private-pack',
        'preview_image' => $previewPath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app(DeleteUser::class)->execute($target, $admin->id);

    expect(DB::table('lb_template_imports')->count())->toBe(0)
        ->and(DB::table('lb_template_packs')->count())->toBe(0);
    Storage::disk('public')->assertMissing($previewPath);

    $migration->down();
});

test('CRM ownership migration prevents a personal team id collision from deleting another users tag', function (): void {
    DB::table('users')->insert([
        [
            'id' => 50,
            'name' => 'User B',
            'username' => 'crm_user_b',
            'email' => 'crm-user-b@example.test',
            'password' => 'password-password-password-password',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 101,
            'name' => 'User A',
            'username' => 'crm_user_a',
            'email' => 'crm-user-a@example.test',
            'password' => 'password-password-password-password',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('teams')->insert([
        'id' => 50,
        'name' => 'Personal team A',
        'slug' => 'personal-team-a',
        'owner_user_id' => 101,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('team_user')->insert([
        'team_id' => 50,
        'user_id' => 101,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tagA = DB::table('lb_customer_tags')->insertGetId([
        'team_id' => 101,
        'name' => 'Tag A',
        'slug' => 'tag-a',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $tagB = DB::table('lb_customer_tags')->insertGetId([
        'team_id' => 50,
        'name' => 'Tag B',
        'slug' => 'tag-b',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = require base_path('modules/AppAdvancedCustomerCrm/database/migrations/2026_07_23_130000_normalize_advanced_crm_user_ownership.php');
    $migration->up();

    expect(DB::table('lb_customer_tags')->where('id', $tagA)->value('owner_user_id'))->toBe(101)
        ->and(DB::table('lb_customer_tags')->where('id', $tagB)->value('owner_user_id'))->toBe(50);

    app(DeleteUser::class)->execute(User::query()->findOrFail(101), null);

    expect(DB::table('lb_customer_tags')->where('id', $tagA)->exists())->toBeFalse()
        ->and(DB::table('lb_customer_tags')->where('id', $tagB)->exists())->toBeTrue()
        ->and(DB::table('lb_customer_tags')->where('id', $tagB)->value('owner_user_id'))->toBe(50);

    $migration->down();
});

test('orphan cleanup is dry run by default and only executes demonstrably safe CRM cleanup', function (): void {
    Schema::table('lb_customer_tags', function (Blueprint $table): void {
        $table->unsignedBigInteger('owner_user_id')->nullable()->index();
    });
    Schema::create('lb_template_imports', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('file_name');
        $table->timestamps();
    });
    Schema::create('lb_template_packs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('created_by_user_id')->nullable();
        $table->string('source')->default('custom');
        $table->string('visibility')->default('private');
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
    });

    $orphanTagId = DB::table('lb_customer_tags')->insertGetId([
        'team_id' => 999999,
        'owner_user_id' => null,
        'name' => 'Legacy orphan',
        'slug' => 'legacy-orphan',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $packId = DB::table('lb_template_packs')->insertGetId([
        'team_id' => null,
        'created_by_user_id' => null,
        'source' => 'custom',
        'visibility' => 'private',
        'name' => 'Unattributed pack',
        'slug' => 'unattributed-pack',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $importId = DB::table('lb_template_imports')->insertGetId([
        'team_id' => null,
        'user_id' => null,
        'file_name' => 'historical-unattributed-import.json',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(Artisan::call('users:inspect-orphans'))->toBe(0)
        ->and(Artisan::output())->toContain('lb_customer_tags')
        ->toContain('legacy_owner_missing')
        ->and(DB::table('lb_customer_tags')->where('id', $orphanTagId)->exists())->toBeTrue();

    expect(Artisan::call('users:cleanup-orphans'))->toBe(0)
        ->and(DB::table('lb_customer_tags')->where('id', $orphanTagId)->exists())->toBeTrue();

    expect(Artisan::call('users:cleanup-orphans', ['--execute' => true, '--force' => true]))->toBe(0)
        ->and(DB::table('lb_customer_tags')->where('id', $orphanTagId)->exists())->toBeFalse()
        ->and(DB::table('lb_template_packs')->where('id', $packId)->exists())->toBeTrue()
        ->and(DB::table('lb_template_imports')->where('id', $importId)->exists())->toBeTrue();
});

test('UserIndex deleteUser refuses an empty confirmation and never calls the delete service', function (): void {
    $admin = makeDeletionUser('confirm-empty-admin', true);
    $target = makeDeletionUser('confirm-empty-target');
    Auth::login($admin);

    $index = new UserIndex;
    $index->deleteUser($target->id);

    expect(User::query()->whereKey($target->id)->exists())->toBeTrue()
        ->and((string) session('error'))->toContain('XOA USER '.$target->id)
        ->and($index->deleteConfirmation)->toBe([]);
});

test('UserIndex deleteUser refuses a wrong confirmation and never calls the delete service', function (): void {
    $admin = makeDeletionUser('confirm-wrong-admin', true);
    $target = makeDeletionUser('confirm-wrong-target');
    Auth::login($admin);

    $index = new UserIndex;
    $index->deleteConfirmation[$target->id] = 'XOA USER 999999';
    $index->deleteUser($target->id);

    expect(User::query()->whereKey($target->id)->exists())->toBeTrue()
        ->and((string) session('error'))->toContain('XOA USER '.$target->id);
});

test('UserIndex deleteUser succeeds only once the exact confirmation phrase is typed', function (): void {
    $admin = makeDeletionUser('confirm-exact-admin', true);
    $target = makeDeletionUser('confirm-exact-target');
    Auth::login($admin);

    $index = new UserIndex;
    $index->deleteConfirmation[$target->id] = 'XOA USER '.$target->id;
    $index->deleteUser($target->id);

    expect(User::query()->whereKey($target->id)->exists())->toBeFalse()
        ->and((string) session('status'))->toContain('deleted');
});

test('UserForm deleteUser refuses a wrong confirmation and never calls the delete service', function (): void {
    $admin = makeDeletionUser('form-confirm-wrong-admin', true);
    $target = makeDeletionUser('form-confirm-wrong-target');
    Auth::login($admin);

    $form = new UserForm;
    $form->userId = $target->id;
    $form->deleteConfirmation = 'not the right phrase';
    $form->deleteUser();

    expect(User::query()->whereKey($target->id)->exists())->toBeTrue()
        ->and((string) session('error'))->toContain('XOA USER '.$target->id)
        ->and($form->deleteConfirmation)->toBe('');
});

test('UserIndex deleteUser never reports success when verification finds residue', function (): void {
    $admin = makeDeletionUser('residue-ui-admin', true);
    $target = makeDeletionUser('residue-ui-target');
    Auth::login($admin);

    $fake = new class extends DeleteUser
    {
        public function __construct() {}

        public function execute(User $user, ?int $deletedByUserId = null): UserDeletionResult
        {
            $result = new UserDeletionResult((int) $user->id);
            $result->deleted = true;
            $result->status = 'failed_verification';
            $result->databaseResidueCount = 2;

            return $result;
        }
    };
    app()->instance(DeleteUser::class, $fake);

    $index = new UserIndex;
    $index->deleteConfirmation[$target->id] = 'XOA USER '.$target->id;
    $index->deleteUser($target->id);

    expect(session('status'))->toBeNull()
        ->and((string) session('error'))->toContain('2');
});

test('UserIndex deleteUser refuses to delete the account currently signed in even with the exact confirmation', function (): void {
    $admin = makeDeletionUser('self-delete-admin', true);
    Auth::login($admin);

    $index = new UserIndex;
    $index->deleteConfirmation[$admin->id] = 'XOA USER '.$admin->id;
    $index->deleteUser($admin->id);

    expect(User::query()->whereKey($admin->id)->exists())->toBeTrue()
        ->and((string) session('error'))->toBe(__('You cannot delete the account currently signed in.'));
});

test('UserIndex deleteUser with a wrong confirmation deletes zero SQL rows across the whole schema', function (): void {
    $admin = makeDeletionUser('confirm-zero-rows-admin', true);
    $target = makeDeletionUser('confirm-zero-rows-target');
    $usersBefore = DB::table('users')->count();
    Auth::login($admin);

    $index = new UserIndex;
    $index->deleteConfirmation[$target->id] = 'wrong phrase';
    $index->deleteUser($target->id);

    expect(DB::table('users')->count())->toBe($usersBefore)
        ->and(User::query()->whereKey($target->id)->exists())->toBeTrue();
});
