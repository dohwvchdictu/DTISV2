{{-- Return --}}
<div wire:ignore.self id="document-return-modal"
    class="document-modal hs-overlay [--overlay-backdrop:static] hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none"
    role="dialog" tabindex="-1" aria-labelledby="document-return-modal-label" data-hs-overlay-keyboard="false">
    <div
        class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-3.5rem)] flex items-center">
        <div
            class="w-full flex flex-col bg-white border shadow-sm rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-red-700 dark:text-neutral-200">
                        Return Document
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        {{ $control_no }}
                    </p>
                </div>
                <button type="button"
                    class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                    aria-label="Close" data-hs-overlay="#document-return-modal">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="confirmReturnDocument">
                <div class="p-4 overflow-y-auto">
                    <div class="max-w-sm space-y-3">
                        <input wire:model='returnedDocument' type="text" value="{{$this->returnedDocument}}"
                            class="hidden py-3 px-4 w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                            placeholder="This is placeholder" readonly>
                    </div>
                    <!-- Floating Select -->
                    <div class="relative">
                        <select wire:model="returnTo" class="peer p-4 pe-9 block w-full bg-gray-100 border-transparent rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:border-transparent dark:text-neutral-400 dark:focus:ring-neutral-600
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
                            class="absolute top-0 start-0 p-4 h-full truncate pointer-events-none transition ease-in-out duration-100 border border-transparent peer-disabled:opacity-50 peer-disabled:pointer-events-none
                            peer-focus:text-xs
                            peer-focus:-translate-y-1.5
                            peer-focus:text-gray-500 dark:peer-focus:text-neutral-500
                            peer-[:not(:placeholder-shown)]:text-xs
                            peer-[:not(:placeholder-shown)]:-translate-y-1.5
                            peer-[:not(:placeholder-shown)]:text-gray-500 dark:peer-[:not(:placeholder-shown)]:text-neutral-500">Return
                            to</label>
                    </div>
                    <!-- End Floating Select -->

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
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-gray-600 text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:bg-gray-700 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                        data-hs-overlay="#document-return-modal">
                        Cancel
                    </button>
                    <button type="submit"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:bg-red-700 disabled:opacity-50 disabled:pointer-events-none">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
                            <path
                                d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z" />
                            <path d="m21.854 2.147-10.94 10.939" />
                        </svg>
                        Return
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
{{-- End of Return --}}

{{-- Timeline --}}
<div wire:ignore.self id="document-timeline-modal"
    class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none"
    role="dialog" tabindex="-1" aria-labelledby="document-timeline-modal-label">
    <div
        class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all lg:max-w-4xl lg:w-full m-3 lg:mx-auto h-[calc(100%-3.5rem)] min-h-[calc(100%-3.5rem)] flex items-center">
        <div
            class="w-full max-h-full overflow-hidden flex flex-col bg-white border shadow-sm rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                <div class="mb-2">
                    <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                        Tracking Details
                    </h2>
                    <span class="text-sm text-gray-600 dark:text-neutral-400 mb-4">
                        {{ $control_no }}
                    </span>
                </div>
                <div>
                    <span class="text-sm px-4 py-2 rounded-lg bg-gray-100 text-gray-600 mr-3">
                        <em>{{'Calculated Turnaround Time: '. $turnaround_time . ' ' . $this->suffixTurnaroundTime()
                            }}</em>
                    </span>
                    <button type="button"
                        class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600"
                        aria-label="Close" data-hs-overlay="#document-timeline-modal">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-4 overflow-y-auto">
                <div class="space-y-4">
                    <!-- Timeline -->
                    <div>
                        @forelse ($logs as $key => $log)
                        <!-- Item -->
                        <div wire:key='{{ $log->id }}' class="flex gap-x-3">
                            <!-- Left Content -->
                            <div class="w-28     text-end">
                                <span class="text-xs text-gray-500 dark:text-neutral-400">{{
                                    Carbon\Carbon::parse($log['created_at'])->format('d M')}}</span>
                                <span class="text-xs text-gray-500 dark:text-neutral-400">{{
                                    Carbon\Carbon::parse($log['created_at'])->format('h:i A')}}</span>
                                <div class="mt-1 my-1">
                                    <span
                                        class="inline-flex items-center gap-1.5 py-1 px-3 rounded-lg text-xs {{ \App\Models\Action::find($log->action_id)->color }} font-medium text-gray-800">
                                        {{ Str::title(\App\Models\Action::find($log->action_id)->name) }}
                                    </span>
                                </div>
                            </div>
                            <!-- End Left Content -->

                            <!-- Icon -->
                            <div
                                class="relative last:after:hidden after:absolute after:top-7 after:bottom-0 after:start-3.5 after:w-px after:-translate-x-[0.5px] after:bg-gray-200 dark:after:bg-neutral-700">
                                <div class="relative z-10 size-7 flex justify-center items-center">
                                    <div
                                        class="size-2 rounded-full {{ $loop->first ? 'bg-emerald-400' : 'bg-gray-400' }}">
                                    </div>
                                </div>
                            </div>
                            <!-- End Icon -->

                            <!-- Right Content -->
                            <div class="grow pt-0.5 pb-8">
                                <h3 class="flex max-w-xl gap-x-1.5 text-sm font-semibold text-gray-800 dark:text-white">
                                    {{ $log['description'] }}
                                </h3>
                                @if($log['endorsed_to'])
                                <p class="mt-1 text-sm text-gray-600 dark:text-neutral-400">
                                    Endorsed to {{ $this->filterUser($log['endorsed_to']) }}
                                </p>
                                @endif
                                <em class="mt-1 text-xs text-gray-600 dark:text-neutral-400">
                                    {{ $log['remarks'] }}
                                </em>
                                <p class="mt-1 text-xs text-gray-600 dark:text-neutral-400">
                                    {{ $this->lookUpOffice($log['office_id']) }}
                                </p>
                                <button type="button"
                                    class="mt-1 -ms-1 p-1 inline-flex items-center gap-x-2 text-xs rounded-lg border border-transparent text-gray-500 bg-gray-100 focus:outline-none focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
                                    <img class="shrink-0 size-4 rounded-full"
                                        src="https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=2&w=300&h=300&q=80"
                                        alt="Avatar">
                                    {{ $this->filterUser($log['user_id']) }}
                                </button>
                            </div>
                            <!-- End Right Content -->
                        </div>
                        <!-- End Item -->
                        @empty
                        <div class="mt-2 text-center bg-gray-50 border border-gray-200 text-sm text-gray-600 rounded-lg p-4 dark:bg-white/10 dark:border-white/10 dark:text-neutral-400"
                            role="alert" tabindex="-1" aria-labelledby="hs-soft-color-secondary-label">
                            <span id="hs-soft-color-secondary-label" class="font-bold">Result:</span> No logs were found
                            for this document!
                        </div>
                        @endforelse
                        <!-- End Timeline -->
                    </div>
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
            </div>
        </div>
    </div>
</div>
{{-- End of Timeline --}}