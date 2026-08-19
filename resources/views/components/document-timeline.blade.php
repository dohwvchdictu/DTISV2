@props(['rows' => []])

{{--
    Shared routing trail. Rows come from App\Support\DocumentTimeline, which
    owns the From / To labelling and the Forwarded + For Receiving merge.
--}}
<div>
    @foreach ($rows as $row)
        <div wire:key="{{ $row['key'] }}" class="flex gap-x-3">
            {{-- Date, action badge and time since the previous step --}}
            <div class="w-24 sm:w-28 shrink-0 text-end">
                <span class="block text-xs text-gray-500 dark:text-neutral-400">
                    {{ $row['created_at'] ? $row['created_at']->format('d M Y') : '—' }}
                </span>
                @if ($row['created_at'])
                    <span class="block text-xs text-gray-500 dark:text-neutral-400">
                        {{ $row['created_at']->format('h:i A') }}
                    </span>
                @endif
                <div class="mt-1 my-1">
                    <span
                        class="inline-flex items-center gap-1.5 py-1 px-3 rounded-lg text-xs {{ $row['color'] }} dark:{{ str_replace('-100', '-500/20', $row['color']) }} font-medium text-gray-800 dark:text-neutral-200">
                        {{ Str::title($row['action']) }}
                    </span>
                </div>
                @if ($row['elapsed'])
                    <span class="block text-[11px] text-gray-400 dark:text-neutral-500"
                        title="Time since the previous step">
                        +{{ $row['elapsed'] }}
                    </span>
                @endif
            </div>

            {{-- Connector --}}
            <div
                class="relative last:after:hidden after:absolute after:top-7 after:bottom-0 after:start-3.5 after:w-px after:-translate-x-[0.5px] after:bg-gray-200 dark:after:bg-neutral-700">
                <div class="relative z-10 size-7 flex justify-center items-center">
                    <div
                        class="size-2 rounded-full {{ $loop->first ? 'bg-emerald-400' : 'bg-gray-400 dark:bg-neutral-600' }}">
                    </div>
                </div>
            </div>

            {{-- Detail --}}
            <div class="grow pt-0.5 pb-8">
                @if ($row['description'])
                    <h3 class="flex max-w-xl gap-x-1.5 text-sm font-semibold text-gray-800 dark:text-white">
                        {{ $row['description'] }}
                    </h3>
                @endif

                @foreach ($row['offices'] as $label => $office)
                    <p class="mt-1 text-sm text-gray-600 dark:text-neutral-400">
                        <span class="font-medium">{{ $label }}:</span> {{ $office }}
                    </p>
                @endforeach

                @if ($row['endorsed_to'])
                    <p class="mt-1 text-sm text-gray-600 dark:text-neutral-400">
                        <span class="font-medium">Endorsed to:</span> {{ $row['endorsed_to'] }}
                    </p>
                @endif

                @if ($row['user'])
                    <p class="mt-1 text-sm text-gray-600 dark:text-neutral-400">
                        <span class="font-medium">By:</span> {{ $row['user'] }}
                    </p>
                @endif

                @if ($row['remarks'])
                    <p class="mt-1 text-sm text-gray-600 dark:text-neutral-400">
                        <span class="font-medium">Remarks:</span> {{ $row['remarks'] }}
                    </p>
                @endif
            </div>
        </div>
    @endforeach
</div>
