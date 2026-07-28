{{--
    Review panel for the current selection.

    Selections deliberately survive a change of search or filter so a batch can be
    assembled across several searches, which means the selection routinely contains
    documents that are not on the table behind this panel. This is where the user can
    actually see what they have queued and drop individual entries before acting.

    Rows are resolved through the component's eligibilityQuery(), so anything that has
    stopped being actionable since it was picked simply will not appear here — the same
    outcome it would get at submit time.

    wire:ignore.self matches every other Preline overlay in this project: it keeps
    Livewire's morph off the modal root so the overlay's own open/close state survives a
    re-render, while still letting the list inside update when a row is removed.
--}}
<div wire:ignore.self id="selected-documents-modal"
    class="document-modal hs-overlay [--overlay-backdrop:static] hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none"
    role="dialog" tabindex="-1" aria-labelledby="selected-documents-modal-label" data-hs-overlay-keyboard="false">
    <div
        class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-2xl sm:w-full m-3 sm:mx-auto min-h-[calc(100%-3.5rem)] flex items-center">
        <div
            class="w-full flex flex-col bg-white border shadow-sm rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">

            {{-- Header --}}
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                <div>
                    <h2 id="selected-documents-modal-label"
                        class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                        Selected Documents
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        {{ count($this->selected_item) }} selected
                    </p>
                </div>
                <button type="button"
                    class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400"
                    aria-label="Close" data-hs-overlay="#selected-documents-modal">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>

            {{-- List --}}
            <div class="p-4 overflow-y-auto max-h-96">
                @if (count($this->selected_item) > 0)
                    <ul class="flex flex-col divide-y divide-gray-200 dark:divide-neutral-700">
                        @foreach ($this->selectedDocuments() as $selected)
                            <li wire:key="selected-{{ $selected->id }}"
                                class="flex items-center justify-between gap-x-3 py-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-x-2">
                                        <span
                                            class="inline-flex items-center gap-x-1 py-0.5 px-2 rounded-full text-xs font-medium {{ $this->colorIndicator($selected->status) }} text-gray-800 dark:text-neutral-200">
                                            {{ $selected->status }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-800 dark:text-neutral-200 truncate">
                                            {{ $selected->control_no }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-neutral-400 truncate">
                                        {{ $selected->subject }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-neutral-500">
                                        {{ $selected->category->name ?? 'Uncategorised' }}
                                        &middot;
                                        {{ $selected->created_at?->format('d M Y') }}
                                    </p>
                                </div>

                                <button type="button" wire:click='deselect({{ $selected->id }})'
                                    class="hs-tooltip shrink-0 size-8 inline-flex justify-center items-center rounded-full border border-transparent text-gray-500 hover:bg-red-100 hover:text-red-700 focus:outline-none focus:bg-red-100 dark:text-neutral-400 dark:hover:bg-red-500/20 dark:hover:text-red-400">
                                    <span class="sr-only">Remove {{ $selected->control_no }} from selection</span>
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        Nothing is selected right now.
                    </p>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t dark:border-neutral-700">
                <button type="button" wire:click='clearSelection'
                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700"
                    @disabled(count($this->selected_item) === 0)>
                    Clear all
                </button>
                <button type="button"
                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700"
                    data-hs-overlay="#selected-documents-modal">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
