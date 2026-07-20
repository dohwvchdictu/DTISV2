<div>
    @if ($paginator->hasPages())
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-600 dark:text-neutral-400">
                Showing
                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->lastItem() }}</span>
                of
                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->total() }}</span>
                results
            </p>

            <nav class="flex items-center gap-x-1" aria-label="Pagination">
                {{-- Previous --}}
                <button type="button" wire:click="previousPage"
                    @if ($paginator->onFirstPage()) disabled @endif
                    class="py-2 px-2.5 inline-flex items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                    aria-label="Previous">
                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    <span class="hidden sm:inline">Previous</span>
                </button>

                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span
                            class="min-w-9 flex justify-center items-center py-2 px-3 text-sm text-gray-500 dark:text-neutral-500">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                    class="min-w-9 flex justify-center items-center py-2 px-3 text-sm font-medium rounded-lg bg-blue-600 text-white tabular-nums">
                                    {{ $page }}
                                </span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }})"
                                    class="min-w-9 flex justify-center items-center py-2 px-3 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10 tabular-nums">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                <button type="button" wire:click="nextPage"
                    @if (!$paginator->hasMorePages()) disabled @endif
                    class="py-2 px-2.5 inline-flex items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                    aria-label="Next">
                    <span class="hidden sm:inline">Next</span>
                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            </nav>
        </div>
    @endif
</div>
