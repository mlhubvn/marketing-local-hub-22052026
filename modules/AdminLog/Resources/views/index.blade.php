<section class="w-full">
    <x-settings.layout :heading="__('Logs')" :subheading="__('View, download, and clear application log files.')">
        <div class="space-y-6">
            @if ($statusMessage)
                <x-ui.alert :variant="$statusVariant" :title="$statusVariant === 'success' ? __('Completed') : __('Action failed')" :description="$statusMessage" />
            @endif

            @if (empty($files))
                <x-ui.alert variant="info" inline :title="__('No log files')" :description="__('There are no .log files in storage/logs yet.')" />
            @elseif ($activeFile && $selectedFile)
                <x-theme.section-card
                    :title="$activeFile['name']"
                    :description="$activeFile['size_human'].' · '.$activeFile['modified']"
                    body-class="p-6"
                >
                    @if (count($files) > 1)
                        <x-slot:meta>
                            <select
                                wire:change="selectFile($event.target.value)"
                                class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                            >
                                @foreach ($files as $file)
                                    <option value="{{ $file['name'] }}" @selected($selectedFile === $file['name'])>{{ $file['name'] }}</option>
                                @endforeach
                            </select>
                        </x-slot:meta>
                    @endif

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
                </x-theme.section-card>
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
