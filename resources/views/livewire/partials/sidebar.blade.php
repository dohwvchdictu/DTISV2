<!-- Sidebar -->
<div id="hs-application-sidebar" style="top: 145px; bottom: 0"
    class="hs-overlay  [--auto-close:lg]
  hs-overlay-open:translate-x-0
  -translate-x-full transition-all duration-300 transform
  w-[260px]
  hidden
  fixed start-0 z-[60]
  bg-white
  lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
  dark:bg-neutral-800 dark:border-neutral-700"
    role="dialog" tabindex="-1" aria-label="Sidebar">
    <div class="relative flex flex-col h-full max-h-full">
        <!-- Collapse Toggle (desktop) -->
        <div class="hidden lg:block border-b border-gray-200 dark:border-neutral-700">
            <button type="button" id="sidebar-collapse-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar"
                class="w-full flex items-center justify-end gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-200">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m11 17-5-5 5-5" />
                    <path d="m18 17-5-5 5-5" />
                </svg>
            </button>
        </div>
        <!-- End Collapse Toggle -->

        <!-- Content -->
        <div
            style="min-height: 0"
            class="flex-1 overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
            <nav class="hs-accordion-group p-3 w-full flex flex-col flex-wrap" data-hs-accordion-always-open>
                <ul class="flex flex-col space-y-1">
                    <li>
                        <a wire:navigate
                            class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm {{ request()->is('/') ? 'text-gray-100 bg-emerald-600' : '' }} rounded-lg  hover:bg-gray-100 hover:text-neutral-800 focus:outline-none focus:bg-gray-100 dark:bg-neutral-700 dark:text-white"
                            href="/dashboard">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                <polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <li class="hs-accordion" id="account-accordion" data-hs-accordion-always-open>
                        <button type="button"
                            class="hs-accordion-toggle w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:text-neutral-200"
                            aria-expanded="true" aria-controls="account-accordion-child">
                            <svg class="shrink-0 mt-0.5 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-plus-2">
                                <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                <path d="M3 15h6" />
                                <path d="M6 12v6" />
                            </svg>
                            New Document

                            <svg class="hs-accordion-active:block ms-auto hidden size-4"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m18 15-6-6-6 6" />
                            </svg>

                            <svg class="hs-accordion-active:hidden ms-auto block size-4"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div id="account-accordion-child"
                            class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300"
                            role="region" aria-labelledby="account-accordion" style="height:auto;">
                            <ul class="ps-8 pt-1 space-y-1">
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/new-document">
                                        <svg class="shrink-0 mt-0.5 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-file">
                                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                        </svg>
                                        Document
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="hs-accordion" id="inbox-accordion" data-hs-accordion-always-open>
                        <button type="button"
                            class="hs-accordion-toggle w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:text-neutral-200"
                            aria-expanded="true" aria-controls="inbox-accordion-child">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-inbox">
                                <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                                <path
                                    d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                            </svg>
                            Inbox

                            <svg class="hs-accordion-active:block ms-auto hidden size-4"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m18 15-6-6-6 6" />
                            </svg>

                            <svg class="hs-accordion-active:hidden ms-auto block size-4"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div id="inbox-accordion-child"
                            class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300"
                            role="region" aria-labelledby="inbox-accordion" style="height:auto;">
                            <ul class="ps-8 pt-1 space-y-1">
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/my-documents">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-mail">
                                            <rect width="20" height="16" x="2" y="4" rx="2" />
                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                        </svg>
                                        My Documents
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/my-purchase-requests">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-shopping-bag">
                                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                            <path d="M3 6h18" />
                                            <path d="M16 10a4 4 0 0 1-8 0" />
                                        </svg>
                                        My Purchase Requests
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/my-payments">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-wallet-cards">
                                            <rect width="18" height="18" x="3" y="3" rx="2" />
                                            <path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2" />
                                            <path
                                                d="M3 11h3c.8 0 1.6.3 2.1.9l1.1.9c1.6 1.6 4.1 1.6 5.7 0l1.1-.9c.5-.5 1.3-.9 2.1-.9H21" />
                                        </svg>
                                        My Payments
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/routing-logbook">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M8 2v4" />
                                            <path d="M16 2v4" />
                                            <rect width="18" height="18" x="3" y="4" rx="2" />
                                            <path d="M3 10h18" />
                                            <path d="M8 14h.01" />
                                            <path d="M12 14h.01" />
                                            <path d="M16 14h.01" />
                                            <path d="M8 18h.01" />
                                            <path d="M12 18h.01" />
                                            <path d="M16 18h.01" />
                                        </svg>
                                        Routing Logbook
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="hs-accordion" id="status-accordion" data-hs-accordion-always-open>
                        <button type="button"
                            class="hs-accordion-toggle w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:text-neutral-200"
                            aria-expanded="true" aria-controls="status-accordion-child">
                            <span class="relative flex shrink-0">
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-book-open-text">
                                    <path d="M12 7v14" />
                                    <path d="M16 12h2" />
                                    <path d="M16 8h2" />
                                    <path
                                        d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z" />
                                    <path d="M6 12h2" />
                                    <path d="M6 8h2" />
                                </svg>
                                @if ($statusCount->whereNull('bundle_id')->count() > 0)
                                    <span class="absolute top-0 start-0 flex size-3"
                                        style="transform: translate(-40%, -40%)">
                                        <span
                                            class="animate-ping absolute inline-flex size-full rounded-full bg-red-400 opacity-75 dark:bg-red-600"></span>
                                        <span class="relative inline-flex rounded-full size-3 bg-red-500"></span>
                                    </span>
                                @endif
                            </span>
                            Status

                            <svg class="hs-accordion-active:block ms-auto hidden size-4"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m18 15-6-6-6 6" />
                            </svg>

                            <svg class="hs-accordion-active:hidden ms-auto block size-4"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div id="status-accordion-child"
                            class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300"
                            role="region" aria-labelledby="status-accordion" style="height:auto;">
                            <ul class="ps-8 pt-1 space-y-1">
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/status-incoming">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-file-input">
                                            <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                            <path d="M2 15h10" />
                                            <path d="m9 18 3-3-3-3" />
                                        </svg>
                                        Incoming
                                        @if ($statusCount->whereNull('bundle_id')->whereIn('status', ['For Receiving', 'Returned'])->count() > 0)
                                            <span
                                                class="inline-flex items-center py-0.5 px-1.5 rounded-full text-xs font-medium bg-red-500 text-white">{{ $statusCount->whereNull('bundle_id')->whereIn('status', ['For Receiving', 'Returned'])->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/status-pending">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-file-code-2">
                                            <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                            <path d="m5 12-3 3 3 3" />
                                            <path d="m9 18 3-3-3-3" />
                                        </svg>
                                        Pending
                                        @if ($statusCount->whereNull('bundle_id')->where('status', 'On Process')->count() > 0)
                                            <span
                                                class="inline-flex items-center py-0.5 px-1.5 rounded-full text-xs font-medium bg-red-500 text-white">{{ $statusCount->whereNull('bundle_id')->where('status', 'On Process')->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/status-endorsed">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-file-user-icon lucide-file-user">
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                            <path d="M15 18a3 3 0 1 0-6 0" />
                                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z" />
                                            <circle cx="12" cy="13" r="2" />
                                        </svg>
                                        Endorsed
                                        @if ($statusCount->whereNull('bundle_id')->where('status', 'On Process')->where('endorsed_to', $user['id'])->count() > 0)
                                            <span
                                                class="inline-flex items-center py-0.5 px-1.5 rounded-full text-xs font-medium bg-red-500 text-white">{{ $statusCount->whereNull('bundle_id')->where('status', 'On Process')->where('endorsed_to', $user['id'])->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/status-forwarded">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-file-output">
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                            <path d="M4 7V4a2 2 0 0 1 2-2 2 2 0 0 0-2 2" />
                                            <path d="M4.063 20.999a2 2 0 0 0 2 1L18 22a2 2 0 0 0 2-2V7l-5-5H6" />
                                            <path d="m5 11-3 3" />
                                            <path d="m5 17-3-3h10" />
                                        </svg>
                                        Processed
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/status-closed">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-file-x-2">
                                            <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                            <path d="m8 12.5-5 5" />
                                            <path d="m3 12.5 5 5" />
                                        </svg>
                                        Closed
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="hs-accordion" id="reports-accordion" data-hs-accordion-always-open>
                        <button type="button"
                            class="hs-accordion-toggle w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-700 dark:text-neutral-200"
                            aria-expanded="true" aria-controls="reports-accordion-child">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-library">
                                <path d="m16 6 4 14" />
                                <path d="M12 6v14" />
                                <path d="M8 8v12" />
                                <path d="M4 4v16" />
                            </svg>
                            Reports

                            <svg class="hs-accordion-active:block ms-auto hidden size-4"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m18 15-6-6-6 6" />
                            </svg>

                            <svg class="hs-accordion-active:hidden ms-auto block size-4"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div id="reports-accordion-child"
                            class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300"
                            role="region" aria-labelledby="reports-accordion" style="height:auto;">
                            <ul class="ps-8 pt-1 space-y-1">
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/report-status-of-documents">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-book-copy">
                                            <path d="M2 16V4a2 2 0 0 1 2-2h11" />
                                            <path
                                                d="M22 18H11a2 2 0 1 0 0 4h10.5a.5.5 0 0 0 .5-.5v-15a.5.5 0 0 0-.5-.5H11a2 2 0 0 0-2 2v12" />
                                            <path d="M5 14H4a2 2 0 1 0 0 4h1" />
                                        </svg>
                                        Status
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/report-status-per-employee">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-book-copy">
                                            <path d="M2 16V4a2 2 0 0 1 2-2h11" />
                                            <path
                                                d="M22 18H11a2 2 0 1 0 0 4h10.5a.5.5 0 0 0 .5-.5v-15a.5.5 0 0 0-.5-.5H11a2 2 0 0 0-2 2v12" />
                                            <path d="M5 14H4a2 2 0 1 0 0 4h1" />
                                        </svg>
                                        Endorsements
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/report-status-of-external-documents">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-folder-output">
                                            <path
                                                d="M2 7.5V5a2 2 0 0 1 2-2h3.9a2 2 0 0 1 1.69.9l.81 1.2a2 2 0 0 0 1.67.9H20a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-1.5" />
                                            <path d="M2 13h10" />
                                            <path d="m5 10-3 3 3 3" />
                                        </svg>
                                        External Requests
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/report-status-of-internal-documents">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-folder-input">
                                            <path
                                                d="M2 9V5a2 2 0 0 1 2-2h3.9a2 2 0 0 1 1.69.9l.81 1.2a2 2 0 0 0 1.67.9H20a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-1" />
                                            <path d="M2 13h10" />
                                            <path d="m9 16 3-3-3-3" />
                                        </svg>
                                        Internal Documents
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/report-per-unit">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-building-2">
                                            <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" />
                                            <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" />
                                            <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" />
                                            <path d="M10 6h4" />
                                            <path d="M10 10h4" />
                                            <path d="M10 14h4" />
                                            <path d="M10 18h4" />
                                        </svg>
                                        Per Unit
                                    </a>
                                </li>
                                <li>
                                    <a wire:navigate
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:bg-neutral-800 dark:text-neutral-200"
                                        href="/report-turnaround-time">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-timer">
                                            <line x1="10" x2="14" y1="2" y2="2" />
                                            <line x1="12" x2="15" y1="14" y2="11" />
                                            <circle cx="12" cy="14" r="8" />
                                        </svg>
                                        Turnaround Time
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li><a class="w-full flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-900 dark:text-neutral-200 dark:hover:text-neutral-300"
                            href="#">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-message-square-heart">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                <path
                                    d="M14.8 7.5a1.84 1.84 0 0 0-2.6 0l-.2.3-.3-.3a1.84 1.84 0 1 0-2.4 2.8L12 13l2.7-2.7c.9-.9.8-2.1.1-2.8" />
                            </svg>
                            Feedback
                        </a></li>
                </ul>
            </nav>
        </div>
        <!-- End Content -->
    </div>
</div>
<!-- End Sidebar -->
