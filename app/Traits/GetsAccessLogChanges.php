<?php

namespace App\Traits;

use App\Models\Tag;

trait GetsAccessLogChanges
{
    protected array $differences = [];

    /**
     * Returns differences between entries
     * @param array $oldEntry
     * @param array $newEntry
     * @return array
     */
    public function getDifferences(array $oldEntry, array $newEntry):array
    {
        $differences = [];

        if (count($oldEntry) != count($newEntry)) {
            return $newEntry;
        }

        foreach ($newEntry as $key => $value) {
            if (!isset($oldEntry[$key])) {
                $differences[$key] = $value;
            }

            if (isset($oldEntry[$key])) {
                if (is_object($oldEntry[$key])) {
                    $oldEntry[$key] = json_decode(json_encode($oldEntry[$key]), true);
                    $value = json_decode(json_encode($value), true);
                }
                if (is_array($oldEntry[$key])) {
                    $difference = $this->getDifferences($oldEntry[$key], $value);

                    if(!empty($difference)) {
                        $differences[$key] = $difference;
                    }
                } else {
                    if ($oldEntry[$key] != $value) {
                        $differences[$key] = $value;
                        if (isset($newEntry['id'])) {
                            $differences['id'] = $newEntry['id'];
                        }
                    }
                }
            }

            if(array_key_exists($key, $differences) && is_null($differences[$key])) {
                unset($differences[$key]);
            }
            if ($key === 'tags' && isset($differences[$key])) {
                $differences[$key] = Tag
                    ::whereIn('id', $differences[$key])
                    ->get()
                    ->pluck('label');
            }
        }

        if(empty($differences)) {
            return [];
        }

        return $differences;
    }

    /**
     * Has differences
     * @return bool
     */
    public function hasDifferences():bool
    {
        return !empty($this->differences);
    }
}
