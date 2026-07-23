<!-- Content -->
<div class="w-full lg:ps-64">
    <div class="p-4 sm:p-6 lg:pt-1.5 space-y-4 sm:space-y-6">
        <ol class="flex items-center whitespace-nowrap">
            <li class="inline-flex items-center">
                <a class="flex items-center text-sm text-gray-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 dark:text-neutral-500 dark:hover:text-blue-500 dark:focus:text-blue-500"
                    href="#">
                    Home
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                Dashboard
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        <!-- Card Section -->
        <div class="max-w-[85rem] px-4 py-2 sm:px-6 lg:px-8 lg:py-2 mx-auto">
            <div class="flex flex-row items-center justify-end gap-1">
                <div class="pb-4 flex flex-wrap gap-2 items-center">
                    <div class="sm:col-span-2">
                        <label for="filterDate"
                            class="inline-block text-gray-700 mt-2.5 font-semibold dark:text-neutral-200">
                            Filter by Date:
                        </label>
                    </div>
                    <div class="min-w-[130px]">
                        <label for="startDate" class="sr-only">Start Date</label>
                        <div class="relative">
                            <input type="date" wire:model.live.debounce.2500ms="startDate" name='startDate'
                                class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-neutral-800 dark:border-neutral-600 dark:placeholder-neutral-400 dark:text-neutral-200 dark:[color-scheme:dark] dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Select date">
                        </div>
                    </div>
                    <div class="min-w-[130px]">
                        <label for="EndDate" class="sr-only">End Date</label>
                        <div class="relative">
                            <input type="date" wire:model.live.debounce.2500ms="endDate" name="endDate"
                                class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-neutral-800 dark:border-neutral-600 dark:placeholder-neutral-400 dark:text-neutral-200 dark:[color-scheme:dark] dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Select date">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Header Grid -->
            <div class="mb-4 flex justify-between items-center border-b border-gray-200 dark:border-neutral-700">
                <div>
                    <h3 class="py-2 text-xl font-semibold text-emerald-600 dark:text-neutral-200">Status
                        Disaggregation</h3>
                </div>

            </div>
            <!-- End Header Grid -->

            <!-- Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div
                            class="shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
                            <svg class="shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-file-input">
                                <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                <path d="M2 15h10" />
                                <path d="m9 18 3-3-3-3" />
                            </svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-amber-500 dark:text-amber-500">
                                    For Receiving
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                            role="tooltip">
                                            The number of documents for receiving and returned.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                    {{ number_format($this->incomings) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div
                            class="shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
                            <svg class="shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M5 22h14" />
                                <path d="M5 2h14" />
                                <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" />
                                <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />
                            </svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-red-500 dark:text-red-500">
                                    Pending
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                            role="tooltip">
                                            The number of documents for processing.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl font-medium text-gray-800 dark:text-neutral-200">
                                    {{ number_format($this->pendings) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div
                            class="shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
                            <svg class="shrink-0 size-5 text-gray-600 dark:text-neutral-400"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-file-output">
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                <path d="M4 7V4a2 2 0 0 1 2-2 2 2 0 0 0-2 2" />
                                <path d="M4.063 20.999a2 2 0 0 0 2 1L18 22a2 2 0 0 0 2-2V7l-5-5H6" />
                                <path d="m5 11-3 3" />
                                <path d="m5 17-3-3h10" />
                            </svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-emerald-500 dark:text-emerald-500">
                                    Processed
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                            role="tooltip">
                                            The number of documents processed (Forwarded & Closed) by your office.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                    {{ number_format($this->processed) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div
                            class="shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
                            <svg class="shrink-0 size-5 text-gray-500 dark:text-neutral-500"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-book-copy">
                                <path d="M2 16V4a2 2 0 0 1 2-2h11" />
                                <path
                                    d="M22 18H11a2 2 0 1 0 0 4h10.5a.5.5 0 0 0 .5-.5v-15a.5.5 0 0 0-.5-.5H11a2 2 0 0 0-2 2v12" />
                                <path d="M5 14H4a2 2 0 1 0 0 4h1" />
                            </svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-sky-500 dark:text-sky-500">
                                    Acted Upon
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                            role="tooltip">
                                            The percent of documents acted upon by your office.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl font-medium text-gray-800 dark:text-neutral-200">
                                    {{ number_format($this->percentage, 2) }} %
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->
            </div>
            <!-- End Grid -->
        </div>
        <!-- End Card Section -->

        <!-- Card Section -->
        <div class="max-w-[85rem] px-4 py-2 sm:px-6 lg:px-8 lg:py-2 mx-auto">
            <!-- Header Grid -->
            <div class="mb-4 flex justify-between items-center border-b border-gray-200 dark:border-neutral-700">
                <div>
                    <h3 class="py-2 text-xl font-semibold text-emerald-600 dark:text-neutral-200">Documents
                        Disaggregation</h3>
                </div>

            </div>
            <!-- End Header Grid -->

            <!-- Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div
                            class="shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
                            <svg class="shrink-0 size-5 text-gray-600 dark:text-neutral-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-files">
                                <path d="M20 7h-3a2 2 0 0 1-2-2V2" />
                                <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z" />
                                <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8" />
                            </svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-amber-500 dark:text-amber-500">
                                    Documents
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                            role="tooltip">
                                            Created documents except Purchase Requests and Payments.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                    {{ number_format($this->documents) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div
                            class="shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
                            <svg class="shrink-0 size-5 text-gray-600 dark:text-neutral-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                <path d="M10 9H8" />
                                <path d="M16 13H8" />
                                <path d="M16 17H8" />
                            </svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-red-500 dark:text-red-500">
                                    Purchase Requests
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                            role="tooltip">
                                            The number of Purchase Requests created.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl font-medium text-gray-800 dark:text-neutral-200">
                                    {{ number_format($this->purchaseOrders) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div
                            class="shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
                            <svg class="shrink-0 size-5 text-gray-600 dark:text-neutral-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scroll-text">
                                <path d="M15 12h-5" />
                                <path d="M15 8h-5" />
                                <path d="M19 17V5a2 2 0 0 0-2-2H4" />
                                <path
                                    d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3" />
                            </svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-emerald-500 dark:text-emerald-500">
                                    Payments
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                            role="tooltip">
                                            The number of Payments created.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                    {{ number_format($this->payments) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5 flex gap-x-4">
                        <div
                            class="shrink-0 flex justify-center items-center size-[46px] bg-gray-100 rounded-lg dark:bg-neutral-800">
                            <svg class="shrink-0 size-5 text-gray-500 dark:text-neutral-500"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-book-copy">
                                <path d="M2 16V4a2 2 0 0 1 2-2h11" />
                                <path
                                    d="M22 18H11a2 2 0 1 0 0 4h10.5a.5.5 0 0 0 .5-.5v-15a.5.5 0 0 0-.5-.5H11a2 2 0 0 0-2 2v12" />
                                <path d="M5 14H4a2 2 0 1 0 0 4h1" />
                            </svg>
                        </div>

                        <div class="grow">
                            <div class="flex items-center gap-x-2">
                                <p class="text-xs uppercase tracking-wide text-sky-500 dark:text-sky-500">
                                    Bundles
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                        <span
                                            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                            role="tooltip">
                                            The number of Bundles created.
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2">
                                <h3 class="text-xl font-medium text-gray-800 dark:text-neutral-200">
                                    {{ number_format($this->bundles) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->
            </div>
            <!-- End Grid -->
        </div>
        <!-- End Card Section -->
    </div>
</div>
<!-- End Content -->