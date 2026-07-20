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
                Turnaround Time
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        {{-- Filters --}}
        <div class="max-w-full px-2 sm:px-6 lg:px-2 mx-auto">
            <div
                class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 dark:bg-neutral-900 dark:border-neutral-700">
                <div class="flex flex-wrap gap-2 items-center">
                    <div class="w-full md:w-[420px]">
                        <label for="officeFilter" class="sr-only">Unit/Office</label>
                        <select wire:model="officeFilter" name="officeFilter"
                            class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="">All Units/Offices</option>
                            @foreach ($this->offices as $officeOption)
                                <option value="{{ $officeOption['id'] }}">
                                    {{ $officeOption['officeName'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[130px]">
                        <label for="source" class="sr-only">Source</label>
                        <select wire:model="source" name="source"
                            class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="">All Sources</option>
                            <option value="internal">Internal</option>
                            <option value="external">External</option>
                        </select>
                    </div>
                    <div class="min-w-[130px]">
                        <label for="startDate" class="sr-only">Start Date</label>
                        <input type="date" wire:model="startDate" name="startDate"
                            class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Select date">
                    </div>
                    <div class="min-w-[130px]">
                        <label for="endDate" class="sr-only">End Date</label>
                        <input type="date" wire:model="endDate" name="endDate"
                            class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Select date">
                    </div>
                    <div>
                        <button type="button" wire:click="applyFilters"
                            class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                            </svg>
                            <span wire:loading.remove wire:target="applyFilters">Filter</span>
                            <span wire:loading wire:target="applyFilters">Loading...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- End of Filters --}}

        {{-- Overall Stat Cards --}}
        <div class="max-w-full px-2 sm:px-6 lg:px-2 mx-auto">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5">
                        <p class="text-xs uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                            Closed Documents
                        </p>
                        <h3 class="mt-1 text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200 tabular-nums">
                            {{ number_format($totals['closed']) }}
                            <span class="text-sm text-gray-500 dark:text-neutral-400 font-normal">
                                of {{ number_format($totals['total']) }}
                            </span>
                        </h3>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5">
                        <p class="text-xs uppercase tracking-wide text-sky-500">
                            Average Turnaround
                        </p>
                        <h3 class="mt-1 text-xl sm:text-2xl font-medium text-sky-600 tabular-nums">
                            @if ($totals['average'] !== null)
                                {{ number_format($totals['average'], 1) }}
                                <span class="text-sm text-gray-500 dark:text-neutral-400 font-normal">
                                    {{ $totals['average'] == 1 ? 'day' : 'days' }}
                                </span>
                            @else
                                —
                            @endif
                        </h3>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5">
                        <p class="text-xs uppercase tracking-wide text-emerald-500">
                            Fastest
                        </p>
                        <h3 class="mt-1 text-xl sm:text-2xl font-medium text-emerald-600 tabular-nums">
                            @if ($totals['fastest'] !== null)
                                {{ number_format($totals['fastest']) }}
                                <span class="text-sm text-gray-500 dark:text-neutral-400 font-normal">
                                    {{ $totals['fastest'] == 1 ? 'day' : 'days' }}
                                </span>
                            @else
                                —
                            @endif
                        </h3>
                    </div>
                </div>
                <!-- End Card -->

                <!-- Card -->
                <div
                    class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                    <div class="p-4 md:p-5">
                        <p class="text-xs uppercase tracking-wide text-rose-500">
                            Slowest
                        </p>
                        <h3 class="mt-1 text-xl sm:text-2xl font-medium text-rose-600 tabular-nums">
                            @if ($totals['slowest'] !== null)
                                {{ number_format($totals['slowest']) }}
                                <span class="text-sm text-gray-500 dark:text-neutral-400 font-normal">
                                    {{ $totals['slowest'] == 1 ? 'day' : 'days' }}
                                </span>
                            @else
                                —
                            @endif
                        </h3>
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
        {{-- End of Overall Stat Cards --}}

        {{-- Turnaround Time Table --}}
        <div class="max-w-full px-2 sm:px-6 lg:px-2 mx-auto">
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
                                        Turnaround Time Per Document Type
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                                        Working days from creation to closure, weekends excluded. Closed documents only.
                                    </p>
                                </div>
                            </div>
                            {{-- End of Header --}}

                            <!-- Table -->
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-start">
                                            <span
                                                class="text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                                Document Type
                                            </span>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <span
                                                class="text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                                Closed
                                            </span>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <span
                                                class="text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                                Total Docs
                                            </span>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <span
                                                class="text-xs font-semibold uppercase text-sky-500 dark:text-neutral-200">
                                                Avg TAT (Days)
                                            </span>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <span
                                                class="text-xs font-semibold uppercase text-emerald-500 dark:text-neutral-200">
                                                Min
                                            </span>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <span
                                                class="text-xs font-semibold uppercase text-rose-500 dark:text-neutral-200">
                                                Max
                                            </span>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @forelse ($rows as $index => $category)
                                        <tr wire:key="type-{{ $index }}"
                                            class="bg-white hover:bg-gray-50 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                                            <td class="size-px whitespace-nowrap">
                                                <div class="px-6 py-4 font-semibold text-emerald-900 dark:text-neutral-200">
                                                    {{ $category['name'] }}
                                                </div>
                                            </td>
                                            <td class="size-px whitespace-nowrap text-center">
                                                <div class="px-6 py-4 text-gray-800 dark:text-neutral-200 tabular-nums">
                                                    {{ number_format($category['closed']) }}
                                                </div>
                                            </td>
                                            <td class="size-px whitespace-nowrap text-center">
                                                <div class="px-6 py-4 text-gray-600 dark:text-neutral-400 tabular-nums">
                                                    {{ number_format($category['total']) }}
                                                </div>
                                            </td>
                                            <td class="size-px whitespace-nowrap text-center">
                                                <div class="px-6 py-4 font-semibold text-sky-600 tabular-nums">
                                                    {{ $category['avg'] !== null ? number_format($category['avg'], 1) : '—' }}
                                                </div>
                                            </td>
                                            <td class="size-px whitespace-nowrap text-center">
                                                <div class="px-6 py-4 text-emerald-600 tabular-nums">
                                                    {{ $category['min'] !== null ? number_format($category['min']) : '—' }}
                                                </div>
                                            </td>
                                            <td class="size-px whitespace-nowrap text-center">
                                                <div class="px-6 py-4 text-rose-600 tabular-nums">
                                                    {{ $category['max'] !== null ? number_format($category['max']) : '—' }}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center py-5 font-bold text-lg" colspan="6">
                                                No records found!
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                            <!-- End Table -->

                            {{-- Pagination --}}
                            @if ($rows->hasPages())
                                <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                                    {{ $rows->links('livewire.partials.pagination') }}
                                </div>
                            @endif
                            {{-- End of Pagination --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- End of Turnaround Time Table --}}
    </div>
</div>
