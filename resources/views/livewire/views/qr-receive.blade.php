<div class="w-full lg:ps-64">
    <div class="p-6 sm:p-6 space-y-4 sm:space-y-6">

        {{-- Breadcrumb --}}
        <ol class="flex items-center whitespace-nowrap">
            <li class="inline-flex items-center">
                <a class="flex items-center text-sm text-gray-500 hover:text-emerald-600 focus:outline-none focus:text-emerald-600 dark:text-neutral-500 dark:hover:text-emerald-500"
                    href="/dashboard">
                    Home
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center">
                <a class="flex items-center text-sm text-gray-500 hover:text-emerald-600 focus:outline-none focus:text-emerald-600 dark:text-neutral-500 dark:hover:text-emerald-500"
                    href="/status-incoming">
                    Incoming
                </a>
                <svg class="shrink-0 mx-2 size-4 text-gray-400 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </li>
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                QR Receive
            </li>
        </ol>
        {{-- End Breadcrumb --}}

        <div class="max-w-[50rem] py-5 sm:px-6 lg:px-2 lg:py-5 mx-auto">

            {{-- ── NOT FOUND ────────────────────────────────────────────────── --}}
            @if ($state === 'not_found')
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                        <h2 class="text-xl font-bold text-red-600 dark:text-neutral-200">Document Not Found</h2>
                        <p class="text-sm text-gray-600 dark:text-neutral-400 mt-1">The scanned QR code does not match any document in the system.</p>
                    </div>
                    <div class="px-6 py-6 flex justify-center">
                        <svg class="size-16 text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div class="px-6 pb-6 flex justify-end">
                        <a href="/dashboard"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-gray-600 text-white hover:bg-gray-700 focus:outline-none">
                            Go to Dashboard
                        </a>
                    </div>
                </div>

            {{-- ── WRONG OFFICE ─────────────────────────────────────────────── --}}
            @elseif ($state === 'wrong_office')
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                        <h2 class="text-xl font-bold text-amber-600 dark:text-neutral-200">Not Assigned to Your Office</h2>
                        <p class="text-sm text-gray-600 dark:text-neutral-400 mt-1">This document is addressed to a different office and cannot be received here.</p>
                    </div>
                    <div class="px-6 py-5 grid space-y-3">
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Control No.:</dt>
                            <dd class="font-semibold text-gray-800 dark:text-neutral-200">{{ $document->control_no }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Subject:</dt>
                            <dd class="text-gray-800 dark:text-neutral-200">{{ $document->subject }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Category:</dt>
                            <dd class="text-gray-800 dark:text-neutral-200">{{ $document->category->name }}</dd>
                        </dl>
                    </div>
                    <div class="px-6 pb-6 flex justify-end">
                        <a href="/status-incoming"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none">
                            View Incoming
                        </a>
                    </div>
                </div>

            {{-- ── ALREADY RECEIVED ──────────────────────────────────────────── --}}
            @elseif ($state === 'already_received')
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                        <h2 class="text-xl font-bold text-blue-600 dark:text-neutral-200">Already Received</h2>
                        <p class="text-sm text-gray-600 dark:text-neutral-400 mt-1">This document has already been received and is currently being processed.</p>
                    </div>
                    <div class="px-6 py-5 grid space-y-3">
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Control No.:</dt>
                            <dd class="font-semibold text-gray-800 dark:text-neutral-200">{{ $document->control_no }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Subject:</dt>
                            <dd class="text-gray-800 dark:text-neutral-200">{{ $document->subject }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Current Status:</dt>
                            <dd>
                                <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-sky-100 text-gray-800 dark:bg-sky-500/20 dark:text-neutral-200">
                                    {{ $document->status }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                    <div class="px-6 pb-6 flex justify-end gap-x-2">
                        <a href="/dashboard"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200 shadow-sm hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none">
                            Dashboard
                        </a>
                        <a href="/status-pending"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none">
                            View Pending
                        </a>
                    </div>
                </div>

            {{-- ── CLOSED ────────────────────────────────────────────────────── --}}
            @elseif ($state === 'closed')
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                        <h2 class="text-xl font-bold text-gray-500 dark:text-neutral-200">Document Closed</h2>
                        <p class="text-sm text-gray-600 dark:text-neutral-400 mt-1">This document has already been completed and closed.</p>
                    </div>
                    <div class="px-6 py-5 grid space-y-3">
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Control No.:</dt>
                            <dd class="font-semibold text-gray-800 dark:text-neutral-200">{{ $document->control_no }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Subject:</dt>
                            <dd class="text-gray-800 dark:text-neutral-200">{{ $document->subject }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Category:</dt>
                            <dd class="text-gray-800 dark:text-neutral-200">{{ $document->category->name }}</dd>
                        </dl>
                    </div>
                    <div class="px-6 pb-6 flex justify-end">
                        <a href="/dashboard"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-gray-600 text-white hover:bg-gray-700 focus:outline-none">
                            Go to Dashboard
                        </a>
                    </div>
                </div>

            {{-- ── SUCCESS ───────────────────────────────────────────────────── --}}
            @elseif ($state === 'done')
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center gap-x-3">
                        <span class="inline-flex items-center justify-center size-9 rounded-full bg-emerald-100 shrink-0">
                            <svg class="size-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">Document Received!</h2>
                            <p class="text-sm text-gray-600 dark:text-neutral-400">Marked as <strong>On Process</strong> and assigned to your office.</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 grid space-y-3">
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Control No.:</dt>
                            <dd>
                                <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-gray-50 dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                    {{ $document->control_no }}
                                </span>
                            </dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Subject:</dt>
                            <dd class="font-medium text-gray-800 dark:text-neutral-200">{{ $document->subject }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Category:</dt>
                            <dd class="text-gray-800 dark:text-neutral-200">{{ $document->category->name }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Received by:</dt>
                            <dd class="font-semibold text-emerald-700 dark:text-neutral-200">{{ $officeName }}</dd>
                        </dl>
                        <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                            <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">New Status:</dt>
                            <dd>
                                <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-yellow-100 text-gray-800 dark:bg-yellow-500/20 dark:text-neutral-200">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>
                                    </svg>
                                    On Process
                                </span>
                            </dd>
                        </dl>
                    </div>
                    <div class="px-6 pb-6 flex justify-end gap-x-2">
                        <a href="/dashboard"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200 shadow-sm hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none">
                            Dashboard
                        </a>
                        <a href="/status-pending"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                            </svg>
                            View Pending
                        </a>
                    </div>
                </div>

            {{-- ── CONFIRM ───────────────────────────────────────────────────── --}}
            @else
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">

                    {{-- Card header --}}
                    <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                        <div>
                            <h2 class="text-xl font-bold text-emerald-700 dark:text-neutral-200">
                                {{ $document->category->name }}
                            </h2>
                            <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-gray-50 dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                Control No. {{ $document->control_no }}
                            </span>
                        </div>
                        <div class="inline-flex gap-x-2">
                            @if ($document->is_arta)
                                <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-red-100 text-red-700">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                    </svg>
                                    ARTA
                                </span>
                            @endif
                            <button
                                wire:click="promptConfirm"
                                wire:loading.attr="disabled"
                                class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700 disabled:opacity-50 disabled:pointer-events-none">
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/>
                                </svg>
                                <span wire:loading.remove>Receive Document</span>
                                <span wire:loading>Receiving...</span>
                            </button>
                        </div>
                    </div>
                    {{-- End Card header --}}

                    {{-- Document fields --}}
                    <div class="max-w-[70rem] py-5 sm:px-6 lg:px-6 lg:py-5 mx-auto">
                        <div class="grid md:grid-cols-2 gap-3 py-4">

                            {{-- Left column --}}
                            <div class="grid space-y-3 gap-x-4">

                                <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                                    <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Created at:</dt>
                                    <dd class="text-gray-800 dark:text-neutral-200">
                                        <span class="inline-flex items-center gap-x-1.5 text-xs text-gray-800 dark:text-neutral-200">
                                            {{ $document->created_at->format('D, M d, Y h:i:s A') }}
                                        </span>
                                    </dd>
                                </dl>

                                <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                                    <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Subject:</dt>
                                    <dd class="font-medium text-gray-800 dark:text-neutral-200">
                                        {{ $document->subject }}
                                    </dd>
                                </dl>

                                <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                                    <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Tagging:</dt>
                                    <dd>
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium {{ $document->source === 'internal' ? 'bg-emerald-100' : 'bg-red-100' }} text-gray-800 dark:text-neutral-200">
                                            {{ Str::title($document->source) }}
                                        </span>
                                    </dd>
                                </dl>

                                <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                                    <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Type:</dt>
                                    <dd class="inline-flex items-center gap-1.5 text-gray-800 py-1 px-2 rounded-lg text-xs dark:text-neutral-200">
                                        @if ($document->is_bundle)
                                            <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg> Bundle
                                        @else
                                            <svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg> Document
                                        @endif
                                    </dd>
                                </dl>

                            </div>
                            {{-- End Left column --}}

                            {{-- Right column --}}
                            <div class="grid space-y-3">

                                <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                                    <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Status:</dt>
                                    <dd>
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium bg-sky-100 text-gray-800 dark:bg-sky-500/20 dark:text-neutral-200">
                                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M2 15h10"/><path d="m9 18 3-3-3-3"/>
                                            </svg>
                                            {{ $document->status }}
                                        </span>
                                    </dd>
                                </dl>

                                <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                                    <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Receiving Office:</dt>
                                    <dd class="font-semibold text-emerald-700 dark:text-neutral-200">
                                        {{ $officeName }}
                                    </dd>
                                </dl>

                                <dl class="flex flex-col sm:flex-row gap-x-3 text-sm">
                                    <dt class="min-w-36 max-w-[200px] text-gray-500 dark:text-neutral-500">Scanned by:</dt>
                                    <dd class="text-gray-800 dark:text-neutral-200">
                                        {{ session('user')['firstName'] }} {{ session('user')['lastName'] }}
                                    </dd>
                                </dl>

                            </div>
                            {{-- End Right column --}}

                        </div>
                    </div>
                    {{-- End Document fields --}}

                </div>
            @endif

        </div>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading class="fixed z-50 flex items-center justify-center top-1/2 start-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 py-4 max-w-full min-h-[8rem]">
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg py-4 px-6 flex flex-col items-center">
            <div class="flex items-center gap-4">
                <div class="animate-spin inline-block size-8 border-[3px] border-current border-t-transparent text-emerald-600 rounded-full"
                    role="status" aria-label="loading">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="text-emerald-600 text-xl font-medium">Processing...</p>
            </div>
        </div>
    </div>
    {{-- End Loading overlay --}}

</div>
