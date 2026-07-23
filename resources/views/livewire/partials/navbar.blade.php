<div wire:poll.240s="refreshToken">
    <!-- ========== HEADER ========== -->
    <header style="top: 90px; height: 55px"
        class="fixed inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-[48] w-full bg-emerald-600 border-b text-sm dark:bg-neutral-800 dark:border-neutral-700">
        <nav class="px-4 sm:px-4 flex basis-full items-center w-full mx-auto">
            <div class="me-5 flex items-center shrink-0">
                <!-- Logo -->
                <a href="#" aria-label="Document Tracking Information System"
                    class="flex flex-col text-white focus:outline-none focus:opacity-80" style="line-height: 1.2">
                    <span class="font-bold text-center" style="font-size: 20px">DTIS</span>
                    <span class="text-xs whitespace-nowrap">Document Tracking Information System</span>
                </a>
                <!-- End Logo -->
            </div>

            <div class="w-full flex items-center justify-end ms-auto md:justify-between gap-x-1 md:gap-x-3">

                <div class="hidden md:block" style="margin-inline-start: 12px">
                    <!-- Search Input -->
                    <div class="relative cursor-pointer"
                        onclick="document.getElementById('document-search-modal') && window.HSOverlay.open(document.getElementById('document-search-modal'))">
                        <div class="px-2 absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-3.5">
                            <svg class="shrink-0 size-4 text-gray-400 dark:text-white/60"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                        </div>
                        <input type="text" readonly
                            class="py-2 ps-10 pe-16 block w-full bg-white border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-blue-500 cursor-pointer dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder:text-neutral-400 dark:focus:ring-neutral-600"
                            placeholder="Search documents...">
                        <div class="absolute inset-y-0 end-0 flex items-center pointer-events-none z-20 pe-3">
                            <kbd
                                class="px-2 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 border border-gray-200 rounded dark:bg-neutral-700 dark:text-neutral-300 dark:border-neutral-600">/</kbd>
                        </div>
                    </div>
                    <!-- End Search Input -->
                </div>

                <div class="flex flex-row items-center justify-end gap-1">
                    <!-- Dropdown -->
                    <div class="hs-dropdown [--placement:bottom-right] relative inline-flex">
                        <button id="hs-dropdown-account" type="button"
                            class="size-[38px] inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 focus:outline-none disabled:opacity-50 disabled:pointer-events-none dark:text-white"
                            aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <img class="shrink-0 size-[38px] rounded-full" src="{{ $this->image }}" alt="Avatar">
                        </button>

                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full"
                            role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-account">
                            <div class="py-3 px-5 bg-gray-100 rounded-t-lg dark:bg-neutral-700">
                                <p class="text-sm text-gray-500 dark:text-neutral-500">Signed in as</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                                    {{ $this->completeName() }}</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                                    {{ $this->user['office']['officeName'] }}</p>
                            </div>
                            <div class="p-1.5 space-y-0.5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 dark:focus:text-neutral-300">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                            <polyline points="16 17 21 12 16 7" />
                                            <line x1="21" x2="9" y1="12" y2="12" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End Dropdown -->
                </div>
            </div>
        </nav>
        <!-- End SearchBox Modal -->
    </header>
    <!-- ========== END HEADER ========== -->

    <!-- ========== MAIN CONTENT ========== -->
    <div class="-mt-px">
        <!-- Breadcrumb -->
        <div style="top: 145px; height: 50px"
            class="fixed inset-x-0 z-20 flex items-center bg-white border-y px-4 sm:px-6 lg:px-8 lg:hidden dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex items-center py-2">
                <!-- Navigation Toggle -->
                <button type="button"
                    class="size-8 flex justify-center items-center gap-x-2 border border-gray-200 text-gray-800 hover:text-gray-500 rounded-lg focus:outline-none focus:text-gray-500 disabled:opacity-50 disabled:pointer-events-none dark:border-neutral-700 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
                    aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-application-sidebar"
                    aria-label="Toggle navigation" data-hs-overlay="#hs-application-sidebar">
                    <span class="sr-only">Toggle Navigation</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-menu">
                        <line x1="4" x2="20" y1="12" y2="12" />
                        <line x1="4" x2="20" y1="6" y2="6" />
                        <line x1="4" x2="20" y1="18" y2="18" />
                    </svg>
                </button>
                <!-- End Navigation Toggle -->
            </div>
        </div>
        <!-- End Breadcrumb -->

        <!-- Spacer for fixed breadcrumb (mobile only) -->
        <div class="lg:hidden" style="height: 50px"></div>
    </div>

    {{-- Document Search Modal --}}
    <livewire:document-search />

</div>
