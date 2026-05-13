{{-- Forward --}}
<div wire:ignore.self id="document-forward-modal"
    class="document-modal hs-overlay [--overlay-backdrop:static] hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none"
    role="dialog" tabindex="-1" aria-labelledby="document-forward-modal-label" data-hs-overlay-keyboard="false">
    <div
        class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-3.5rem)] flex items-center">
        <div
            class="w-full flex flex-col bg-white border shadow-sm rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                        Forward Pending Documents
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        Number of Documents: {{ count($this->selected_item) }}
                    </p>
                </div>
                <button type="button"
                    class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                    aria-label="Close" data-hs-overlay="#document-forward-modal">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="forward">
                <div class="p-4 overflow-y-auto">
                    <!-- Floating Select -->
                    <div class="relative">
                        <select wire:model.live='assignedTo' class="peer p-4 pe-9 block w-full bg-gray-100 border-transparent rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:border-transparent dark:text-neutral-400 dark:focus:ring-neutral-600
                            focus:pt-6
                            focus:pb-2
                            [&:not(:placeholder-shown)]:pt-6
                            [&:not(:placeholder-shown)]:pb-2
                            autofill:pt-6
                            autofill:pb-2">
                            <option> Select Office</option>
                            @foreach ($this->offices as $office)
                            <option value="{{ $office['id'] }}"> {{ $office['officeName'] }} </option>
                            @endforeach
                        </select>
                        <label
                            class="absolute top-0 start-0 p-4 h-full truncate pointer-events-none transition ease-in-out duration-100 border border-transparent dark:text-white peer-disabled:opacity-50 peer-disabled:pointer-events-none
                            peer-focus:text-xs
                            peer-focus:-translate-y-1.5
                            peer-focus:text-gray-500 dark:peer-focus:text-neutral-500
                            peer-[:not(:placeholder-shown)]:text-xs
                            peer-[:not(:placeholder-shown)]:-translate-y-1.5
                            peer-[:not(:placeholder-shown)]:text-gray-500 dark:peer-[:not(:placeholder-shown)]:text-neutral-500">Forward
                            to</label>
                    </div>
                    <!-- End Floating Select -->

                    @if($subEmployees)
                    <div class="max-w-full py-4 mt-2">
                        <label for="endorsedToOtherPersonnel" class="block text-sm font-medium mb-2 dark:text-white">Endorsed
                            To <span class="text-sm text-gray-500">(optional)</span></label>
                        <select wire:model='endorsedToOtherPersonnel'
                            class="py-3 px-4 pe-9 block w-full border-gray-200 bg-gray-100 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                            <option selected="">Select Personnel </option>
                            @foreach($this->subEmployees as $employee)
                            <option value="{{ $employee['id'] }}">{{ $employee['lastName'] . ', ' .
                                $employee['firstName'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="max-w-full py-4 mt-2">
                        <label for="remarks" class="block text-sm font-medium mb-2 dark:text-white">Remarks</label>
                        <textarea wire:model='remarks' id="remarks"
                            class="py-3 px-4 block w-full border-gray-200 bg-gray-100 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                            rows="3" placeholder="Type remarks.."></textarea>
                    </div>
                    {{-- End of Text Input --}}
                </div>
                <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t dark:border-neutral-700">
                    <button type="button"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-red-600 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:bg-red-700 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                        data-hs-overlay="#document-forward-modal">
                        Cancel
                    </button>
                    <button type="submit"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700 disabled:opacity-50 disabled:pointer-events-none">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
                            <path
                                d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z" />
                            <path d="m21.854 2.147-10.94 10.939" />
                        </svg>
                        Forward
                    </button>
                </div>

                {{-- Modal Loading --}}
                <div wire:loading>
                    <div class="absolute top-1/2 start-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <div class="animate-spin inline-block size-8 border-[3px] border-current border-t-transparent text-emerald-600 rounded-full"
                            role="status" aria-label="loading">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                {{-- End of Modal Loading --}}
            </form>

        </div>
    </div>
</div>
{{-- End of Forward --}}

{{-- Generate Electronic Logbook --}}
<div wire:ignore.self id="generate-logbook-modal"
    class="document-modal hs-overlay [--overlay-backdrop:static] hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none"
    role="dialog" tabindex="-1" aria-labelledby="generate-logbook-modal-label" data-hs-overlay-keyboard="false">
    <div
        class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-3.5rem)] flex items-center">
        <div
            class="w-full flex flex-col bg-white border shadow-sm rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                        Generate Electronic Logbook
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        Number of Documents: {{ count($this->selected_item) }}
                    </p>
                </div>
                <button type="button"
                    class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                    aria-label="Close" data-hs-overlay="#generate-logbook-modal">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t dark:border-neutral-700">
                <button type="button"
                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-red-600 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:bg-red-700 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                    data-hs-overlay="#generate-logbook-modal">
                    Cancel
                </button>
                <a href="{{ route('inbox.generate-logbook', ['selected_items' => implode(',', $this->selected_item)]) }}" target="_blank" 
                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700 disabled:opacity-50 disabled:pointer-events-none">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-notebook-text-icon lucide-notebook-text">
                        <path d="M2 6h4" />
                        <path d="M2 10h4" />
                        <path d="M2 14h4" />
                        <path d="M2 18h4" />
                        <rect width="16" height="20" x="4" y="2" rx="2" />
                        <path d="M9.5 8h5" />
                        <path d="M9.5 12H16" />
                        <path d="M9.5 16H14" />
                    </svg>
                    Generate Logbook
                </a>
            </div>

            {{-- Modal Loading Overlay --}}
            <div wire:loading class="fixed z-50 flex items-center justify-center top-1/2 start-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 py-4 max-w-full min-h-[8rem]">
                <div class="bg-white rounded-xl shadow-lg  py-4 px-6 flex flex-col items-center">
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
</div>
{{-- End of Generate Electronic Logbook --}}
