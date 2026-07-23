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
                    Status
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                Closed
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        {{-- Closed Table --}}
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
                                        Closed Documents
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                                        View all the documents closed by your office.
                                    </p>
                                </div>

                                <div>
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <div class="flex-1 min-w-[150px]">
                                            <label for="search" class="sr-only">Search</label>
                                            <div class="relative">
                                                <input wire:model.blur="search" type="text" id="search"
                                                    name="search"
                                                    class="py-2 px-3 ps-11 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
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
                                    </div>
                                </div>
                            </div>
                            <!-- End Header -->

                            <!-- Table -->
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-800">
                                    <tr>
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
                                                Closed At
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
                                                Closed By
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
                                                        <div class="block text-sm text-emerald-900 dark:text-emerald-400 decoration-2">
                                                            {{ $document->control_no }}
                                                        </div>
                                                    </div>
                                                    <div class="px-6">
                                                        <span
                                                            class="inline-flex items-center gap-1.5 py-1 px-2 mt-2 rounded-lg text-xs font-medium {{ $this->colorIndicator($document->status) }} text-gray-800 dark:text-neutral-200">
                                                            {!! $this->iconIndicator($document->status) !!}
                                                            {{ Str::title($document->status) }}
                                                        </span>
                                                    </div>
                                                </span>
                                            </td>
                                            <td class="align-top max-w-xs">
                                                <span class="block p-6">
                                                    <span
                                                        class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $document->category->name }}</span>
                                                    <span
                                                        class="block text-sm text-gray-500 dark:text-neutral-500 break-words">{{ $document->subject }}</span>
                                                    <div class="flex gap-x-1 my-2">
                                                        <span
                                                            class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium {{ $document->source == 'internal' ? 'bg-emerald-100 text-gray-800 dark:bg-emerald-500/20 dark:text-neutral-200' : 'bg-red-100 text-gray-800 dark:bg-red-500/20 dark:text-neutral-200' }} ">
                                                            {{ Str::title($document->source) }}
                                                        </span>
                                                        @if ($document->citizen_charter_id)
                                                            <span
                                                                class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-gray-100 dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                                                {{ \App\Models\CitizenCharter::find($document->citizen_charter_id)->name }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <span
                                                        class="py-2 block text-sm text-red-800 dark:text-neutral-200"><em>
                                                            Remarks:
                                                            {{ isset($document->logs->where('action_id', 5)->first()->remarks) ? $document->logs->where('action_id', 5)->first()->remarks : 'No remarks' }}
                                                        </em>
                                                    </span>
                                            </td>
                                            <td class="size-px whitespace-nowrap">
                                                <span class="block relative z-10">
                                                    <div class="px-6 flex gap-x-1 text-sm text-gray-600 dark:text-neutral-400">
                                                        {{ Carbon\Carbon::parse($this->filterLog($document))->format('D, M d, Y') }}
                                                    </div>
                                                    <div class="px-6 flex gap-x-1 text-sm text-gray-600 dark:text-neutral-400">
                                                        {{ Carbon\Carbon::parse($this->filterLog($document))->format('h:i:s A') }}
                                                    </div>
                                                </span>
                                            </td>

                                            <td class="size-px whitespace-nowrap">
                                                <span class="block relative z-10">
                                                    <div class="px-6 flex gap-x-1 text-sm text-gray-600 dark:text-neutral-400">
                                                        {{ $this->filterUser($document) }}
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center py-5 font-bold text-lg text-gray-800 dark:text-neutral-200" colspan="6">No records
                                                found!
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
                                        <span
                                            class="font-semibold text-gray-800 dark:text-neutral-200">{{ $documents->count() }}</span>
                                        results per page
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
        {{-- End of Closed Table --}}
    </div>
</div>
