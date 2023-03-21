<?php
namespace App\Transformers\BackendApi\Appointments;

use App\Transformers\AbstractTransformer;

class SimpleAppointmentTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        return [
            'end_date' => $model->end_date->toDateTimeString(),
            'id' => $model->id,
            'is_cancelled' => $model->is_cancelled,
            'is_draft' => $model->is_draft,
            'name' => $model->name,
            'published_at' => $model->published_at,
            'start_date' => $model->start_date->toDateTimeString(),
        ];
    }
}

