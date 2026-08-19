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

    protected function resolveTimelineOffice($id): ?string
    {
        return $this->lookUpOffice($id);
    }

    protected function resolveTimelineUser($id): ?string
    {
        return $this->filterUser($id);
    }
}
