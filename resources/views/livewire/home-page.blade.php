<!-- Content -->
<div class="w-full lg:ps-64">
    <div class="p-4 sm:p-6 lg:pt-1.5 space-y-4 sm:space-y-6">
        <ol class="flex items-center whitespace-nowrap">
            <li class="inline-flex items-center">
                <a class="flex items-center text-sm text-gray-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 dark:text-neutral-500 dark:hover:text-blue-500 dark:focus:text-blue-500"
                    href="#">
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
                Dashboard
            </li>
        </ol>
        {{-- End of Breadcrumb --}}

        <!-- Card Section -->
        <div class="max-w-[85rem] px-4 py-2 sm:px-6 lg:px-8 lg:py-2 mx-auto">
            @php
                /**
                 * One definition per card. `group` picks the icon: the workflow
                 * cards say what the office must do next, the deadline cards cut
                 * the same documents by how much time is left.
                 *
                 * The colour says which of those two questions a card answers.
                 * Workflow cards are cool (indigo, sky) — a state, not a warning.
                 * Deadline cards climb one warm ramp, yellow to orange to red, so
                 * urgency reads from the colour alone without reading the label.
                 * Nothing warm appears outside that ramp, or "For Action" would
                 * look like a deadline it has no part in.
                 *
                 * Light-mode shades are the 700s, not the 500s: the label is 12px
                 * uppercase, and yellow-500 on white sits near 2:1 contrast. The
                 * tile only carries an icon, so it can take the lighter tint —
                 * the same 100 / 500-at-20% pair the status badges use elsewhere.
                 */
                $actionCards = [
                    [
                        'key' => 'for_action',
                        'label' => 'For Action',
                        'group' => 'workflow',
                        'accent' => 'text-indigo-700 dark:text-indigo-400',
                        'tile' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400',
                        'tooltip' => 'Documents waiting to be received by your office (For Receiving and Returned).',
                    ],
                    [
                        'key' => 'pending',
                        'label' => 'Pending',
                        'group' => 'workflow',
                        'accent' => 'text-sky-700 dark:text-sky-400',
                        'tile' => 'bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400',
                        'tooltip' =>
                            'Documents already received and still in process at your office (On Process and Endorsed).',
                    ],
                    [
                        'key' => 'due_soon',
                        'label' => 'Due Soon',
                        'group' => 'deadline',
                        'accent' => 'text-yellow-700 dark:text-yellow-400',
                        'tile' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400',
                        'tooltip' => 'Deadline falls within the next 3 working days.',
                    ],
                    [
                        'key' => 'due_today',
                        'label' => 'Due Today',
                        'group' => 'deadline',
                        'accent' => 'text-orange-700 dark:text-orange-400',
                        'tile' => 'bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400',
                        'tooltip' => 'Deadline is today. Act on these before the day ends.',
                    ],
                    [
                        'key' => 'overdue',
                        'label' => 'Overdue',
                        'group' => 'deadline',
                        'accent' => 'text-red-700 dark:text-red-400',
                        'tile' => 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400',
                        'tooltip' => 'Past the required days and still not acted upon.',
                    ],
                ];
            @endphp

            <!-- Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6">
                @foreach ($actionCards as $card)
                    <!-- Card -->
                    <div
                        class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-900 dark:border-neutral-800">
                        <div class="p-4 md:p-5 flex gap-x-4">
                            <div
                                class="shrink-0 flex justify-center items-center size-[46px] rounded-lg {{ $card['tile'] }}">
                                @if ($card['group'] === 'workflow')
                                    <svg class="lucide lucide-file-input shrink-0 size-5"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                        <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                        <path d="M2 15h10" />
                                        <path d="m9 18 3-3-3-3" />
                                    </svg>
                                @else
                                    <svg class="lucide lucide-clock shrink-0 size-5"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                @endif
                            </div>

                            <div class="grow">
                                <div class="flex items-center gap-x-2">
                                    <p class="text-xs uppercase tracking-wide {{ $card['accent'] }}">
                                        {{ $card['label'] }}
                                    </p>
                                    <div class="hs-tooltip">
                                        <div class="hs-tooltip-toggle">
                                            <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                                <path d="M12 17h.01" />
                                            </svg>
                                            <span
                                                class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded shadow-sm dark:bg-neutral-700"
                                                role="tooltip">
                                                {{ $card['tooltip'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-1 flex items-center gap-x-2">
                                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                        {{ number_format($this->actionCounts[$card['key']]) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Card -->
                @endforeach
            </div>
            <!-- End Grid -->
        </div>
        <!-- End Card Section -->
    </div>
</div>
<!-- End Content -->
