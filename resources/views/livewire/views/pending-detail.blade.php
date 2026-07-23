<div class="w-full lg:ps-64">
    <div class="p-6 sm:p-6 space-y-4 sm:space-y-6">
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
                    Pending
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                View
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        <div class="max-w-[70rem] py-5 sm:px-6 lg:px-2 lg:py-5 mx-auto">
            <!-- Grid -->
            <div class="mb-5 pb-5 flex justify-between items-center border-b border-gray-200 dark:border-neutral-700">
                <div>
                    <h2 class="text-2xl font-semibold text-emerald-600 dark:text-neutral-200">{{
                        $document->category->name }}</h2>
                    <span
                        class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-gray-50 dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                        Control No. {{
                        $document->control_no
                        }}
                    </span>
                </div>
                <!-- Col -->
                <div class="inline-flex gap-x-2">
                    <a wire:click="trackDocument({{ $document->id }})"
                        class="py-2 px-3 inline-flex items-center cursor-pointer gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none"
                        aria-haspopup="dialog" aria-expanded="false" aria-controls="document-timeline-modal"
                        data-hs-overlay="#document-timeline-modal">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-search">
                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            <path d="M4.268 21a2 2 0 0 0 1.727 1H18a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v3" />
                            <path d="m9 18-1.5-1.5" />
                            <circle cx="5" cy="14" r="3" />
                        </svg>
                        Track
                    </a>
                    <a wire:click='modalForwardDocument({{ $document->id }})'
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm cursor-pointer font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700 disabled:opacity-50 disabled:pointer-events-none"
                        aria-haspopup="dialog" aria-expanded="false" aria-controls="document-forward-modal"
                        data-hs-overlay="#document-forward-modal">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
                            <path
                                d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z" />
                            <path d="m21.854 2.147-10.94 10.939" />
                        </svg>
                        Forward
                    </a>
                    <a wire:click='modalEndorseDocument({{ $document->id }})'
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm cursor-pointer font-medium rounded-lg border border-transparent bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:bg-indigo-700 disabled:opacity-50 disabled:pointer-events-none"
                        aria-haspopup="dialog" aria-expanded="false" aria-controls="document-endorse-modal"
                        data-hs-overlay="#document-endorse-modal">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-user-round-icon lucide-user-round">
                            <circle cx="12" cy="8" r="5" />
                            <path d="M20 21a8 8 0 0 0-16 0" />
                        </svg>
                        Endorse
                    </a>
                    <a wire:click='modalCloseDocument'
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm cursor-pointer font-medium rounded-lg border border-transparent bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:bg-red-700 disabled:opacity-50 disabled:pointer-events-none"
                        aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-modal-input-text"
                        data-hs-overlay="#hs-modal-input-text">
                        <div class="hs-tooltip inline-block">
                            <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder-down">
                                <path
                                    d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z" />
                                <path d="M12 10v6" />
                                <path d="m15 13-3 3-3-3" />
                            </svg>
                            <span
                                class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                role="tooltip">
                                Close Document
                            </span>
                        </div>
                    </a>
                </div>
                <!-- Col -->
            </div>
            <!-- End Grid -->

            <!-- Grid -->
            <div class="grid md:grid-cols-2 gap-3 py-4">
                <div>
                    <div class="grid space-y-3 gap-x-4">
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Created at:
                            </dt>
                            <dd class="text-gray-800 dark:text-neutral-200">
                                <span class="inline-flex items-center gap-x-1.5 text-xs text-gray-800 dark:text-neutral-200">
                                    {{ Carbon\Carbon::parse($document->created_at)->format('D, M d, Y h:i:s A') }}
                                </span>
                            </dd>
                        </dl>

                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Subject:
                            </dt>
                            <dd class="font-medium text-gray-800 dark:text-neutral-200">
                                <address class="not-italic font-normal">
                                    {{ $document->subject}}
                                </address>
                            </dd>
                        </dl>

                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Tagging:
                            </dt>
                            <dd class="text-gray-800 dark:text-neutral-200">
                                <span
                                    class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium {{ $document->source == 'internal' ? 'bg-emerald-100 text-gray-800 dark:bg-emerald-500/20 dark:text-neutral-200' : 'bg-red-100 text-gray-800 dark:bg-red-500/20 dark:text-neutral-200'}} ">
                                    {{ Str::title($document->source) }}
                                </span>
                                @if($document->citizen_charter_id)
                                <span
                                    class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-gray-50 dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                    {{
                                    \App\Models\CitizenCharter::find($document->citizen_charter_id)->name
                                    }}
                                </span>
                                @endif
                            </dd>

                        </dl>

                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Required Days:
                            </dt>
                            <dd class="text-gray-800 dark:text-neutral-200">
                                @if($document->citizen_charter_id)
                                <span
                                    class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-gray-50 dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                    {{
                                    \App\Models\CitizenCharter::find($document->citizen_charter_id)->required_days
                                    }} Days
                                </span>
                                @endif
                            </dd>

                        </dl>

                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Endorsed To:
                            </dt>
                            <dd class="text-gray-800 dark:text-neutral-200">
                                @if($document->endorsed_to)
                                {{
                                $this->filterUser($document->endorsed_to)
                                }}
                                @endif
                            </dd>

                        </dl>

                    </div>
                </div>
                <!-- Col -->

                <div>

                    <div class="grid space-y-3">
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Status:
                            </dt>
                            <dd
                                class="inline-flex items-center gap-1.5 text-gray-800 py-1 px-2 rounded-lg text-xs {{ $this->colorIndicator($document->status) }} dark:text-neutral-200">
                                {!! $this->iconIndicator($document->status) !!}
                                {{ Str::title($document->status ) }}
                            </dd>
                        </dl>

                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Type of Document:
                            </dt>
                            <dd
                                class="inline-flex items-center gap-1.5 text-gray-800 py-1 px-2 rounded-lg text-xs dark:text-neutral-200">
                                {!! $this->typeIndicator($document->is_bundle) !!}
                            </dd>
                        </dl>

                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Office Origin:
                            </dt>
                            <dd class="text-gray-800 dark:text-neutral-200">
                                <span class="inline-flex items-center gap-x-1.5 text-gray-800 dark:text-neutral-200">
                                    {{ $this->lookupOffice($document->office_id) }}
                                </span>
                            </dd>
                        </dl>

                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">
                                Encoded by:
                            </dt>
                            <dd class="text-gray-800 dark:text-neutral-200">
                                <span class="inline-flex items-center gap-x-1.5 text-gray-800 dark:text-neutral-200">
                                    {{ $this->filterUser($document->user_id) }}
                                </span>
                            </dd>
                        </dl>

                    </div>
                </div>
                <!-- Col -->
            </div>
            <!-- End Grid -->

            <!-- Grid -->
            @if($documents_attached->count() > 0)
            <div class="grid py-4">
                <div class="-m-1.5 overflow-x-auto">
                    <div class="p-1.5 min-w-full inline-block align-middle">
                        <!-- Header -->
                        <div
                            class="py-2 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                            <div>
                                <lead class="text-sm text-neutral-600 dark:text-neutral-200">
                                    Attached Documents
                                </lead>
                            </div>
                        </div>
                        <!-- End Header -->
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            Control No</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            Subject</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                            Origin</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @forelse ($documents_attached as $document)
                                    <tr>
                                        <td
                                            class="py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            {{ $document->control_no }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $document->subject}}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $this->lookUpOffice($document->office_id) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="text-center py-5 font-bold text-lg text-gray-800 dark:text-neutral-200" colspan="4">
                                            No Attachments found!
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- End Grid -->
        </div>
    </div>
    {{-- Modal --}}
    @include('components.modals.view-pending-modal')
    {{-- End of Modal --}}
</div>
