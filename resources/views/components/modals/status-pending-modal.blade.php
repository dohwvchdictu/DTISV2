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

{{-- Endorse --}}
<div wire:ignore.self id="document-endorse-modal"
    class="document-modal hs-overlay [--overlay-backdrop:static] hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none"
    role="dialog" tabindex="-1" aria-labelledby="document-endorse-modal-label" data-hs-overlay-keyboard="false">
    <div
        class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-3.5rem)] flex items-center">
        <div
            class="w-full flex flex-col bg-white border shadow-sm rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-indigo-700 dark:text-neutral-200">
                        Endorse Pending Documents
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        Number of Documents: {{ count($this->selected_item) }}
                    </p>
                </div>
                <button type="button"
                    class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                    aria-label="Close" data-hs-overlay="#document-endorse-modal">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="endorse">
                <div class="p-4 overflow-y-auto">
                    <div class="max-w-full py-4 mt-2">
                        <label for="endorsedToPersonnel" class="block text-sm font-medium mb-2 dark:text-white">Endorsed
                            To <span
                                class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span></label>
                        <select wire:model='endorsedToPersonnel'
                            class="py-3 px-4 pe-9 block w-full border-gray-200 bg-gray-100 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                            <option selected="">Select Personnel </option>
                            @foreach($this->filterOfficeEmployees as $employee)
                            <option value="{{ $employee['id'] }}">{{ $employee['lastName'] . ', ' .
                                $employee['firstName'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="max-w-full py-4 mt-2">
                        <label for="remarks" class="block text-sm font-medium mb-2 dark:text-white">Remarks <span
                                class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span></label>
                        <textarea wire:model='remarks' id="remarks" required
                            class="py-3 px-4 block w-full border-gray-200 bg-gray-100 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                            rows="3" placeholder="Type remarks.."></textarea>
                    </div>
                    {{-- End of Text Input --}}

                </div>
                <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t dark:border-neutral-700">
                    <button type="button"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-red-600 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:bg-red-700 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                        data-hs-overlay="#document-endorse-modal">
                        Cancel
                    </button>
                    <button type="submit"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:bg-indigo-700 disabled:opacity-50 disabled:pointer-events-none">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
                            <path
                                d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z" />
                            <path d="m21.854 2.147-10.94 10.939" />
                        </svg>
                        Endorse
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
{{-- End of Endorse --}}

{{-- Input Text Before Close --}}
<div wire:ignore.self id="hs-modal-input-text"
    class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto" role="dialog"
    tabindex="-1" aria-labelledby="hs-modal-input-text-label">
    <div
        class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-3.5rem)] flex items-center">
        <div
            class="w-full max-h-full overflow-hidden flex flex-col bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-neutral-900 dark:border-neutral-800">
            <div class="p-4 sm:p-7">
                <div class="flex justify-between items-center pb-2 px-2 border-b dark:border-neutral-700">
                    <div class="mb-2">
                        <h2 class="text-xl font-bold text-red-600 dark:text-neutral-200">
                            Close Document
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-neutral-400">
                            Number of Documents: {{ count($this->selected_item) }}
                        </p>
                    </div>
                    <button type="button"
                        class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                        aria-label="Close" data-hs-overlay="#hs-modal-input-text">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="mt-5">
                    <!-- Form -->
                    <form wire:submit.prevent='close'>
                        <input type="text" readonly disabled wire:model='phrase'
                            class="block w-full text-center font-serif font-semibold py-2 px-2 mb-2 text-gray-700 text-4xl bg-gray-50">
                        <div class="grid gap-y-4">
                            <!-- Form Group -->
                            <div>
                                <label for="text" class="block text-sm mb-2 dark:text-white">Enter characters in your
                                    screen:
                                    <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <input type="text" wire:model='passphrase' id="passphrase" name="passphrase"
                                        class="py-3 px-4 block w-full border-red-200 rounded-lg text-sm bg-gray-100 focus:border-blue-500 focus:ring-blue-500  dark:bg-neutral-900 dark:border-neutral-800 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                        required aria-describedby="passphrase-error">
                                    <div class="hidden absolute inset-y-0 end-0 pointer-events-none pe-3">
                                        <svg class="size-5 text-red-500" width="16" height="16" fill="currentColor"
                                            viewBox="0 0 16 16" aria-hidden="true">
                                            <path
                                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="hidden text-xs text-red-600 mt-2" id="passphrase-error">Please include a valid
                                    email address so we can get back to you</p>
                            </div>

                            <div>
                                <label for="text" class="block text-sm mb-2 dark:text-white">Remarks: <span
                                        class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span></label>
                                <div class="relative">
                                    <textarea wire:model='remarks' id="remarks" name="remarks"
                                        class="py-2 px-3 block w-full border-gray-200 shadow text-sm rounded-lg text-slate-600 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                        rows="3" placeholder="Type your remarks"></textarea>
                                </div>
                                <p class="hidden text-xs text-red-600 mt-2" id="passphrase-error">Please include a valid
                                    email address so we can get back to you</p>
                            </div>

                            <div class="items-center mx-auto">
                                {{--Default Remarks --}}
                                <div class="max-w-full py-2 flex flex-col sm:inline-flex sm:flex-row">
                                    <button type="button" wire:click='inputRemarks("Approved")'
                                        class="py-2 px-3 inline-flex justify-center items-center gap-2 -ms-px first:rounded-s-lg first:ms-0 last:rounded-e-lg text-sm font-medium focus:z-10 border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        Approved
                                    </button>
                                    <button type="button" wire:click='inputRemarks("Signed")'
                                        class="py-2 px-3 inline-flex justify-center items-center gap-2 -ms-px first:rounded-s-lg first:ms-0 last:rounded-e-lg text-sm font-medium focus:z-10 border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        Signed
                                    </button>
                                    <button type="button" wire:click='inputRemarks("Initialed")'
                                        class="py-2 px-3 inline-flex justify-center items-center gap-2 -ms-px first:rounded-s-lg first:ms-0 last:rounded-e-lg text-sm font-medium focus:z-10 border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        Initialed
                                    </button>
                                    <button type="button" wire:click='inputRemarks("Checked")'
                                        class="py-2 px-3 inline-flex justify-center items-center gap-2 -ms-px first:rounded-s-lg first:ms-0 last:rounded-e-lg text-sm font-medium focus:z-10 border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        Checked
                                    </button>
                                    <button type="button" wire:click='inputRemarks("Processed")'
                                        class="py-2 px-3 inline-flex justify-center items-center gap-2 -ms-px first:rounded-s-lg first:ms-0 last:rounded-e-lg text-sm font-medium focus:z-10 border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        Processed
                                    </button>
                                </div>
                            </div>
                            {{-- End of Default Remarks --}}
                            <!-- End Form Group -->

                            <button type="submit"
                                class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:bg-red-700 disabled:opacity-50 disabled:pointer-events-none">Confirm</button>
                        </div>
                    </form>
                    <!-- End Form -->

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
                </div>
            </div>
        </div>
    </div>
</div>
{{-- End of Input Text --}}