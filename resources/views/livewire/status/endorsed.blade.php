<div class="w-full lg:ps-64">
    <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
        {{-- Breadcrumb --}}
        <ol class="flex items-center whitespace-nowrap">
            <li class="inline-flex items-center">
                <a class="flex items-center text-sm text-gray-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 dark:text-neutral-500 dark:hover:text-blue-500 dark:focus:text-blue-500"
                    href="/">
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
                    Status
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                Endorsed
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        {{-- Endorsed Table --}}
        <div class="max-w-full px-2 py-5 sm:px-6 lg:px-2 lg:py-5 mx-auto">
            <!-- Card -->
            <div class="flex flex-col">
                <div class="-m-1.5 overflow-x-auto">
                    <div class="p-1.5 min-w-full inline-block align-middle">
                        <div
                            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                            <!-- Header -->
                            <div
                                class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-gray-200 dark:border-neutral-700">
                                <div>
                                    <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                                        Endorsed Documents
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                                        View all the pending and for action documents endorsed to you.
                                    </p>
                                </div>

                                <div>
                                    <div class="inline-flex gap-x-2">
                                        <div class="sm:col-span-1">
                                            <label for="search" class="sr-only">Search</label>
                                            <div class="relative">
                                                <input wire:model.blur="search" type="text" id="search" name="search"
                                                    class="py-3 px-3 ps-11 block w-full border-gray-200 rounded-lg shadow text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                                    placeholder="Search">
                                                <div
                                                    class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4">
                                                    <svg class="size-4 text-gray-400 dark:text-neutral-500"
                                                        xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" viewBox="0 0 16 16">
                                                        <path
                                                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-1">
                                            <label for="startDate" class="sr-only">Start Date</label>
                                            <div class="relative">
                                                <input type="date" wire:model.live.debounce.2500ms="startDate"
                                                    name='startDate'
                                                    class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Select date">
                                            </div>
                                        </div>
                                        <div class="sm:col-span-1">
                                            <label for="EndDate" class="sr-only">End Date</label>
                                            <div class="relative">
                                                <input type="date" wire:model.live.debounce.2500ms="endDate"
                                                    name="endDate"
                                                    class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Select date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hs-dropdown [--placement:bottom-right] relative inline-block">
                                        <button id="hs-as-table-table-filter-dropdown" type="button"
                                            class="py-3 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                            aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                            <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18" />
                                                <path d="M7 12h10" />
                                                <path d="M10 18h4" />
                                            </svg>
                                        </button>
                                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden divide-y divide-gray-200 min-w-48 z-20 bg-white shadow-md rounded-lg mt-2 dark:divide-neutral-700 dark:bg-neutral-800 dark:border dark:border-neutral-700"
                                            role="menu" aria-orientation="vertical"
                                            aria-labelledby="hs-as-table-table-filter-dropdown">
                                            <div class="divide-y divide-gray-200 dark:divide-neutral-700">
                                                <ul class="max-w-sm flex flex-col">
                                                    <li
                                                        class="inline-flex items-center gap-x-2 py-3 px-4 text-sm font-medium bg-white border text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg">
                                                        <div class="relative flex items-start w-full">
                                                            <div class="flex items-center h-5">
                                                                <input wire:key='type-1' id="hs-list-group-item-radio-1"
                                                                    wire:click='documentTypeFilter("")'
                                                                    name="hs-list-group-item-radio" type="radio"
                                                                    class="border-gray-200 rounded-full disabled:opacity-50"
                                                                    checked="">
                                                            </div>
                                                            <label for="hs-list-group-item-radio-1"
                                                                class="ms-3 block w-full text-sm text-gray-600">
                                                                All Documents
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li
                                                        class="inline-flex items-center gap-x-2 py-3 px-4 text-sm font-medium bg-white border text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg">
                                                        <div class="relative flex items-start w-full">
                                                            <div class="flex items-center h-5">
                                                                <input wire:key='type-2' id="hs-list-group-item-radio-2"
                                                                    wire:click='documentTypeFilter("Purchase")'
                                                                    name="hs-list-group-item-radio" type="radio"
                                                                    class="border-gray-200 rounded-full disabled:opacity-50">
                                                            </div>
                                                            <label for="hs-list-group-item-radio-2"
                                                                class="ms-3 block w-full text-sm text-gray-600">
                                                                Purchase Requests
                                                            </label>
                                                        </div>
                                                    </li>

                                                    <li
                                                        class="inline-flex items-center gap-x-2 py-3 px-4 text-sm font-medium bg-white border text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg">
                                                        <div class="relative flex items-start w-full">
                                                            <div class="flex items-center h-5">
                                                                <input wire:key='type-3' id="hs-list-group-item-radio-3"
                                                                    wire:click='documentTypeFilter("Payment")'
                                                                    name="hs-list-group-item-radio" type="radio"
                                                                    class="border-gray-200 rounded-full disabled:opacity-50">
                                                            </div>
                                                            <label for="hs-list-group-item-radio-3"
                                                                class="ms-3 block w-full text-sm text-gray-600">
                                                                Payments
                                                            </label>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="inline-flex rounded-lg shadow-sm gap-x-1">
                                        <div class="hs-tooltip inline-block">
                                            <button type="button" {{ count($this->selected_item) > 0 ? '' : 'disabled'
                                                }}
                                                aria-haspopup="dialog" aria-expanded="false"
                                                aria-controls="document-forward-modal"
                                                data-hs-overlay="#document-forward-modal"
                                                class="py-3 px-3 inline-flex items-center gap-x-2 text-sm font-medium
                                                rounded-lg border border-transparent bg-emerald-600 text-white
                                                hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700
                                                disabled:opacity-50 disabled:pointer-events-none">
                                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="lucide lucide-send">
                                                    <path
                                                        d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z" />
                                                    <path d="m21.854 2.147-10.94 10.939" />
                                                </svg>
                                                <span
                                                    class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                                    role="tooltip">
                                                    Forward selected documents
                                                </span>
                                            </button>
                                        </div>

                                        <div class="hs-tooltip inline-block">
                                            <button type="button" wire:click='modalCloseDocument' {{
                                                count($this->selected_item) > 0 ? '' : 'disabled' }}
                                                class="py-3 px-3 inline-flex items-center gap-x-2 text-sm font-medium
                                                rounded-lg border border-transparent bg-red-600 text-white
                                                hover:bg-red-700 focus:outline-none focus:bg-red-700
                                                disabled:opacity-50 disabled:pointer-events-none"
                                                aria-haspopup="dialog" aria-expanded="false"
                                                aria-controls="hs-modal-input-text"
                                                data-hs-overlay="#hs-modal-input-text">
                                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="lucide lucide-folder-down">
                                                    <path
                                                        d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z" />
                                                    <path d="M12 10v6" />
                                                    <path d="m15 13-3 3-3-3" />
                                                </svg>
                                                <span
                                                    class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                                    role="tooltip">
                                                    Close selected documents
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Header -->

                            <!-- Table -->
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-start">
                                            <div class="flex items-center h-5">
                                                <input id="hs-table-checkbox-all" type="checkbox"
                                                    wire:model.lazy='selectAll'
                                                    class="border-gray-200 rounded text-emerald-600 focus:ring-emerald-500 dark:bg-neutral-700 dark:border-neutral-500 dark:checked:bg-emerald-500 dark:checked:border-emerald-500 dark:focus:ring-offset-gray-800">
                                                <label for="hs-table-checkbox-all" class="sr-only">Checkbox</label>
                                            </div>
                                        </th>

                                        <th scope="col" class="px-6 py-3 text-end"></th>

                                        <th scope="col" class="px-6 py-3 text-start">
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-gray-800 hover:text-gray-500 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Document Control No
                                                <svg class="shrink-0 size-3.5 text-gray-800 dark:text-neutral-200"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m7 15 5 5 5-5" />
                                                    <path d="m7 9 5-5 5 5" />
                                                </svg>
                                            </a>
                                        </th>

                                        <th scope="col" class="px-6 py-3 text-start">
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-gray-800 hover:text-gray-500 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                From
                                                <svg class="shrink-0 size-3.5 text-gray-800 dark:text-neutral-200"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m7 15 5 5 5-5" />
                                                    <path d="m7 9 5-5 5 5" />
                                                </svg>
                                            </a>
                                        </th>

                                        <th scope="col" class="px-6 py-3 text-start">
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-gray-800 hover:text-gray-500 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Subject
                                                <svg class="shrink-0 size-3.5 text-gray-800 dark:text-neutral-200"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m7 15 5 5 5-5" />
                                                    <path d="m7 9 5-5 5 5" />
                                                </svg>
                                            </a>
                                        </th>

                                        <th scope="col" class="px-6 py-3 text-start">
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-gray-800 hover:text-gray-500 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Transferred At
                                                <svg class="shrink-0 size-3.5 text-gray-800 dark:text-neutral-200"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m7 15 5 5 5-5" />
                                                    <path d="m7 9 5-5 5 5" />
                                                </svg>
                                            </a>
                                        </th>

                                        <th scope="col" class="px-6 py-3 text-start">
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-gray-800 hover:text-gray-500 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Length of Stay
                                                <svg class="shrink-0 size-3.5 text-gray-800 dark:text-neutral-200"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m7 15 5 5 5-5" />
                                                    <path d="m7 9 5-5 5 5" />
                                                </svg>
                                            </a>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-start">
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-gray-800 hover:text-gray-500 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Endorsed To
                                                <svg class="shrink-0 size-3.5 text-gray-800 dark:text-neutral-200"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m7 15 5 5 5-5" />
                                                    <path d="m7 9 5-5 5 5" />
                                                </svg>
                                            </a>
                                        </th>

                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @forelse ($documents as $key => $document)
                                    <tr wire:key="document-{{ $document->id }}"
                                        class="bg-white hover:bg-gray-50 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                                        <td class="size-px whitespace-nowrap">
                                            <span class="block">
                                                <div class="px-6">
                                                    <div class="flex items-center h-5">
                                                        <input wire:model.lazy='selected_item' type="checkbox"
                                                            value="{{ $document->id }}"
                                                            class="border-gray-200 rounded text-blue-600 focus:ring-blue-500
                                                        dark:bg-neutral-800 dark:border-neutral-700
                                                        dark:checked:bg-blue-500 dark:checked:border-blue-500
                                                        dark:focus:ring-offset-gray-800">
                                                        <label for="incoming_checkbox" class="sr-only">Checkbox</label>
                                                    </div>
                                                </div>
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="size-px whitespace-nowrap">
                                            <div class="py-2">
                                                <a class="flex items-center gap-x-2 py-2 px-2 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 dark:focus:text-neutral-300"
                                                    href="{{ '/document/pending/'. $document->control_no }}">
                                                    <svg class="shrink-0 size-4"
                                                        xmlns="http://www.w3.org/2000/svg" width="24"
                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round"
                                                        class="lucide lucide-eye">
                                                        <path
                                                            d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                                        <circle cx="12" cy="12" r="3" />
                                                    </svg>
                                                    View
                                                </a>
                                            </div>
                                        </td>
                                        {{-- End of Actions --}}

                                        <td class="size-px whitespace-nowrap">
                                            <span class="block">
                                                <div class="px-6">
                                                    <div class="block text-sm text-emerald-900 decoration-2">
                                                        {{ $document->control_no }}
                                                    </div>
                                                </div>
                                                <div class="px-6">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 py-1 px-2 mt-2 rounded-lg text-xs font-medium bg-yellow-100 text-gray-800">
                                                        <svg class="shrink-0 size-3" xmlns="http://www.w3.org/2000/svg"
                                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-refresh-ccw">
                                                            <path
                                                                d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                                            <path d="M3 3v5h5" />
                                                            <path
                                                                d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                                                            <path d="M16 16h5v5" />
                                                        </svg> {{ Str::title($document->status ) }}
                                                    </span>
                                                </div>
                                            </span>
                                        </td>

                                        <td class="size-px whitespace-nowrap">
                                            <span class="block relative z-10">
                                                <div class="px-6 flex gap-x-1 text-sm">
                                                    {{
                                                        $this->filterOffice($document->logs->sortByDesc('id')->where('office_id', '!=', $this->office)->first()->office_id)
                                                    }}
                                                </div>
                                            </span>
                                        </td>

                                        <td class="h-px w-80 min-w-80 align-top">
                                            <span class="block p-6">
                                                <span
                                                    class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{
                                                    $document->category->name }}</span>
                                                <span class="block text-sm text-gray-500 dark:text-neutral-500">{{
                                                    $document->subject }}</span>
                                                <div class="flex gap-x-1 my-2">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium {{ $document->source == 'internal' ? 'bg-emerald-100 text-gray-800' : 'bg-red-100 text-gray-800'}} ">
                                                        {{ Str::title($document->source) }}
                                                    </span>
                                                    @if($document->citizen_charter_id)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{
                                                        \App\Models\CitizenCharter::find($document->citizen_charter_id)->name
                                                        }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </span>
                                        </td>
                                        <td class="size-px whitespace-nowrap">
                                            <span class="block relative z-10">
                                                <div class="px-6 flex gap-x-1 text-sm">
                                                    {{
                                                    Carbon\Carbon::parse($document->updated_at)->format('D, M d, Y')
                                                    }}
                                                </div>
                                                <div class="px-6 flex gap-x-1 text-sm">
                                                    {{
                                                    Carbon\Carbon::parse($document->updated_at)->format('h:i:s A')
                                                    }}
                                                </div>
                                            </span>
                                        </td>

                                        <td class="size-px whitespace-nowrap">
                                            <span class="block relative z-10">
                                                <div class="px-6 flex gap-x-1 text-sm">
                                                    {{
                                                    Carbon\Carbon::now()->diffForHumans($document->updated_at)
                                                    }}
                                                </div>
                                            </span>
                                        </td>

                                        <td class="size-px whitespace-nowrap">
                                            <span class="block relative z-10">
                                                <div class="px-6 flex gap-x-1 text-sm">
                                                    {{
                                                    $document->endorsed_to ? $this->filterUser($document->endorsed_to) :
                                                    ''
                                                    }}
                                                </div>
                                            </span>
                                        </td>

                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="text-center py-5 font-bold text-lg" colspan="7">No records found!
                                        </td>
                                    </tr>
                                    @endforelse

                                </tbody>

                            </table>
                            <!-- End Table -->
                            <!-- Footer -->
                            <div
                                class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-t border-gray-200 dark:border-neutral-700">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                                        <span class="font-semibold text-gray-800 dark:text-neutral-200">{{
                                            $documents->count() }}</span> results per page
                                    </p>
                                </div>

                                <div>
                                    <div class="flex flex-row mt-2">
                                        {{ $documents->links() }}
                                    </div>
                                </div>
                            </div>
                            <!-- End Footer -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- End of Pending Table --}}

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

    {{-- Modal --}}
    @include('components.modals.status-pending-modal')
    {{-- End of Modal --}}
</div>