<div class="w-full lg:ps-64">
    <div class="p-4 sm:p-6 lg:pt-1.5 space-y-4 sm:space-y-6">
        {{-- Breadcrumb --}}
        <ol class="flex items-center whitespace-nowrap">
            <li class="inline-flex items-center">
                <a class="flex items-center text-sm text-gray-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 dark:text-neutral-500 dark:hover:text-blue-500 dark:focus:text-blue-500"
                    href="{{ route('dashboard') }}">
                    Home
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center">
                <a class="flex items-center text-sm text-gray-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 dark:text-neutral-500 dark:hover:text-blue-500 dark:focus:text-blue-500"
                    href="#">
                    Document
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                New Document
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        <hr class="border-gray-200 dark:border-neutral-700">

        <!-- Card Section -->
        <div class="max-full px-4 sm:px-6 lg:px-8 mx-auto">
            <!-- Card -->
            <div class="bg-slate-100 dark:bg-neutral-900 p-2">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                        New Document
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        Create new document.
                    </p>
                </div>

                <form>
                    <!-- Grid -->
                    <div class="grid sm:grid-cols-12 gap-2 sm:gap-6">
                        <div class="sm:col-span-2">
                            <label for="control_no"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Document Control No.
                            </label>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10">
                            <div class="max-w-sm space-y-3">
                                <input wire:model="control_no" type="text" id="control_no" name="control_no"
                                    value="{{ $control_no }}"
                                    class="py-3 px-4 block w-full border-gray-200 bg-slate-100 text-slate-600 shadow rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                    placeholder="Document Tracking No." readonly>
                            </div>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-2">
                            <label for="source"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Document Source
                            </label>
                            <span
                                class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10">
                            <div class="max-w-sm space-y-3">
                                <select wire:model="source" id="source" name="source"
                                    class="py-3 px-4 pe-9 block w-full border-gray-200 shadow rounded-lg text-sm text-gray-700 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                                    <option>Select Source</option>
                                    <option value="internal">Internal</option>
                                    <option value="external">External</option>
                                </select>
                            </div>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-2">
                            <label for="af-account-bio"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Citizen Charter?
                            </label>
                            <span
                                class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10">
                            <div class="flex gap-x-6">
                                <div class="flex">
                                    <input wire:model='is_arta' type="radio" id="is_arta-1"
                                        wire:click="$set('showCitizenProcedure', false)" name="citizenRadioGroup"
                                        value="0"
                                        class="shrink-0 mt-0.5 size-5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                        checked>
                                    <label for="citizenRadioGroup-1"
                                        class="text-sm text-gray-500 ms-2 dark:text-neutral-400">No</label>
                                </div>

                                <div class="flex">
                                    <input wire:model='is_arta' type="radio" id="is_arta-2"
                                        wire:click="$set('showCitizenProcedure', true)" name="citizenRadioGroup"
                                        value="1"
                                        class="shrink-0 mt-0.5 size-5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                    <label for="citizenRadioGroup-2"
                                        class="text-sm text-gray-500 ms-2 dark:text-neutral-400">Yes</label>
                                </div>
                            </div>
                        </div>
                        <!-- End Col -->

                        @if($showCitizenProcedure === true)
                        <div class="sm:col-span-2" wire:show="showCitizenProcedure">
                            <label for="af-account-full-name"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Charter Procedure
                            </label>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10" wire:show="showCitizenProcedure">
                            <div class="max-w-sm space-y-3">
                                <!-- Select -->
                                <select wire:model='citizen_charter_id' id="citizen_charter_id"
                                    name="citizen_charter_id"
                                    class="py-3 px-4 pe-9 block w-full border-gray-200 shadow rounded-lg text-sm text-slate-600 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                                    <option value="">Select Citizen Charter</option>
                                    @foreach ($citizen_charters as $charter)
                                    <option value="{{ $charter->id }}"> {{ $charter->name }}</option>
                                    @endforeach
                                </select>
                                <!-- End Select -->
                            </div>
                        </div>
                        @endif
                        <!-- End Col -->

                        <div class="sm:col-span-2">
                            <label for="category_id"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Document Type
                            </label>
                            <span
                                class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10">
                            <div class="max-w-sm space-y-3" wire:ignore>
                                <div class="flex gap-x-6">
                                    <div class="flex">
                                        <input type="radio" name="selectedType" wire:key='type-radio-group-1'
                                            wire:model.live='selectedType'
                                            class="shrink-0 mt-0.5 size-5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                            id="type-radio-group-1" value="All">
                                        <label for="type-radio-group-1"
                                            class="text-sm text-gray-500 ms-2 dark:text-neutral-400">All</label>
                                    </div>

                                    <div class="flex">
                                        <input type="radio" name="selectedType" wire:key='type-radio-group-2'
                                            wire:model.live='selectedType'
                                            class="shrink-0 mt-0.5 size-5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                            id="type-radio-group-2" value="Purchase">
                                        <label for="type-radio-group-2"
                                            class="text-sm text-gray-500 ms-2 dark:text-neutral-400">Purchase
                                            Request</label>
                                    </div>

                                    <div class="flex">
                                        <input type="radio" name="selectedType" wire:key='type-radio-group-3'
                                            wire:model.live='selectedType'
                                            class="shrink-0 mt-0.5 size-5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                            id="type-radio-group-3" value="Payment">
                                        <label for="type-radio-group-3"
                                            class="text-sm text-gray-500 ms-2 dark:text-neutral-400">Payment</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-2">
                            <label for="category_id"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Document Category
                            </label>
                            <span
                                class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10">
                            <div class="max-w-sm space-y-3">
                                <!-- Select -->
                                <select wire:model='category_id' id="category_id" name="category_id" data-hs-select='{
                                    "hasSearch": true,
                                    "searchPlaceholder": "Search...",
                                    "searchClasses": "block w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500 before:absolute before:inset-0 before:z-[1] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 py-2 px-3",
                                    "searchWrapperClasses": "bg-white p-2 -mx-1 sticky top-0 dark:bg-neutral-800",
                                    "placeholder": "Select Category",
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"><span class=\"me-2\" data-icon></span><span class=\"text-gray-800 dark:text-neutral-200 \" data-title></span></button>",
                                    "toggleClasses": "hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 relative shadow text-gray-700 py-3 ps-4 pe-9 flex gap-x-2 text-nowrap w-full cursor-pointer bg-white border border-gray-200 rounded-lg text-start text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-neutral-600",
                                    "dropdownClasses": "mt-2 max-h-72 pb-1 px-1 space-y-0.5 z-20 w-full bg-white border border-gray-200 rounded-lg text-sm overflow-hidden overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500 dark:bg-neutral-800 dark:border-neutral-700",
                                    "optionClasses": "py-2 px-4 w-full text-sm text-slate-600 cursor-pointer hover:bg-gray-100 rounded-lg focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:text-neutral-200 dark:focus:bg-neutral-700",
                                    "optionTemplate": "<div><div class=\"flex items-center\"><div class=\"me-2\" data-icon></div><div class=\"text-gray-800 dark:text-neutral-200 \" data-title></div></div></div>",
                                    "extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
                                }' class="hidden">
                                    <option value="">Choose</option>
                                    @forelse ($this->categories as $category)
                                    <option value="{{ $category->id }}"> {{ $category->name }}</option>
                                    @empty
                                    <option value=""> No records found. </option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-2">
                            <label for="subject"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Subject
                            </label>
                            <span
                                class="inline-flex items-center gap-x-1.5 py-1.5 rounded-full font-medium text-red-500">*</span>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10">
                            <p class="mb-2 text-sm text-gray-500 dark:text-neutral-500" id="hs-textarea-helper-text">
                                {{ $this->subject_placeholder }}</p>
                            <textarea wire:model='subject' id="subject" name="subject" placeholder="Minimum of 8 charaters"
                                class="py-2 px-3 block w-full border-gray-200 shadow text-sm rounded-lg text-slate-600 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                rows="5"></textarea>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-2">
                            <label for="af-account-bio"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Is Bundle?
                            </label>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10">
                            <div class="flex gap-x-6">
                                <div class="flex">
                                    <input wire:model='is_bundle' id="is_bundle" name="is_bundle" type="checkbox"
                                        class="shrink-0 size-5 mt-2 border-gray-200 rounded text-emerald-600 focus:ring-emerald-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-emerald-500 dark:checked:border-emerald-500 dark:focus:ring-offset-gray-800">
                                    <label for="is_bundle"
                                        class="text-sm text-gray-500 ms-3 dark:text-neutral-400"></label>
                                </div>
                            </div>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-2">
                            <label for="user_id"
                                class="inline-block text-sm text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                                Encoded By
                            </label>
                        </div>
                        <!-- End Col -->

                        <div class="sm:col-span-10">
                            <div class="max-w-sm space-y-3">
                                <input wire:model="user['id']" type="text"
                                    class="py-3 px-4 block w-full border-gray-200 dark:border-neutral-700 bg-slate-100 dark:bg-neutral-800 text-slate-500 dark:text-neutral-400 shadow rounded-lg text-sm disabled:opacity-50 disabled:pointer-events-none"
                                    value="{{ $this->completeName() }}" readonly="">
                            </div>
                        </div>
                        <!-- End Col -->
                    </div>
                    <!-- End Grid -->

                    <div class="mt-5 flex justify-end gap-x-2">
                        <button type="button"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                            Cancel
                        </button>
                        <button wire:click.prevent="create" wire:loading.class='opacity-50' type="submit"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700 disabled:opacity-50 disabled:pointer-events-none">
                            Save document
                        </button>
                    </div>

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
                </form>
            </div>
            <!-- End Card -->
        </div>
        <!-- End Card Section -->
    </div>
</div>
