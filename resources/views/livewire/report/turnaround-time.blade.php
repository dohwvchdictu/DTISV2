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
                            class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-neutral-800 dark:border-neutral-600 dark:placeholder-neutral-400 dark:text-neutral-200 dark:[color-scheme:dark] dark:focus:ring-blue-500 dark:focus:border-blue-500">
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
                            class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-neutral-800 dark:border-neutral-600 dark:placeholder-neutral-400 dark:text-neutral-200 dark:[color-scheme:dark] dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="">All Sources</option>
                            <option value="internal">Internal</option>
                            <option value="external">External</option>
                        </select>
                    </div>
                    <div class="min-w-[130px]">
                        <label for="startDate" class="sr-only">Start Date</label>
                        <input type="date" wire:model="startDate" name="startDate"
                            class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-neutral-800 dark:border-neutral-600 dark:placeholder-neutral-400 dark:text-neutral-200 dark:[color-scheme:dark] dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Select date">
                    </div>
                    <div class="min-w-[130px]">
                        <label for="endDate" class="sr-only">End Date</label>
                        <input type="date" wire:model="endDate" name="endDate"
                            class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-neutral-800 dark:border-neutral-600 dark:placeholder-neutral-400 dark:text-neutral-200 dark:[color-scheme:dark] dark:focus:ring-blue-500 dark:focus:border-blue-500"
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
            <div class="grid grid-cols-3 gap-4 sm:gap-6">
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
                                        Turnaround Time Per Office
                                    </h2>
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
                                                Office
                                            </span>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <button type="button" wire:click="sortBy('avg')"
                                                class="inline-flex items-center gap-x-1 text-xs font-semibold uppercase text-sky-500 hover:text-sky-600">
                                                Avg Dwell (Days)
                                                @if ($sortColumn === 'avg')
                                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @else
                                                    <span class="text-gray-300 dark:text-neutral-600">↕</span>
                                                @endif
                                            </button>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <button type="button" wire:click="sortBy('min')"
                                                class="inline-flex items-center gap-x-1 text-xs font-semibold uppercase text-emerald-500 hover:text-emerald-600">
                                                Min
                                                @if ($sortColumn === 'min')
                                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @else
                                                    <span class="text-gray-300 dark:text-neutral-600">↕</span>
                                                @endif
                                            </button>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <button type="button" wire:click="sortBy('max')"
                                                class="inline-flex items-center gap-x-1 text-xs font-semibold uppercase text-rose-500 hover:text-rose-600">
                                                Max
                                                @if ($sortColumn === 'max')
                                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @else
                                                    <span class="text-gray-300 dark:text-neutral-600">↕</span>
                                                @endif
                                            </button>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @forelse ($rows as $index => $office)
                                        <tr wire:key="office-{{ $office['office_id'] }}"
                                            class="bg-white hover:bg-gray-50 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                                            <td class="size-px whitespace-nowrap">
                                                <button type="button" wire:click="toggleOffice({{ $office['office_id'] }})"
                                                    class="w-full flex items-center gap-x-2 px-6 py-4 font-semibold text-start text-emerald-900 hover:text-emerald-600 dark:text-neutral-200 dark:hover:text-emerald-400">
                                                    <svg class="shrink-0 size-4 text-gray-400 dark:text-neutral-500 transition-transform duration-200 {{ $expandedOffice === $office['office_id'] ? 'rotate-90' : '' }}"
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="m9 18 6-6-6-6" />
                                                    </svg>
                                                    {{ $office['name'] }}
                                                </button>
                                            </td>
                                            <td class="size-px whitespace-nowrap text-center">
                                                <div class="px-6 py-4 font-semibold text-sky-600 tabular-nums">
                                                    {{ $office['avg'] !== null ? number_format($office['avg'], 1) : '—' }}
                                                </div>
                                            </td>
                                            <td class="size-px whitespace-nowrap text-center">
                                                <div class="px-6 py-4 text-emerald-600 tabular-nums">
                                                    {{ $office['min'] !== null ? number_format($office['min']) : '—' }}
                                                </div>
                                            </td>
                                            <td class="size-px whitespace-nowrap text-center">
                                                <div class="px-6 py-4 text-rose-600 tabular-nums">
                                                    {{ $office['max'] !== null ? number_format($office['max']) : '—' }}
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Expandable per-category breakdown --}}
                                        @if ($expandedOffice === $office['office_id'])
                                            <tr wire:key="detail-{{ $office['office_id'] }}" class="bg-gray-50 dark:bg-neutral-800/60">
                                                <td colspan="4" class="p-0">
                                                    <div class="px-6 py-4" wire:loading.class="opacity-50" wire:target="toggleOffice,docs">
                                                        @if ($detail && $detail['office'] === $office['office_id'])
                                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mb-3 text-sm">
                                                                <span class="font-semibold text-gray-800 dark:text-neutral-200">
                                                                    {{ number_format($detail['completed']) }} completed
                                                                    {{ Str::plural('hop', $detail['completed']) }}
                                                                </span>
                                                                @if ($detail['current'] > 0)
                                                                    <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                                        {{ number_format($detail['current']) }} currently here
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            @if ($detail['rows']->isEmpty())
                                                                <p class="text-sm text-gray-500 dark:text-neutral-400">
                                                                    No completed hops for this office.
                                                                </p>
                                                            @else
                                                                <div class="overflow-x-auto border border-gray-200 rounded-lg dark:border-neutral-700">
                                                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                                                        <thead class="bg-white dark:bg-neutral-900">
                                                                            <tr class="text-xs font-semibold uppercase text-gray-500 dark:text-neutral-400">
                                                                                <th class="px-4 py-2 text-start">Document Type</th>
                                                                                <th class="px-4 py-2 text-center">
                                                                                    <button type="button" wire:click="sortDetailBy('avg')"
                                                                                        class="inline-flex items-center gap-x-1 uppercase text-sky-500 hover:text-sky-600">
                                                                                        Avg Dwell (Days)
                                                                                        @if ($detailSortColumn === 'avg')
                                                                                            <span>{{ $detailSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                                                        @else
                                                                                            <span class="text-gray-300 dark:text-neutral-600">↕</span>
                                                                                        @endif
                                                                                    </button>
                                                                                </th>
                                                                                <th class="px-4 py-2 text-center">
                                                                                    <button type="button" wire:click="sortDetailBy('min')"
                                                                                        class="inline-flex items-center gap-x-1 uppercase text-emerald-500 hover:text-emerald-600">
                                                                                        Min
                                                                                        @if ($detailSortColumn === 'min')
                                                                                            <span>{{ $detailSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                                                        @else
                                                                                            <span class="text-gray-300 dark:text-neutral-600">↕</span>
                                                                                        @endif
                                                                                    </button>
                                                                                </th>
                                                                                <th class="px-4 py-2 text-center">
                                                                                    <button type="button" wire:click="sortDetailBy('max')"
                                                                                        class="inline-flex items-center gap-x-1 uppercase text-rose-500 hover:text-rose-600">
                                                                                        Max
                                                                                        @if ($detailSortColumn === 'max')
                                                                                            <span>{{ $detailSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                                                        @else
                                                                                            <span class="text-gray-300 dark:text-neutral-600">↕</span>
                                                                                        @endif
                                                                                    </button>
                                                                                </th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                                                            @foreach ($detail['rows'] as $catIndex => $cat)
                                                                                <tr wire:key="cat-{{ $office['office_id'] }}-{{ $catIndex }}"
                                                                                    class="bg-white text-sm dark:bg-neutral-900">
                                                                                    <td class="px-4 py-2 whitespace-nowrap font-medium text-gray-800 dark:text-neutral-200">
                                                                                        {{ $cat['name'] }}
                                                                                    </td>
                                                                                    <td class="px-4 py-2 whitespace-nowrap text-center font-semibold text-sky-600 tabular-nums">
                                                                                        {{ $cat['avg'] !== null ? number_format($cat['avg'], 1) : '—' }}
                                                                                    </td>
                                                                                    <td class="px-4 py-2 whitespace-nowrap text-center text-emerald-600 tabular-nums">
                                                                                        {{ $cat['min'] !== null ? number_format($cat['min']) : '—' }}
                                                                                    </td>
                                                                                    <td class="px-4 py-2 whitespace-nowrap text-center text-rose-600 tabular-nums">
                                                                                        {{ $cat['max'] !== null ? number_format($cat['max']) : '—' }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                @if ($detail['rows']->hasPages())
                                                                    <div class="pt-3">
                                                                        {{ $detail['rows']->links('livewire.partials.pagination') }}
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td class="text-center py-5 font-bold text-lg text-gray-800 dark:text-neutral-200" colspan="4">
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
