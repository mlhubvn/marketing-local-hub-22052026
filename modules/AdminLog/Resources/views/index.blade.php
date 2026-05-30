<section class="w-full">
    <x-settings.layout :heading="__('Logs')" :subheading="__('View, download, and clear application log files.')">
        <div class="space-y-6">
            @if ($statusMessage)
                <x-ui.alert :variant="$statusVariant" :title="$statusVariant === 'success' ? __('Completed') : __('Action failed')" :description="$statusMessage" />
            @endif

            @if (empty($files))
                <x-ui.alert variant="info" inline :title="__('No log files')" :description="__('There are no .log files in storage/logs yet.')" />
            @else
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-1">
                    <x-theme.section-card :title="__('Log files')" body-class="p-0">
                        <ul class="divide-y divide-zinc-200">
                            @foreach ($files as $file)
                                <li wire:key="log-file-{{ $file['name'] }}">
                                    <button
                                        type="button"
                                        wire:click="selectFile('{{ $file['name'] }}')"
                                        class="flex w-full flex-col items-start gap-1 px-5 py-3 text-left transition hover:bg-zinc-50 {{ $selectedFile === $file['name'] ? 'bg-zinc-100 font-semibold' : '' }}"
                                    >
                                        <span class="flex items-center gap-2 text-sm">
                                            <i class="fa-light fa-file-lines text-zinc-400"></i>
                                            <span class="break-all">{{ $file['name'] }}</span>
                                        </span>
                                        <span class="text-xs text-zinc-500">{{ $file['size_human'] }} · {{ $file['modified'] }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </x-theme.section-card>
                    </div>

                    <div class="lg:col-span-2">
                    <x-theme.section-card :title="$selectedFile ?: __('Select a log file')" body-class="p-6">
                        @if ($selectedFile)
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <x-ui.button type="button" variant="outline" wire:click="refresh" wire:loading.attr="disabled">
                                    <i class="fa-light fa-rotate-right"></i>
                                    <span>{{ __('Refresh') }}</span>
                                </x-ui.button>

                                <x-ui.button type="button" variant="secondary" wire:click="download('{{ $selectedFile }}')" wire:loading.attr="disabled">
                                    <i class="fa-light fa-download"></i>
                                    <span>{{ __('Download') }}</span>
                                </x-ui.button>

                                <x-ui.dialog wire:key="log-clear-{{ $selectedFile }}" :title="__('Clear this log file?')" :description="__('This empties the file contents but keeps the file. This cannot be undone.')" width="sm">
                                    <x-slot:trigger>
                                        <x-ui.button type="button" variant="outline">
                                            <i class="fa-light fa-eraser"></i>
                                            <span>{{ __('Clear') }}</span>
                                        </x-ui.button>
                                    </x-slot:trigger>

                                    <x-slot:footer>
                                        <div class="flex justify-end gap-3">
                                            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                            <x-ui.button type="button" variant="secondary" wire:click="clearFile('{{ $selectedFile }}')" x-on:click="open = false">{{ __('Continue') }}</x-ui.button>
                                        </div>
                                    </x-slot:footer>
                                </x-ui.dialog>

                                <x-ui.dialog wire:key="log-delete-{{ $selectedFile }}" :title="__('Delete this log file?')" :description="__('This permanently removes the file. Laravel will create a new one on the next log write.')" width="sm">
                                    <x-slot:trigger>
                                        <x-ui.button type="button" variant="danger">
                                            <i class="fa-light fa-trash"></i>
                                            <span>{{ __('Delete') }}</span>
                                        </x-ui.button>
                                    </x-slot:trigger>

                                    <x-slot:footer>
                                        <div class="flex justify-end gap-3">
                                            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                            <x-ui.button type="button" variant="danger" wire:click="deleteFile('{{ $selectedFile }}')" x-on:click="open = false">{{ __('Delete') }}</x-ui.button>
                                        </div>
                                    </x-slot:footer>
                                </x-ui.dialog>
                            </div>

                            <pre class="max-h-[32rem] overflow-auto rounded-lg bg-zinc-950 p-4 text-xs leading-relaxed text-zinc-100 whitespace-pre-wrap break-words">{{ filled($content) ? $content : __('This log file is empty.') }}</pre>
                        @else
                            <x-ui.alert variant="info" inline :title="__('Select a log file')" :description="__('Choose a file on the left to preview its latest entries.')" />
                        @endif
                    </x-theme.section-card>
                    </div>
                </div>
            @endif

            <x-ui.alert
                variant="warning"
                inline
                :title="__('Security note')"
                :description="__('Log files can contain sensitive data (stack traces, database hosts, user IDs, emails). Only admins can open this page — never copy logs into the public folder.')"
            />
        </div>
    </x-settings.layout>
</section>
