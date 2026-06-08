<div class="w-full lg:ps-64">
    <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
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
                    Inbox
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                My Purchase Requests
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        {{-- Purchase Request Table --}}
        <div class="max-w-full px-2 py-5 sm:px-6 lg:px-2 lg:py-5 mx-auto">
            <!-- Card -->
            <div class="flex flex-col">
                <div class="-m-1.5 overflow-x-auto">
                    <div class="p-1.5 min-w-full inline-block align-middle">
                        <div
                            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                            <!-- Header -->
                            <div
                                class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                                <div>
                                    <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                                        My Purchase Requests
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                                        View all of your created purchase requests, track and more.
                                    </p>
                                </div>

                                <div>
                                    <div class="inline-flex gap-x-2">
                                        <div class="sm:col-span-1">
                                            <label for="search" class="sr-only">Search</label>
                                            <div class="relative">
                                                <input wire:model.blur="search" type="text" id="search" name="search"
                                                    class="py-3 px-3 ps-11 block w-full border-gray-200 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
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
                                        <div class="hs-dropdown [--placement:bottom-right] relative inline-block">
                                            <button id="hs-as-table-table-filter-dropdown" type="button"
                                                class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                                aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                                <svg class="shrink-0 size-3.5 text-gray-800 dark:text-neutral-200"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18" />
                                                    <path d="M7 12h10" />
                                                    <path d="M10 18h4" />
                                                </svg>
                                                Status
                                                <span
                                                    class="inline-flex items-center gap-1.5 py-0.5 px-1.5 rounded-full text-xs font-medium border border-gray-300 text-gray-800 dark:border-neutral-700 dark:text-neutral-300">
                                                    {{ count($selected_filter) }}
                                                </span>
                                            </button>
                                            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden divide-y divide-gray-200 min-w-48 z-20 bg-white shadow-md rounded-lg mt-2 dark:divide-neutral-700 dark:bg-neutral-800 dark:border dark:border-neutral-700"
                                                role="menu" aria-orientation="vertical"
                                                aria-labelledby="hs-as-table-table-filter-dropdown">
                                                <div class="divide-y divide-gray-200 dark:divide-neutral-700">
                                                    <label wire:key='status-1' for="filter-dropdown-statuses"
                                                        class="flex py-2.5 px-3">
                                                        <input type="checkbox"
                                                            class="shrink-0 mt-0.5 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                                            wire:model.blur='selected_filter' value="Created">
                                                        <span class="ms-3 text-sm text-gray-800 dark:text-neutral-200">
                                                            Created </span>
                                                    </label>
                                                    <label wire:key='status-2' for="filter-dropdown-statuses"
                                                        class="flex py-2.5 px-3">
                                                        <input type="checkbox"
                                                            class="shrink-0 mt-0.5 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                                            wire:model.blur='selected_filter' value="For Receiving">
                                                        <span class="ms-3 text-sm text-gray-800 dark:text-neutral-200">
                                                            For Receiving </span>
                                                    </label>
                                                    <label wire:key='status-3' for="filter-dropdown-statuses"
                                                        class="flex py-2.5 px-3">
                                                        <input type="checkbox"
                                                            class="shrink-0 mt-0.5 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                                            wire:model.blur='selected_filter' value="On Process">
                                                        <span class="ms-3 text-sm text-gray-800 dark:text-neutral-200">
                                                            On Process </span>
                                                    </label>
                                                    <label wire:key='status-4' for="filter-dropdown-statuses"
                                                        class="flex py-2.5 px-3">
                                                        <input type="checkbox"
                                                            class="shrink-0 mt-0.5 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                                            wire:model.blur='selected_filter' value="Closed">
                                                        <span class="ms-3 text-sm text-gray-800 dark:text-neutral-200">
                                                            Closed </span>
                                                    </label>
                                                    <label wire:key='status-5' for="filter-dropdown-statuses"
                                                        class="flex py-2.5 px-3">
                                                        <input type="checkbox"
                                                            class="shrink-0 mt-0.5 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                                            wire:model.blur='selected_filter' value="Returned">
                                                        <span class="ms-3 text-sm text-gray-800 dark:text-neutral-200">
                                                            Returned </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="inline-flex rounded-lg shadow-sm gap-x-2">
                                            <div class="hs-tooltip inline-block">
                                                <button type="button" {{ $this->canForwardSelected() ? '' :
                                                    'disabled'
                                                    }}
                                                    aria-haspopup="dialog" aria-expanded="false"
                                                    aria-controls="document-forward-modal"
                                                    data-hs-overlay="#document-forward-modal"
                                                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm
                                                    font-medium
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
                                        </div>

                                        <div class="inline-flex rounded-lg shadow-sm gap-x-2">
                                            <div class="hs-tooltip inline-block">
                                                <button type="button" {{ $this->canGenerateSelected() ? '' : 'disabled'
                                                    }}
                                                    aria-haspopup="dialog" aria-expanded="false"
                                                    aria-controls="generate-logbook-modal"
                                                    data-hs-overlay="#generate-logbook-modal"
                                                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium
                                                    rounded-lg border border-transparent bg-sky-600 text-white
                                                    hover:bg-sky-700 focus:outline-none focus:bg-sky-700
                                                    disabled:opacity-50 disabled:pointer-events-none">
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
                                                    <span
                                                        class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                                        role="tooltip">
                                                        Generate Electronic Logbook
                                                    </span>
                                                </button>
                                            </div>
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
                                                Destination
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
                                                Created At
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
                                                Encoded By
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
                                                            {{ in_array($document->status, ['Created', 'For Receiving']) ? '' : 'disabled'}}
                                                            value="{{ $document->id }}"
                                                            class="border-gray-200 rounded text-blue-600 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                                        <label for="documents_checkbox" class="sr-only">Checkbox</label>
                                                    </div>
                                                </div>
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="size-px whitespace-nowrap">
                                            <div class="py-2 flex flex-row gap-x-2">
                                                <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 dark:focus:text-neutral-300"
                                                    href="{{ route('document.view', $document->control_no) }}">
                                                    <div class="hs-tooltip inline-block">
                                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-eye">
                                                            <path
                                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                                            <circle cx="12" cy="12" r="3" />
                                                        </svg>
                                                        <span
                                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                                            role="tooltip">
                                                            View Document
                                                        </span>
                                                    </div>
                                                </a>
                                                @if(in_array($document->status, ['Forwarded', 'On Process', 'For Receiving']))
                                                <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 dark:focus:text-neutral-300"
                                                    href="{{ route('print.transmittal.form', $document->control_no) }}" target="_blank">
                                                    <div class="hs-tooltip inline-block">
                                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer-icon lucide-printer">
                                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                                            <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
                                                            <rect x="6" y="14" width="12" height="8" rx="1" />
                                                        </svg>
                                                        <span
                                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                                            role="tooltip">
                                                            Print Transmittal Form
                                                        </span>
                                                    </div>
                                                </a>
                                                @endif
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
                                                <div class="px-6 flex gap-x-2">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 py-1 px-2 mt-2 rounded-lg text-xs font-medium {{ $this->colorIndicator($document->status) }} text-gray-800">
                                                        {!! $this->iconIndicator($document->status) !!}
                                                        {{ Str::title($document->status ) }}
                                                    </span>
                                                    @if($document->status === "Closed")
                                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 mt-2 rounded-lg text-xs font-medium bg-gray-100 text-gray-800">
                                                        TAT: {{ $document->turnaroundtime ?? 0}} day/s
                                                    </span>
                                                    @endif
                                                </div>
                                            </span>
                                        </td>

                                        <td class="size-px whitespace-nowrap">
                                            <a class="block relative z-10" href="#">
                                                <div class="px-6 py-2 flex -space-x-2 text-xs">
                                                    {{ $this->filterOffice($document->assigned_to) }}
                                                </div>
                                            </a>
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
                                                    Carbon\Carbon::parse($document->created_at)->format('D, M d, Y')
                                                    }}
                                                </div>
                                                <div class="px-6 flex gap-x-1 text-sm">
                                                    {{
                                                    Carbon\Carbon::parse($document->created_at)->format('h:i:s A')
                                                    }}
                                                </div>
                                            </span>
                                        </td>
                                        <td class="size-px whitespace-nowrap">
                                            <a class="block relative z-10" href="#">
                                                <div class="px-6 py-2 flex -space-x-2 text-xs">
                                                    {{ $this->filterUser($document->user_id) }}
                                                </div>
                                            </a>
                                        </td>

                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="text-center py-5 font-bold text-lg" colspan="6">No records found!
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
            <!-- End Card -->
        </div>
    </div>

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

    {{-- Modal --}}
    @include('components.modals.inbox-modals')
    {{-- End of Modal --}}
</div>