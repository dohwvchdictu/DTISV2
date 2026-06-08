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
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center">
                <a class="flex items-center text-sm text-gray-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 dark:text-neutral-500 dark:hover:text-blue-500 dark:focus:text-blue-500"
                    href="#">
                    Report
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                Status of Documents
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        {{-- Filter Section --}}
        <!-- Card Section -->
        <div class="max-w-full px-4 py-2 sm:px-6 lg:px-8 lg:py-2 mx-auto">
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
                                <p class="text-xs uppercase tracking-wide text-amber-500 dark:text-neutral-500">
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
                                    {{ number_format(
                                        $incoming = \App\Models\Document::whereIn('status', ['For Receiving', 'Returned'])->whereBetween('created_at', [
                                                \Carbon\Carbon::parse($this->startDate)->addDay(1),
                                                \Carbon\Carbon::parse($this->endDate)->addDay(1),
                                            ])->count(),
                                    ) }}
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
                                <p class="text-xs uppercase tracking-wide text-red-500 dark:text-neutral-500">
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
                                    {{ number_format(
                                        $pending = \App\Models\Document::whereBetween('created_at', [
                                            \Carbon\Carbon::parse($this->startDate)->addDay(1),
                                            \Carbon\Carbon::parse($this->endDate)->addDay(1),
                                        ])->whereIn('status', ['On Process'])->count(),
                                    ) }}
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
                                <p class="text-xs uppercase tracking-wide text-emerald-500 dark:text-neutral-500">
                                    Processed
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                    {{ number_format(
                                        $processed = \App\Models\Log::whereIn('action_id', [3, 5])->whereBetween('created_at', [
                                                \Carbon\Carbon::parse($this->startDate)->addDay(1),
                                                \Carbon\Carbon::parse($this->endDate)->addDay(1),
                                            ])->count(),
                                    ) }}
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
                                <p class="text-xs uppercase tracking-wide text-sky-500 dark:text-neutral-500">
                                    Acted Upon
                                </p>
                                <div class="hs-tooltip">
                                    <div class="hs-tooltip-toggle">
                                        <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                    {{ number_format($this->documentsPercentage($incoming, $pending, $processed), 2) }}
                                    %
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

        {{-- Status by Documents Table --}}
        <div class="max-w-full px-2 py-5 sm:px-6 lg:px-2 lg:py-5 mx-auto">
            <!-- Card -->
            <div class="flex flex-col">
                <div class="-m-1.5 overflow-x-auto">
                    <div class="p-1.5 min-w-full inline-block align-middle">
                        <div
                            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                            {{-- Header --}}
                            <div
                                class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-gray-200 dark:border-neutral-700">
                                <div>
                                    <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                                        Document Status by Office
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-neutral-400 mb-2">
                                        Monthly summary of the status of documents per office.
                                    </p>
                                </div>
                                <div>
                                    <div class="inline-flex gap-x-2">
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
                                        <div class="sm:col-span-1">
                                            <a href="{{ route('print.document.status', ['startDate' => $startDate, 'endDate' => $endDate]) }}"
                                                target="_blank"
                                                class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <polyline points="6,9 6,2 18,2 18,9"></polyline>
                                                    <path
                                                        d="M6,18H4a2,2,0,0,1-2-2V11a2,2,0,0,1,2-2H20a2,2,0,0,1,2,2v5a2,2,0,0,1-2,2H18">
                                                    </path>
                                                    <rect x="6" y="14" width="12" height="8"></rect>
                                                </svg>
                                                Print Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- End of Header --}}

                            <!-- Table -->
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-start">
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-gray-800 hover:text-gray-500 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Office
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
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-amber-500 hover:text-amber-400 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Incoming
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
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-red-500 hover:text-red-400 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Pending
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
                                            <a class="group inline-flex items-center gap-x-2 text-xs font-semibold uppercase text-emerald-500 hover:text-emerald-400 focus:outline-none focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                                                href="#">
                                                Processed
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
                                                Percentage
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
                                    @forelse ($offices as $key => $office)
                                        <tr wire:key="office-{{ $office['id'] }}"
                                            class="bg-white hover:bg-gray-50 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                                            <td class="size-px whitespace-nowrap">
                                                <span class="block">
                                                    <div class="px-6 py-4">
                                                        <div class="block font-semibold text-emerald-900 decoration-2">
                                                            {{ $office['officeName'] }}</div>
                                                    </div>
                                                </span>
                                            </td>
                                            <td class="size-px whitespace-nowrap">
                                                <span class="block relative z-10">
                                                    <div class="px-6 text-amber-600 flex gap-x-1">
                                                        {{ number_format(
                                                            $incoming = \App\Models\Document::where('assigned_to', $office['id'])->whereIn('status', ['For Receiving', 'Returned'])->whereBetween('created_at', [
                                                                    \Carbon\Carbon::parse($this->startDate)->addDay(1),
                                                                    \Carbon\Carbon::parse($this->endDate)->addDay(1),
                                                                ])->count(),
                                                        ) }}
                                                    </div>
                                                </span>
                                            </td>
                                            <td class="size-px whitespace-nowrap">
                                                <span class="block relative z-10">
                                                    <div class="px-6 flex text-red-600 gap-x-1">
                                                        {{ number_format(
                                                            $pending = \App\Models\Document::where('assigned_to', $office['id'])->whereBetween('created_at', [
                                                                    \Carbon\Carbon::parse($this->startDate)->addDay(1),
                                                                    \Carbon\Carbon::parse($this->endDate)->addDay(1),
                                                                ])->whereIn('status', ['On Process'])->count(),
                                                        ) }}
                                                    </div>
                                                </span>
                                            </td>
                                            <td class="size-px whitespace-nowrap">
                                                <span class="block relative z-10">
                                                    <div class="px-6 flex text-emerald-600 gap-x-1">
                                                        {{ number_format(
                                                            $processed = \App\Models\Log::where('assigned_to', $office)->whereIn('action_id', [3, 5])->whereBetween('created_at', [
                                                                    \Carbon\Carbon::parse($this->startDate)->addDay(1),
                                                                    \Carbon\Carbon::parse($this->endDate)->addDay(1),
                                                                ])->count(),
                                                        ) }}
                                                    </div>
                                                </span>
                                            </td>
                                            <td class="size-px whitespace-nowrap">
                                                <span class="block relative z-10">
                                                    <div class="px-6 flex gap-x-1">
                                                        {{ number_format($this->documentsPercentage($incoming, $pending, $processed), 2) }}
                                                        %
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center py-5 font-bold text-lg" colspan="6">No records
                                                found!
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>
                            <!-- End Table -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- End of Status by Documents Table --}}
    </div>
</div>
