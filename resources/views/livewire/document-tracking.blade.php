<div wire:init="openModal">
    {{-- Document Tracking Modal --}}
    <div id="document-tracking-modal"
        class="hs-overlay hidden size-full fixed top-0 start-0 z-[90] overflow-x-hidden overflow-y-auto "
        role="dialog" tabindex="-1" aria-labelledby="document-tracking-modal-label"
        data-hs-overlay-keyboard="false">
        <div
            class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-4xl sm:w-full m-3 sm:mx-auto">
            <div
                class="flex flex-col bg-white border shadow-sm rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">

                {{-- Header --}}
                <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                    <div>
                        <h3 id="document-tracking-modal-label"
                            class="font-bold text-emerald-700 text-lg dark:text-white">
                            Document Tracking
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-neutral-400">
                            {{ $document['control_no'] ?? 'N/A' }}
                            {{ $document['turnaroundtime']}}
                        </p>
                    </div>
                    <button type="button" wire:click="closeModal"
                        class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                        aria-label="Close" data-hs-overlay="#document-tracking-modal">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Document Details --}}
                <div class="p-4 border-b dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900">
                    <div class="mb-2">
                        <p class="text-xs text-gray-500 dark:text-neutral-400 uppercase">Status</p>
                        <p class="text-md font-medium {{ $this->colorIndicator($document['status']) }} dark:text-neutral-200 break-words">
                            {{ $document['status'] ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="mt-2">
                        <p class="text-xs text-gray-500 dark:text-neutral-400 uppercase">Subject</p>
                        <p class="text-md text-gray-800 dark:text-neutral-200 break-words">
                            {{ $document['subject'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                {{-- Tracking Timeline --}}
                <div class="p-4 overflow-y-auto max-h-[500px]">
                    @if($isLoading)
                        {{-- Loading State --}}
                        <div class="flex justify-center items-center py-8">
                            <div class="animate-spin inline-block size-8 border-[3px] border-current border-t-transparent text-emerald-600 rounded-full"
                                role="status" aria-label="loading">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <span class="ml-3 text-gray-600 dark:text-neutral-400">Loading tracking data...</span>
                        </div>
                    @elseif(count($trackingData) > 0)
                        {{-- Timeline --}}
                        <div class="space-y-4">
                            @foreach($trackingData as $index => $log)
                                <div class="flex gap-x-3">
                                    {{-- Timeline Icon --}}
                                    <div class="relative last:after:hidden after:absolute after:top-7 after:bottom-0 after:start-3.5 after:w-px after:-translate-x-[0.5px] after:bg-gray-200 dark:after:bg-neutral-700">
                                        <div class="relative z-10 size-7 flex justify-center items-center">
                                            <div class="size-2 rounded-full {{ $loop->first ? 'bg-emerald-400' : 'bg-gray-400' }}"></div>
                                        </div>
                                    </div>

                                    {{-- Content --}}
                                    <div class="grow pt-0.5 pb-8">
                                        <div class="flex gap-x-1.5 items-center mb-1">
                                            <h3 class="font-semibold {{ $loop->first ? 'text-emerald-800' : 'text-gray-800'}} dark:text-white">
                                                {{ $log['action']['name'] ?? ($log['description'] ?? 'Document Activity') }}
                                            </h3>
                                            <span class="text-xs {{ $loop->first ? 'text-emerald-500' : 'text-gray-500'}} dark:text-neutral-400">
                                                {{ isset($log['created_at']) ? \Carbon\Carbon::parse($log['created_at'])->tz('Asia/Manila')->format('M d, Y h:i A') : 'N/A' }}
                                            </span>
                                        </div>
                                        
                                        <div class="mt-1 text-sm {{ $loop->first ? 'text-emerald-700' : 'text-gray-600'}} dark:text-neutral-400">
                                            @if(isset($log['office_id']))
                                                <p><span class="font-medium">Office:</span> {{ $log['action']['name'] === "For Receiving" ? $this->filterOffice($log['assigned_to']) : $this->filterOffice($log['office_id']) }}</p>
                                            @endif
                                            @if(isset($log['user']['name']))
                                                <p><span class="font-medium">By:</span> {{ $log['user']['name'] }}</p>
                                            @elseif(isset($log['user_id']))
                                                <p><span class="font-medium">User:</span> {{ $this->filterUser($log['user_id']) }}</p>
                                            @endif
                                            @if(isset($log['remarks']) && $log['remarks'])
                                                <p class="mt-1"><span class="font-medium">Remarks:</span> {{ $log['remarks'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- No Tracking Data --}}
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No tracking data
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                                No tracking information available for this document
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t dark:border-neutral-700">
                    <button type="button" wire:click="closeModal"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                        data-hs-overlay="#document-tracking-modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('open-tracking-modal', () => {
        setTimeout(() => {
            const modal = document.getElementById('document-tracking-modal');
            if (modal && window.HSOverlay) {
                window.HSOverlay.open(modal);
            }
        }, 50);
    });

    $wire.on('close-tracking-modal', () => {
        const modal = document.getElementById('document-tracking-modal');
        if (modal && window.HSOverlay) {
            window.HSOverlay.close(modal);
        }
        // Close only the tracking modal; keep the search results so the
        // user returns to the populated search modal underneath.
        Livewire.dispatch('closeTracking');

        // Make sure the search modal is visible again with the previous results.
        const searchModal = document.getElementById('document-search-modal');
        if (searchModal && window.HSOverlay) {
            window.HSOverlay.open(searchModal);
        }
    });

    // Listen for modal close/escape events
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('document-tracking-modal');
        if (modal) {
            // Triggered when modal is closed (button click, ESC, or backdrop click)
            modal.addEventListener('close.hs.overlay', function() {
                Livewire.dispatch('closeTracking');
            });
        }
    });
</script>
@endscript
