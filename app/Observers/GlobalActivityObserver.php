<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class GlobalActivityObserver
{
    public function created(Model $model)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->log(class_basename($model) . ' created');
    }

    public function updated(Model $model)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->log(class_basename($model) . ' updated');
    }

    public function deleted(Model $model)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->log(class_basename($model) . ' deleted');
    }
}