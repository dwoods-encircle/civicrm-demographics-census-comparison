<?php

namespace Civicrm\DemographicsCensusComparison\Service;

/**
 * Handles removal of orphaned custom data records.
 */
final class OrphanedDataCleaner
{
    /** @var callable */
    private $orphanLocator;

    /** @var callable */
    private $orphanRemover;

    /**
     * @param callable $orphanLocator Function returning an array of orphaned record identifiers.
     * @param callable $orphanRemover Function accepting an array of identifiers and returning the number deleted.
     */
    public function __construct(callable $orphanLocator, callable $orphanRemover)
    {
        $this->orphanLocator = $orphanLocator;
        $this->orphanRemover = $orphanRemover;
    }

    /**
     * Remove any orphaned records and return the number deleted.
     */
    public function cleanup(): int
    {
        $orphans = call_user_func($this->orphanLocator);
        if (empty($orphans)) {
            return 0;
        }

        $deleted = call_user_func($this->orphanRemover, $orphans);
        if ($deleted === null) {
            $deleted = is_countable($orphans) ? count($orphans) : 0;
        }

        return (int) $deleted;
    }
}
