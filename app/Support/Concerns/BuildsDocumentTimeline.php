<?php

namespace App\Support\Concerns;

use App\Support\DocumentTimeline;

/**
 * Shared entry point for the components that render a routing trail.
 *
 * Each host resolves office and employee names against the directory API in
 * its own way, so those two lookups stay overridable; everything else about
 * the timeline lives in DocumentTimeline.
 */
trait BuildsDocumentTimeline
{
    /**
     * @param  iterable|null  $logs  Newest-first logs; defaults to $this->logs.
     */
    public function timelineRows($logs = null): array
    {
        return DocumentTimeline::build(
            $logs ?? $this->logs,
            fn ($id) => $this->resolveTimelineOffice($id),
            fn ($id) => $this->resolveTimelineUser($id),
        );
    }

    public function timelineLocation(array $rows): ?string
    {
        return DocumentTimeline::currentLocation($rows);
    }

    /**
     * Status colour for the tracking modal, where the status reads as part of a
     * line of text. The page-level colorIndicator() helpers differ per host —
     * some return background classes, which would paint a block here.
     */
    public function timelineStatusColor($status): string
    {
        return match ($status) {
            'Created' => 'text-gray-500 dark:text-neutral-400',
            'Closed' => 'text-red-600 dark:text-red-400',
            'On Process' => 'text-yellow-600 dark:text-yellow-400',
            'Returned' => 'text-amber-600 dark:text-amber-400',
            default => 'text-sky-600 dark:text-sky-400',
        };
    }

    protected function resolveTimelineOffice($id): ?string
    {
        return $this->lookUpOffice($id);
    }

    protected function resolveTimelineUser($id): ?string
    {
        return $this->filterUser($id);
    }
}
