<div>
    {{-- Search Modal --}}
    <div id="document-search-modal" wire:ignore.self
        class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto"
        role="dialog" tabindex="-1" aria-labelledby="document-search-modal-label">
        <div
            class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-4xl sm:w-full m-3 sm:mx-auto">
            <div
                class="flex flex-col bg-white border shadow-sm rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">

                {{-- Header --}}
                <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                    <h3 id="document-search-modal-label" class="font-bold text-emerald-600 dark:text-white">
                        Search Documents
                    </h3>
                    <button type="button"
                        class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                        aria-label="Close" data-hs-overlay="#document-search-modal">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Search Input --}}
                <div class="p-4 border-b dark:border-neutral-700">
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                            <svg class="shrink-0 size-4 text-gray-400 dark:text-white/60"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="searchQuery" type="text"
                            class="py-3 ps-10 pe-4 block w-full bg-gray-50 border-gray-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:border-neutral-600 dark:text-neutral-400 dark:placeholder:text-neutral-400 dark:focus:ring-neutral-600"
                            placeholder="Search by document subject..." autofocus>
                    </div>

                    @if($searchQuery && strlen($searchQuery) < 2)
                        <p class="mt-2 text-sm text-gray-500 dark:text-neutral-400">
                        Type at least 2 characters to search
                        </p>
                        @endif
                </div>

                {{-- Results --}}
                <div class="p-4 overflow-y-auto max-h-[500px]">
                    @if($isLoading)
                    {{-- Loading State --}}
                    <div class="flex justify-center items-center py-8">
                        <div class="animate-spin inline-block size-8 border-[3px] border-current border-t-transparent text-emerald-600 rounded-full"
                            role="status" aria-label="loading">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <span class="ml-3 text-gray-600 dark:text-neutral-400">Searching...</span>
                    </div>
                    @elseif($searchQuery && count($searchResults) > 0)
                    {{-- Results Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-800">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                                        Document Control No.
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                                        Subject
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @foreach($searchResults as $document)
                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                        {{ $document['control_no'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $document['subject'] ?? 'No subject' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                        <button wire:click="trackDocument({{ $document['id'] }})" type="button"
                                            class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-emerald-600 hover:text-emerald-800 focus:outline-none focus:text-emerald-800 disabled:opacity-50 disabled:pointer-events-none dark:text-emerald-500 dark:hover:text-emerald-400 dark:focus:text-emerald-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-map-pin">
                                                <path
                                                    d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            Track
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @elseif($searchQuery && count($searchResults) === 0 && !$isLoading)
                    {{-- No Results --}}
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-emerald-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No documents found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                            No documents match your search query "{{ $searchQuery }}"
                        </p>
                    </div>
                    @else
                    {{-- Empty State --}}
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-emerald-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <circle cx="11" cy="11" r="8" stroke-width="2" />
                            <path d="m21 21-4.3-4.3" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-emerald-600 dark:text-white">Search documents</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                            Start typing to search for documents by Control Number or Subject
                        </p>
                        <p class="mt-2 text-xs text-gray-400 dark:text-neutral-500">
                            Press <kbd
                                class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded-lg dark:bg-neutral-600 dark:text-neutral-100 dark:border-neutral-500">ESC</kbd>
                            to close
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Modal Loading Overlay --}}
            <div wire:loading class="fixed z-50 flex items-center justify-center top-1/2 start-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 py-4 max-w-full min-h-[8rem]">
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg  py-4 px-6 flex flex-col items-center">
                    <div class="flex items-center gap-4">
                        <div class="animate-spin inline-block size-8 border-[3px] border-current border-t-transparent text-xl text-emerald-600 rounded-full"
                            role="status" aria-label="loading">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="text-emerald-600 text-xl font-medium">Processing...</p>
                    </div>
                </div>
            </div>
            {{-- End of Modal Loading --}}
        </div>
    </div>

    {{-- Document Tracking Modal --}}
    @if($showTrackingModal && $selectedDocument)
    <livewire:document-tracking :document="$selectedDocument" :key="'tracking-'.$selectedDocument['id']" />
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-tracking-modal', () => {
            // Wait for the tracking modal component to be rendered
            setTimeout(() => {
                const modal = document.getElementById('document-tracking-modal');
                if (modal) {
                    if (window.HSOverlay) {
                        window.HSOverlay.open(modal);
                    }
                } else {
                    console.error('Tracking modal not found in DOM');
                }
            }, 200);
        });

        // Listen for clearSearch event from tracking modal
        Livewire.on('clearSearch', () => {
            const component = Livewire.find('{{ $this->getId() }}');
            if (component) {
                component.call('clearSearch');
            }
        });
    });
</script>
