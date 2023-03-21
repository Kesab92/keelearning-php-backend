<?php

namespace App\Services\AccessLogMeta;

interface AccessLogDifferences
{
    /**
     * Returns differences between entries
     * @param array $oldEntry
     * @param array $newEntry
     * @return array
     */
    public function getDifferences(array $oldEntry, array $newEntry):array;

    /**
     * Has differences
     * @return bool
     */
    public function hasDifferences():bool;
}
