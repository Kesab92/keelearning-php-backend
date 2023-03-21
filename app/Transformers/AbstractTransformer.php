<?php


namespace App\Transformers;


use Illuminate\Support\Collection;

abstract class AbstractTransformer
{
    /**
     * Transforms the model.
     * @param $model
     * @return mixed
     */
    abstract public function transform($model);

    /**
     * Transform multiple models.
     * @param $collection
     * @return Collection
     */
    public function transformAll(Collection $collection)
    {
        return $collection->map(function ($item) {
            return $this->transform($item);
        });
    }
}
