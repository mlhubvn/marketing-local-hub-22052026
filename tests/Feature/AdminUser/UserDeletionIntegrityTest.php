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
        'sessions',
        'files',
        'ai_prompt_histories',
        'ai_image_jobs',
        'ai_content_plans',
        'custom_domains',
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
        ->and($residue['storage'])->toBe(['local:residue/asset.txt'])
        ->and($residue['clean'])->toBeFalse();
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
    $index->deleteUser($listTarget->id);
    $index->selectedUserIds = [$bulkTargetA->id, $bulkTargetB->id, $admin->id];
    $index->deleteSelectedUsers();

    $form = new UserForm;
    $form->userId = $formTarget->id;
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
        $table->timestamps();
    });

    $migration = require base_path('modules/AppMarketingTemplates/Database/Migrations/2026_07_23_120000_add_user_ownership_to_template_import_tables.php');
    $migration->up();

    expect(Schema::hasColumn('lb_template_imports', 'user_id'))->toBeTrue()
        ->and(Schema::hasColumn('lb_template_packs', 'created_by_user_id'))->toBeTrue();

    $target = makeDeletionUser('template-owner');
    $admin = makeDeletionUser('template-admin', true);
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
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app(DeleteUser::class)->execute($target, $admin->id);

    expect(DB::table('lb_template_imports')->count())->toBe(0)
        ->and(DB::table('lb_template_packs')->count())->toBe(0);

    $migration->down();
});
