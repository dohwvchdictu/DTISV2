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
                External Documents
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        {{-- External Documents Table --}}
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
                                        External Requests Tracking Report
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-neutral-400 mb-2">
                                        Tracking of external requests with turnaround time monitoring.
                                    </p>
                                </div>
                                <div>
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <div class="min-w-[130px]">
                                            <label for="startDate" class="sr-only">Start Date</label>
                                            <div class="relative">
                                                <input type="date" wire:model.live.debounce.2500ms="startDate"
                                                    name='startDate'
                                                    class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-neutral-800 dark:border-neutral-600 dark:placeholder-neutral-400 dark:text-neutral-200 dark:[color-scheme:dark] dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Select date">
                                            </div>
                                        </div>
                                        <div class="min-w-[130px]">
                                            <label for="EndDate" class="sr-only">End Date</label>
                                            <div class="relative">
                                                <input type="date" wire:model.live.debounce.2500ms="endDate"
                                                    name="endDate"
                                                    class="bg-neutral-50 border border-gray-200 text-gray-600 text-sm shadow-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-neutral-800 dark:border-neutral-600 dark:placeholder-neutral-400 dark:text-neutral-200 dark:[color-scheme:dark] dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Select date">
                                            </div>
                                        </div>
                                        <div class="min-w-[130px]">
                                            <a href="{{ route('print.external.documents', ['startDate' => $startDate, 'endDate' => $endDate]) }}"
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
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Document Control No.
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Date Received
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Document Title
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Document Type
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Originating Office / Encoded By
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Destination / Forwarded To
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Current Location
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Required Days
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Days Remaining
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Current Status
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase text-gray-800 dark:text-neutral-200">
                                            Remarks
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @forelse ($documents as $document)
                                        @php
                                            $tracking = $this->trackingStatus($document);
                                            $badgeColors = [
                                                'complete' => 'bg-green-500 text-white',
                                                'due' => 'bg-yellow-300 text-gray-900 dark:text-neutral-200',
                                                'overdue' => 'bg-red-600 text-white',
                                                'pending' => 'bg-sky-400 text-white',
                                            ];
                                            $badgeClass = $badgeColors[$tracking['state']];
                                        @endphp
                                        <tr wire:key="document-{{ $document->id }}"
                                            class="bg-white hover:bg-gray-50 dark:bg-neutral-900 dark:hover:bg-neutral-800">
                                            <td class="px-4 py-4 whitespace-nowrap align-top">
                                                <span class="block font-semibold text-emerald-900 dark:text-neutral-200">
                                                    {{ $document->control_no ?? '—' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap align-top text-gray-800 dark:text-neutral-200">
                                                {{ $document->created_at->format('F d, Y') }}
                                            </td>
                                            <td class="px-4 py-4 align-top text-gray-800 dark:text-neutral-200 min-w-[280px] max-w-md">
                                                {{ $document->subject }}
                                            </td>
                                            <td class="px-4 py-4 align-top text-gray-800 dark:text-neutral-200 min-w-[150px]">
                                                {{ $document->category->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-4 align-top text-gray-800 dark:text-neutral-200 min-w-[150px]">
                                                {{ $this->getOfficeShortName($document->office_id) }} /
                                                {{ $this->getEmployeeName($document->user_id) }}
                                            </td>
                                            <td class="px-4 py-4 align-top text-gray-800 dark:text-neutral-200 min-w-[150px]">
                                                {{ $this->firstDestination($document) }}
                                            </td>
                                            <td class="px-4 py-4 align-top text-gray-800 dark:text-neutral-200 min-w-[150px]">
                                                {{ $this->currentLocation($document) }}
                                            </td>
                                            <td class="px-4 py-4 text-center align-middle whitespace-nowrap text-gray-800 dark:text-neutral-200">
                                                <span class="text-sm font-medium">{{ $tracking['required_days'] }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-center align-middle whitespace-nowrap">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">
                                                    {{ $tracking['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 align-top text-gray-800 dark:text-neutral-200 min-w-[130px]">
                                                {{ $document->status }}
                                            </td>
                                            <td class="px-4 py-4 align-top text-gray-800 dark:text-neutral-200 min-w-[150px]">
                                                {{ $this->latestRemarks($document) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center py-5 font-bold text-lg text-gray-800 dark:text-neutral-200" colspan="11">No records
                                                found!
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>
                            <!-- End Table -->

                            {{-- Pagination --}}
                            <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                                {{ $documents->links() }}
                            </div>
                            {{-- End of Pagination --}}

                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- End of External Documents Table --}}
    </div>
</div>
