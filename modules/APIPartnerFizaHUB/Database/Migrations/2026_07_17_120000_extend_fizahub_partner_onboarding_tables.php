<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_onboarding_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('partner_onboarding_requests', 'requested_package_code')) {
                $table->string('requested_package_code', 64)->nullable()->after('package_code');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'approved_package_code')) {
                $table->string('approved_package_code', 64)->nullable()->after('requested_package_code');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'admin_status')) {
                $table->string('admin_status', 32)->nullable()->after('current_step');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'assigned_consultant_id')) {
                $table->unsignedBigInteger('assigned_consultant_id')->nullable()->after('admin_status');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'review_note')) {
                $table->text('review_note')->nullable()->after('assigned_consultant_id');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'partner_confirmed_at')) {
                $table->timestamp('partner_confirmed_at')->nullable()->after('review_note');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'consultant_contacted_at')) {
                $table->timestamp('consultant_contacted_at')->nullable()->after('partner_confirmed_at');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'ready_at')) {
                $table->timestamp('ready_at')->nullable()->after('consultant_contacted_at');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('ready_at');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('partner_onboarding_requests', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('cancelled_at');
            }
        });

        // Indexes are separate so a partial previous run (columns already present)
        // does not keep re-adding indexes that already exist.
        Schema::table('partner_onboarding_requests', function (Blueprint $table): void {
            if (! $this->hasIndex('partner_onboarding_requests', 'partner_onboarding_requested_package_index')) {
                $table->index(
                    ['partner_code', 'requested_package_code'],
                    'partner_onboarding_requested_package_index'
                );
            }

            if (! $this->hasIndex('partner_onboarding_requests', 'partner_onboarding_consultant_index')) {
                $table->index(
                    ['partner_code', 'assigned_consultant_id'],
                    'partner_onboarding_consultant_index'
                );
            }
        });

        if (! Schema::hasTable('partner_onboarding_status_histories')) {
            Schema::create('partner_onboarding_status_histories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('onboarding_request_id')
                    ->constrained('partner_onboarding_requests')
                    ->cascadeOnDelete();
                $table->string('from_status', 32)->nullable();
                $table->string('to_status', 32);
                $table->string('changed_by_type', 32);
                $table->unsignedBigInteger('changed_by_id')->nullable();
                $table->text('reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['onboarding_request_id', 'created_at'], 'partner_onboarding_history_request_created_index');
                $table->index(['to_status', 'created_at'], 'partner_onboarding_history_status_created_index');
            });
        }

        if (! Schema::hasTable('partner_package_assignments')) {
            Schema::create('partner_package_assignments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('partner_integration_id')
                    ->constrained('partner_integrations')
                    ->cascadeOnDelete();
                $table->string('package_code', 64);
                $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
                $table->string('status', 32)->default('active');
                $table->timestamp('effective_from')->nullable();
                $table->timestamp('effective_to')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->string('reason', 255)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['partner_integration_id', 'status'], 'partner_package_assignments_integration_status_index');
                $table->index(['package_code', 'status'], 'partner_package_assignments_package_status_index');
            });
        }

        if (! Schema::hasTable('partner_support_presets')) {
            Schema::create('partner_support_presets', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 64);
                $table->string('partner_code', 32);
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('type_id')->nullable();
                $table->string('default_subject', 255)->nullable();
                $table->text('message_template')->nullable();
                $table->json('required_fields')->nullable();
                $table->json('allowed_package_codes')->nullable();
                $table->unsignedInteger('sla_hours')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['partner_code', 'code'], 'partner_support_presets_partner_code_unique');
                $table->index(['partner_code', 'is_active', 'sort_order'], 'partner_support_presets_active_sort_index');
            });
        }

        if (! Schema::hasTable('partner_support_ticket_contexts')) {
            Schema::create('partner_support_ticket_contexts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('support_ticket_id')
                    ->constrained('support_tickets')
                    ->cascadeOnDelete();
                $table->foreignId('partner_integration_id')
                    ->constrained('partner_integrations')
                    ->cascadeOnDelete();
                $table->string('external_business_id', 128);
                $table->string('request_code', 64)->nullable();
                $table->string('package_code', 64)->nullable();
                $table->string('related_resource_type', 64)->nullable();
                $table->string('related_resource_id', 128)->nullable();
                $table->json('context')->nullable();
                $table->timestamps();

                $table->unique('support_ticket_id', 'partner_support_ticket_contexts_ticket_unique');
                $table->index(['partner_integration_id', 'request_code'], 'partner_support_contexts_integration_code_index');
            });
        }

        if (! Schema::hasTable('partner_webhook_outbox')) {
            Schema::create('partner_webhook_outbox', function (Blueprint $table): void {
                $table->id();
                $table->string('partner_code', 32);
                $table->string('event_type', 64);
                $table->string('dedupe_key', 191);
                $table->string('endpoint_path', 255);
                $table->json('payload');
                $table->string('status', 32)->default('pending');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamp('available_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->text('last_error')->nullable();
                $table->string('signature_hash', 128)->nullable();
                $table->timestamps();

                $table->unique(['partner_code', 'dedupe_key'], 'partner_webhook_outbox_partner_dedupe_unique');
                $table->index(['status', 'available_at'], 'partner_webhook_outbox_status_available_index');
                $table->index(['partner_code', 'event_type'], 'partner_webhook_outbox_partner_event_index');
            });
        }

        if (! Schema::hasTable('partner_support_attachments')) {
            Schema::create('partner_support_attachments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('support_ticket_id')
                    ->constrained('support_tickets')
                    ->cascadeOnDelete();
                $table->string('id_secure', 40)->unique();
                $table->string('original_name', 255);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->string('disk', 40)->default('local');
                $table->string('path', 500);
                $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
                $table->timestamps();

                $table->index(['support_ticket_id', 'created_at'], 'partner_support_attachments_ticket_created_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_support_attachments');
        Schema::dropIfExists('partner_webhook_outbox');
        Schema::dropIfExists('partner_support_ticket_contexts');
        Schema::dropIfExists('partner_support_presets');
        Schema::dropIfExists('partner_package_assignments');
        Schema::dropIfExists('partner_onboarding_status_histories');

        Schema::table('partner_onboarding_requests', function (Blueprint $table): void {
            if ($this->hasIndex('partner_onboarding_requests', 'partner_onboarding_requested_package_index')) {
                $table->dropIndex('partner_onboarding_requested_package_index');
            }
            if ($this->hasIndex('partner_onboarding_requests', 'partner_onboarding_consultant_index')) {
                $table->dropIndex('partner_onboarding_consultant_index');
            }
        });

        Schema::table('partner_onboarding_requests', function (Blueprint $table): void {
            $columns = [
                'requested_package_code',
                'approved_package_code',
                'admin_status',
                'assigned_consultant_id',
                'review_note',
                'partner_confirmed_at',
                'consultant_contacted_at',
                'ready_at',
                'completed_at',
                'cancelled_at',
                'last_synced_at',
            ];

            $existing = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('partner_onboarding_requests', $column)
            ));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        if (method_exists(Schema::getFacadeRoot(), 'hasIndex')) {
            return Schema::hasIndex($table, $index);
        }

        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $row) {
            if (($row['name'] ?? null) === $index) {
                return true;
            }
        }

        return false;
    }
};
