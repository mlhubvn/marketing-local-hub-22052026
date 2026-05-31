<?php

namespace Modules\AppAdvancedCustomerCrm\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminUser\Models\User;
use Modules\AppAdvancedCustomerCrm\Models\CustomerNote;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTag;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTask;
use Modules\AppAdvancedCustomerCrm\Support\CustomerActivityService;
use Modules\AppAdvancedCustomerCrm\Support\CustomerMergeService;
use Modules\AppAdvancedCustomerCrm\Support\CustomerScoreService;
use Modules\AppAdvancedCustomerCrm\Support\CrmAutomationService;
use Modules\AppCustomers\Models\Customer;

#[Title('CRM Customer')]
class CrmCustomerShow extends Component
{
    public Customer $customer;
    public string $tab = 'overview';
    public string $timelineFilter = 'all';
    public string $note = '';
    public bool $notePinned = false;
    public ?int $editingNoteId = null;
    public string $editingNote = '';
    public bool $editingNotePinned = false;
    public string $taskTitle = '';
    public string $taskDescription = '';
    public string $taskType = 'follow_up';
    public string $taskPriority = 'medium';
    public string $taskDueAt = '';
    public string $taskAssignedTo = '';
    public ?int $editingTaskId = null;
    public string $editingTaskTitle = '';
    public string $editingTaskDescription = '';
    public string $editingTaskType = 'follow_up';
    public string $editingTaskPriority = 'medium';
    public string $editingTaskStatus = 'open';
    public string $editingTaskDueAt = '';
    public string $editingTaskAssignedTo = '';
    public string $selectedTagId = '';
    public string $newTagName = '';
    public string $newTagColor = '#0f766e';
    public string $status = 'active';
    public ?string $statusMessage = null;

    public function mount(Customer $customer): void
    {
        abort_unless(! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('advanced_crm') ?? false), 403);
        abort_unless((int) $customer->user_id === (int) auth()->id(), 404);

        $this->customer = $customer;
        $this->status = (string) ($customer->status ?: 'active');
        $this->taskAssignedTo = (string) auth()->id();
    }

    public function addNote(): void
    {
        $payload = $this->validate([
            'note' => ['required', 'string', 'max:3000'],
            'notePinned' => ['boolean'],
        ]);

        CustomerNote::query()->create([
            'team_id' => $this->customer->team_id,
            'business_id' => $this->customer->business_id,
            'customer_id' => $this->customer->id,
            'user_id' => auth()->id(),
            'note' => $payload['note'],
            'visibility' => 'team',
            'pinned' => (bool) $payload['notePinned'],
        ]);

        app(CustomerActivityService::class)->record($this->customer, 'note_created', __('Note added'), ['description' => str($payload['note'])->limit(180)->toString()]);
        app(CrmAutomationService::class)->handle('note_created', $this->customer->refresh(), ['note' => $payload['note']]);
        $this->note = '';
        $this->notePinned = false;
        $this->statusMessage = __('Note added.');
    }

    public function editNote(int $id): void
    {
        $note = CustomerNote::query()->where('customer_id', $this->customer->id)->findOrFail($id);
        $this->editingNoteId = $note->id;
        $this->editingNote = $note->note;
        $this->editingNotePinned = (bool) $note->pinned;
    }

    public function cancelEditNote(): void
    {
        $this->editingNoteId = null;
        $this->editingNote = '';
        $this->editingNotePinned = false;
    }

    public function updateNote(): void
    {
        $payload = $this->validate([
            'editingNote' => ['required', 'string', 'max:3000'],
            'editingNotePinned' => ['boolean'],
        ]);

        $note = CustomerNote::query()->where('customer_id', $this->customer->id)->findOrFail((int) $this->editingNoteId);
        $note->forceFill([
            'note' => $payload['editingNote'],
            'pinned' => (bool) $payload['editingNotePinned'],
        ])->save();

        app(CustomerActivityService::class)->record($this->customer, 'note_updated', __('Note updated'), ['description' => str($payload['editingNote'])->limit(180)->toString()]);
        $this->cancelEditNote();
        $this->statusMessage = __('Note updated.');
    }

    public function toggleNotePin(int $id): void
    {
        $note = CustomerNote::query()->where('customer_id', $this->customer->id)->findOrFail($id);
        $note->forceFill(['pinned' => ! $note->pinned])->save();

        app(CustomerActivityService::class)->record($this->customer, $note->pinned ? 'note_pinned' : 'note_unpinned', $note->pinned ? __('Note pinned') : __('Note unpinned'));
        $this->statusMessage = $note->pinned ? __('Note pinned.') : __('Note unpinned.');
    }

    public function deleteNote(int $id): void
    {
        $note = CustomerNote::query()->where('customer_id', $this->customer->id)->findOrFail($id);
        $preview = str($note->note)->limit(180)->toString();
        $note->delete();

        app(CustomerActivityService::class)->record($this->customer, 'note_deleted', __('Note deleted'), ['description' => $preview]);
        $this->statusMessage = __('Note deleted.');
    }

    public function createTask(): void
    {
        $payload = $this->validate([
            'taskTitle' => ['required', 'string', 'max:255'],
            'taskDescription' => ['nullable', 'string', 'max:2000'],
            'taskType' => ['required', 'string', 'in:call,email,whatsapp,meeting,follow_up,custom'],
            'taskPriority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'taskDueAt' => ['nullable', 'date'],
            'taskAssignedTo' => ['nullable', 'integer'],
        ]);

        CustomerTask::query()->create([
            'team_id' => $this->customer->team_id,
            'business_id' => $this->customer->business_id,
            'customer_id' => $this->customer->id,
            'assigned_to' => filled($payload['taskAssignedTo']) ? (int) $payload['taskAssignedTo'] : auth()->id(),
            'title' => $payload['taskTitle'],
            'description' => $payload['taskDescription'],
            'type' => $payload['taskType'],
            'priority' => $payload['taskPriority'],
            'status' => 'open',
            'due_at' => filled($payload['taskDueAt']) ? $payload['taskDueAt'] : null,
            'created_by' => auth()->id(),
        ]);

        app(CustomerActivityService::class)->record($this->customer, 'task_created', __('Task created'), ['description' => $payload['taskTitle']]);
        app(CrmAutomationService::class)->handle('task_created', $this->customer->refresh(), ['task_title' => $payload['taskTitle']]);
        $this->reset(['taskTitle', 'taskDescription', 'taskDueAt']);
        $this->taskType = 'follow_up';
        $this->taskPriority = 'medium';
        $this->taskAssignedTo = (string) auth()->id();
        $this->statusMessage = __('Task created.');
    }

    public function editTask(int $id): void
    {
        $task = CustomerTask::query()->where('customer_id', $this->customer->id)->findOrFail($id);
        $this->editingTaskId = $task->id;
        $this->editingTaskTitle = $task->title;
        $this->editingTaskDescription = (string) $task->description;
        $this->editingTaskType = $task->type;
        $this->editingTaskPriority = $task->priority;
        $this->editingTaskStatus = $task->status;
        $this->editingTaskDueAt = $task->due_at?->format('Y-m-d\TH:i') ?: '';
        $this->editingTaskAssignedTo = $task->assigned_to ? (string) $task->assigned_to : '';
    }

    public function cancelEditTask(): void
    {
        $this->editingTaskId = null;
        $this->editingTaskTitle = '';
        $this->editingTaskDescription = '';
        $this->editingTaskType = 'follow_up';
        $this->editingTaskPriority = 'medium';
        $this->editingTaskStatus = 'open';
        $this->editingTaskDueAt = '';
        $this->editingTaskAssignedTo = '';
    }

    public function updateTask(): void
    {
        $payload = $this->validate([
            'editingTaskTitle' => ['required', 'string', 'max:255'],
            'editingTaskDescription' => ['nullable', 'string', 'max:2000'],
            'editingTaskType' => ['required', 'string', 'in:call,email,whatsapp,meeting,follow_up,custom'],
            'editingTaskPriority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'editingTaskStatus' => ['required', 'string', 'in:open,in_progress,done,cancelled'],
            'editingTaskDueAt' => ['nullable', 'date'],
            'editingTaskAssignedTo' => ['nullable', 'integer'],
        ]);

        $task = CustomerTask::query()->where('customer_id', $this->customer->id)->findOrFail((int) $this->editingTaskId);
        $task->forceFill([
            'title' => $payload['editingTaskTitle'],
            'description' => $payload['editingTaskDescription'],
            'type' => $payload['editingTaskType'],
            'priority' => $payload['editingTaskPriority'],
            'status' => $payload['editingTaskStatus'],
            'due_at' => filled($payload['editingTaskDueAt']) ? $payload['editingTaskDueAt'] : null,
            'assigned_to' => filled($payload['editingTaskAssignedTo']) ? (int) $payload['editingTaskAssignedTo'] : null,
            'completed_at' => $payload['editingTaskStatus'] === 'done' ? ($task->completed_at ?: now()) : null,
        ])->save();

        app(CustomerActivityService::class)->record($this->customer, 'task_updated', __('Task updated'), ['description' => $payload['editingTaskTitle']]);
        $this->cancelEditTask();
        $this->statusMessage = __('Task updated.');
    }

    public function completeTask(int $id): void
    {
        $task = CustomerTask::query()->where('customer_id', $this->customer->id)->findOrFail($id);
        $task->forceFill(['status' => 'done', 'completed_at' => now()])->save();
        app(CustomerActivityService::class)->record($this->customer, 'task_completed', __('Task completed'), ['description' => __(':task was marked done.', ['task' => $task->title])]);
        $this->statusMessage = __('Task completed.');
    }

    public function rescheduleTask(int $id): void
    {
        $task = CustomerTask::query()->where('customer_id', $this->customer->id)->findOrFail($id);
        $task->forceFill(['due_at' => now()->addDay(), 'status' => $task->status === 'done' ? 'open' : $task->status])->save();

        app(CustomerActivityService::class)->record($this->customer, 'task_rescheduled', __('Task rescheduled'), ['description' => __(':task due tomorrow.', ['task' => $task->title])]);
        $this->statusMessage = __('Task rescheduled for tomorrow.');
    }

    public function deleteTask(int $id): void
    {
        $task = CustomerTask::query()->where('customer_id', $this->customer->id)->findOrFail($id);
        $title = $task->title;
        $task->delete();

        app(CustomerActivityService::class)->record($this->customer, 'task_deleted', __('Task deleted'), ['description' => $title]);
        $this->statusMessage = __('Task deleted.');
    }

    public function addTag(): void
    {
        $payload = $this->validate(['selectedTagId' => ['required', 'integer']]);
        $tag = CustomerTag::query()->findOrFail((int) $payload['selectedTagId']);

        $this->customer->crmTags()->syncWithoutDetaching([
            $tag->id => ['team_id' => $tag->team_id, 'created_by' => auth()->id(), 'created_at' => now()],
        ]);

        app(CustomerActivityService::class)->record($this->customer, 'tag_added', __('Tag added'), ['description' => __(':tag was added to this customer.', ['tag' => $tag->name])]);
        app(CrmAutomationService::class)->handle('customer_tagged', $this->customer->refresh(), ['tag' => $tag->name]);
        $this->selectedTagId = '';
        $this->statusMessage = __('Tag added.');
    }

    public function createAndAddTag(): void
    {
        $payload = $this->validate([
            'newTagName' => ['required', 'string', 'max:80'],
            'newTagColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $tag = CustomerTag::query()->firstOrCreate(
            ['team_id' => auth()->id(), 'slug' => str($payload['newTagName'])->slug()->toString()],
            ['name' => $payload['newTagName'], 'color' => $payload['newTagColor'], 'description' => null, 'is_system' => false]
        );

        $this->customer->crmTags()->syncWithoutDetaching([
            $tag->id => ['team_id' => auth()->id(), 'created_by' => auth()->id(), 'created_at' => now()],
        ]);

        app(CustomerActivityService::class)->record($this->customer, 'tag_added', __('Tag added'), ['description' => __(':tag was added to this customer.', ['tag' => $tag->name])]);
        app(CrmAutomationService::class)->handle('customer_tagged', $this->customer->refresh(), ['tag' => $tag->name]);
        $this->newTagName = '';
        $this->newTagColor = '#0f766e';
        $this->statusMessage = __('Tag created and added.');
    }

    public function removeTag(int $id): void
    {
        $tag = $this->customer->crmTags()->whereKey($id)->firstOrFail();
        $this->customer->crmTags()->detach($tag->id);

        app(CustomerActivityService::class)->record($this->customer, 'tag_removed', __('Tag removed'), ['description' => __(':tag was removed from this customer.', ['tag' => $tag->name])]);
        $this->statusMessage = __('Tag removed.');
    }

    public function updateStatus(): void
    {
        $payload = $this->validate(['status' => ['required', 'string', 'in:new,active,inactive,vip,blocked']]);
        $this->customer->forceFill(['status' => $payload['status']])->save();
        app(CustomerActivityService::class)->record($this->customer, 'status_changed', __('Status changed to :status', ['status' => str($payload['status'])->headline()]));
        app(CrmAutomationService::class)->handle('customer_status_changed', $this->customer->refresh(), ['status' => $payload['status']]);
        $this->statusMessage = __('Status updated.');
    }

    public function addScore(int $points, string $reason): void
    {
        app(CustomerScoreService::class)->add($this->customer, $points, $reason);
        app(CustomerActivityService::class)->record($this->customer, 'score_changed', __('Score changed: :reason', ['reason' => $reason]));
        $this->customer->refresh();
    }

    public function mergeDuplicate(int $duplicateId): void
    {
        $duplicate = Customer::query()->where('user_id', auth()->id())->findOrFail($duplicateId);
        $this->customer = app(CustomerMergeService::class)->merge($this->customer, $duplicate);
        app(CustomerActivityService::class)->record($this->customer, 'customer_merged', __('Duplicate customer merged'));
        $this->statusMessage = __('Duplicate customer merged.');
    }

    public function render(): View
    {
        $this->customer->load([
            'business',
            'crmTags',
            'activities' => fn ($query) => $query->latest('occurred_at')->latest(),
            'notes' => fn ($query) => $query->with('user')->orderByDesc('pinned')->latest(),
            'tasks' => fn ($query) => $query->with('assignedUser')->orderByRaw("case when status in ('open','in_progress') then 0 else 1 end")->orderByRaw('due_at is null')->orderBy('due_at')->latest(),
        ]);
        $phoneDigits = preg_replace('/\D+/', '', (string) $this->customer->phone);

        $duplicates = collect();
        if (filled($this->customer->email) || filled($this->customer->phone)) {
            $duplicates = Customer::query()
                ->where('user_id', auth()->id())
                ->whereKeyNot($this->customer->id)
                ->where(function ($query): void {
                    if (filled($this->customer->email)) {
                        $query->orWhere('email', $this->customer->email);
                    }
                    if (filled($this->customer->phone)) {
                        $query->orWhere('phone', $this->customer->phone);
                    }
                })
                ->limit(5)
                ->get();
        }

        $timelineCategories = $this->timelineCategories();
        $timelineActivities = $this->customer->activities->map(function ($activity) use ($timelineCategories) {
            $activity->crm_category = $this->activityCategory((string) $activity->type);
            $activity->crm_category_label = data_get($timelineCategories, $activity->crm_category.'.label', __('Activity'));
            $activity->crm_module_label = $this->moduleLabel((string) $activity->source_module);

            return $activity;
        });

        if ($this->timelineFilter !== 'all') {
            $timelineActivities = $timelineActivities->where('crm_category', $this->timelineFilter)->values();
        }

        $tagCreatorIds = $this->customer->crmTags
            ->pluck('pivot.created_by')
            ->filter()
            ->unique()
            ->values();
        $tagCreatorNames = User::query()->whereIn('id', $tagCreatorIds)->pluck('name', 'id');
        $currentTags = $this->customer->crmTags->map(fn ($tag) => [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'color' => $tag->color ?: '#0f766e',
            'is_system' => (bool) $tag->is_system,
            'created_at' => $tag->pivot?->created_at,
            'created_at_label' => $tag->pivot?->created_at ? format_datetime_locale(\Illuminate\Support\Carbon::parse($tag->pivot->created_at)) : null,
            'created_by' => $tag->pivot?->created_by,
            'created_by_name' => $tag->pivot?->created_by ? $tagCreatorNames->get($tag->pivot->created_by) : null,
        ]);

        return view('appadvancedcustomercrm::customer-show', [
            'tags' => CustomerTag::query()->where(function ($query): void {
                $query->whereNull('team_id')->orWhere('team_id', auth()->id());
            })->orderBy('name')->get(),
            'duplicates' => $duplicates,
            'recentActivities' => $this->customer->activities->take(5),
            'timelineCategories' => $timelineCategories,
            'timelineActivities' => $timelineActivities,
            'openTasks' => $this->customer->tasks->whereNotIn('status', ['done', 'cancelled'])->take(5),
            'currentTags' => $currentTags,
            'assignableUsers' => $this->assignableUsers(),
            'scoreLabel' => app(CustomerScoreService::class)->label((int) $this->customer->score),
            'emailUrl' => filled($this->customer->email) ? 'mailto:'.$this->customer->email : null,
            'whatsappUrl' => $phoneDigits !== '' ? 'https://wa.me/'.$phoneDigits : null,
        ])->layout(theme_view('layouts.app', 'app'), ['title' => $this->customer->name]);
    }

    protected function timelineCategories(): Collection
    {
        return collect([
            'all' => ['label' => __('All'), 'icon' => 'fa-light fa-timeline'],
            'bookings' => ['label' => __('Bookings'), 'icon' => 'fa-light fa-calendar-check'],
            'coupons' => ['label' => __('Coupons'), 'icon' => 'fa-light fa-ticket'],
            'leads' => ['label' => __('Leads'), 'icon' => 'fa-light fa-inbox'],
            'feedback' => ['label' => __('Feedback'), 'icon' => 'fa-light fa-message-lines'],
            'reviews' => ['label' => __('Reviews'), 'icon' => 'fa-light fa-star'],
            'automation' => ['label' => __('Automation'), 'icon' => 'fa-light fa-wand-magic-sparkles'],
            'notes' => ['label' => __('Notes'), 'icon' => 'fa-light fa-note-sticky'],
            'tasks' => ['label' => __('Tasks'), 'icon' => 'fa-light fa-list-check'],
            'loyalty' => ['label' => __('Loyalty'), 'icon' => 'fa-light fa-stamp'],
            'referral' => ['label' => __('Referral'), 'icon' => 'fa-light fa-share-nodes'],
            'crm' => ['label' => __('CRM'), 'icon' => 'fa-light fa-address-card'],
        ]);
    }

    protected function assignableUsers(): Collection
    {
        $users = collect([auth()->user()]);
        $teamMembers = auth()->user()?->teams()->with('members')->get()->flatMap->members ?? collect();

        return $users
            ->merge($teamMembers)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    protected function activityCategory(string $type): string
    {
        return match (true) {
            str_starts_with($type, 'booking_') => 'bookings',
            str_starts_with($type, 'coupon_') => 'coupons',
            $type === 'lead_submitted' => 'leads',
            in_array($type, ['feedback_submitted', 'low_score_feedback_submitted'], true) => 'feedback',
            in_array($type, ['review_rating_submitted', 'google_review_synced'], true) => 'reviews',
            in_array($type, ['email_sent', 'whatsapp_sent', 'webhook_sent', 'automation_executed'], true) => 'automation',
            str_starts_with($type, 'task_') => 'tasks',
            str_starts_with($type, 'note_') => 'notes',
            in_array($type, ['loyalty_stamp_added', 'reward_unlocked'], true) => 'loyalty',
            $type === 'referral_converted' => 'referral',
            default => 'crm',
        };
    }

    protected function moduleLabel(string $module): string
    {
        return match ($module) {
            'AppBookingPages' => __('Booking'),
            'AppCouponCampaigns' => __('Coupon'),
            'AppFeedbackForms' => __('Feedback'),
            'AppReviewBooster' => __('Review Booster'),
            'AppGoogleBusiness' => __('Google Business'),
            'AppEmailAutomation' => __('Email Automation'),
            'AppWhatsAppNotification' => __('WhatsApp'),
            'AppWebhookAutomation' => __('Webhook'),
            'AppLoyaltyStampCards' => __('Loyalty'),
            'AppReferralInviteFriend' => __('Referral'),
            'advanced_crm' => __('Advanced CRM'),
            default => filled($module) ? str($module)->headline()->toString() : __('LocalBoost'),
        };
    }
}
