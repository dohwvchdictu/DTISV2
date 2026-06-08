<div class="w-full lg:ps-64">
    <div class="p-4 sm:p-6 space-y-4">

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
            <li class="inline-flex items-center text-sm font-semibold text-gray-800 truncate dark:text-neutral-200"
                aria-current="page">
                Routing Logbook
            </li>
        </ol>
        {{-- End Breadcrumb --}}

        {{-- Card --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-neutral-900 dark:border-neutral-700">

            {{-- Card Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">Routing Logbook</h2>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">
                        Documents forwarded by your office today &mdash; live receipt status.
                    </p>
                </div>
                <div class="flex items-center gap-x-2">
                    {{-- Live indicator --}}
                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                        <span class="relative flex size-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full size-2 bg-emerald-500"></span>
                        </span>
                        Live
                    </span>
                    <span class="text-xs text-gray-400 dark:text-neutral-500">updates every 3s</span>
                </div>
            </div>
            {{-- End Card Header --}}

            {{-- Date filter --}}
            <div class="px-6 py-3 border-b border-gray-200 dark:border-neutral-700 flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-500 dark:text-neutral-400 whitespace-nowrap">From</label>
                    <input type="date" wire:model.live="from"
                        class="py-1.5 px-3 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-500 dark:text-neutral-400 whitespace-nowrap">To</label>
                    <input type="date" wire:model.live="to"
                        class="py-1.5 px-3 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200">
                </div>
                <button type="button" wire:click="resetDates"
                    class="py-1.5 px-3 text-xs font-medium rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 focus:outline-none dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-300">
                    Reset
                </button>
            </div>
            {{-- End Date filter --}}

            {{-- Logbook table (polls every 3 seconds) --}}
            <div wire:poll.3s>
                @if ($logs->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <svg class="size-12 text-gray-300 dark:text-neutral-600 mb-3" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No documents forwarded today.</p>
                        <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">This logbook will populate once you forward documents.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-neutral-400">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-neutral-400">Control No.</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-neutral-400">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-neutral-400">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-neutral-400">Destination</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-neutral-400">Forwarded At</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-neutral-400">Receipt Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @foreach ($logs as $index => $log)
                                    @php
                                        $doc    = $docs[$log->document_id] ?? null;
                                        $status = $doc->status ?? '—';
                                        $received = $status === 'On Process';
                                    @endphp
                                    <tr class="{{ $received ? 'bg-emerald-50 dark:bg-emerald-950/20' : 'bg-white dark:bg-neutral-900' }} transition-colors duration-500">
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-neutral-400">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="/document/view/{{ $doc->control_no }}"
                                                class="text-sm font-semibold text-emerald-600 hover:underline dark:text-emerald-400">
                                                {{ $doc->control_no ?? '—' }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-neutral-300 whitespace-nowrap">
                                            {{ $doc->category->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-neutral-300 max-w-xs truncate">
                                            {{ $doc->subject ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-neutral-300 whitespace-nowrap">
                                            {{ $this->getOfficeName((int) $log->assigned_to) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-neutral-400 whitespace-nowrap">
                                            {{ $log->created_at->format('h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($received)
                                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                                    </svg>
                                                    Received
                                                </span>
                                            @elseif ($status === 'Returned')
                                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                                                    </svg>
                                                    Returned
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-400">
                                                    <span class="relative flex size-2">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full size-2 bg-sky-500"></span>
                                                    </span>
                                                    Awaiting Receipt
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary footer --}}
                    <div class="px-6 py-3 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between text-xs text-gray-500 dark:text-neutral-400">
                        <span>{{ $logs->count() }} document(s) forwarded today</span>
                        <span>
                            {{ $logs->filter(fn($l) => isset($docs[$l->document_id]) && $docs[$l->document_id]->status === 'On Process')->count() }}
                            / {{ $logs->count() }} received
                        </span>
                    </div>
                @endif
            </div>
            {{-- End logbook table --}}

        </div>
        {{-- End Card --}}

    </div>
</div>
