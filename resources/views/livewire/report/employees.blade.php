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
                    Report
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                Status of Documents
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

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
                                        Endorsed Document Status per Employee
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-neutral-400 mb-2">
                                        Monthly summary of the status of endorsed documents per Employee.
                                    </p>
                                </div>
                                <div>
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <div class="min-w-[130px]">
                                            <label for="startDate" class="sr-only">Start Date</label>
                                            <div class="relative">
                                                <input type="date" wire:model.live.debounce.2500ms="startDate"
                                                    name='startDate'
                                                    class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Select date">
                                            </div>
                                        </div>
                                        <div class="min-w-[130px]">
                                            <label for="EndDate" class="sr-only">End Date</label>
                                            <div class="relative">
                                                <input type="date" wire:model.live.debounce.2500ms="endDate"
                                                    name="endDate"
                                                    class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Select date">
                                            </div>
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
                                                Employee
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
                                    @forelse ($sortedEmployees as $key => $employee)
                                    <tr wire:key="employee-{{ $employee['id'] }}"
                                        class="bg-white hover:bg-gray-50 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                                        <td class="size-px whitespace-nowrap">
                                            <span class="block">
                                                <div class="px-6 py-4">
                                                    <div class="block font-semibold text-emerald-900 decoration-2">
                                                        {{ $employee['lastName'] . ', ' . $employee['firstName'] }}
                                                    </div>
                                                </div>
                                            </span>
                                        </td>
                                        <td class="size-px whitespace-nowrap">
                                            <span class="block relative z-10">
                                                <div class="px-6 text-amber-600 flex gap-x-1">
                                                    {{
                                                    number_format( $incoming =
                                                    \App\Models\Document::where('endorsed_to',
                                                    $employee['id'])->whereIn('status', ['For Receiving',
                                                    'Returned'])->whereBetween('created_at',
                                                    [\Carbon\Carbon::parse($this->startDate)->subDay(),
                                                    \Carbon\Carbon::parse($this->endDate)->addDay()])->count())
                                                    }}
                                                </div>
                                            </span>
                                        </td>
                                        <td class="size-px whitespace-nowrap">
                                            <span class="block relative z-10">
                                                <div class="px-6 flex text-red-600 gap-x-1">
                                                    {{
                                                    number_format( $pending = \App\Models\Document::where('endorsed_to',
                                                    $employee['id'])->whereBetween('created_at',
                                                    [\Carbon\Carbon::parse($this->startDate)->subDay(),
                                                    \Carbon\Carbon::parse($this->endDate)->addDay()])->whereIn('status',
                                                    ['On Process', 'Endorsed'])->count())
                                                    }}
                                                </div>
                                            </span>
                                        </td>
                                        <td class="size-px whitespace-nowrap">
                                            <span class="block relative z-10">
                                                <div class="px-6 flex text-emerald-600 gap-x-1">
                                                    {{
                                                    number_format($processed = \App\Models\Document::whereHas('logs',
                                                    fn($query) =>
                                                    $query->where('assigned_to', $this->office)
                                                    ->where('user_id', $employee['id'])
                                                    ->whereIn('action_id', [3, 5])
                                                    )
                                                    ->whereBetween('created_at', [
                                                    \Carbon\Carbon::parse($this->startDate)->subDay(),
                                                    \Carbon\Carbon::parse($this->endDate)->addDay(),
                                                    ])
                                                    ->count())
                                                    }}
                                                </div>
                                            </span>
                                        </td>
                                        <td class="size-px whitespace-nowrap">
                                            <span class="block relative z-10">
                                                <div class="px-6 flex gap-x-1">
                                                    {{
                                                    number_format($this->documentsPercentage($incoming, $pending,
                                                    $processed), 2)
                                                    }} %
                                                </div>
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="text-center py-5 font-bold text-lg" colspan="7">No records
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