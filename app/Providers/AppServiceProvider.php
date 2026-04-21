<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity; // ✅ THIS LINE IS MISSING (IMPORTANT)
use Illuminate\Support\Str;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    /*public function boot(): void
    {
        Event::listen('eloquent.created: *', function ($event, $models) {

            $model = $models[0];

            // ✅ prevent infinite loop
            if ($model instanceof Activity) {
                return;
            }

            // ✅ prevent logging when no user
            if (!auth()->check()) {
                return;
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'attributes' => $model->getAttributes(),
                ])
                ->batch(Str::uuid())
                ->log(class_basename($model) . ' created');

        });


        Event::listen('eloquent.updated: *', function ($event, $models) {

            $model = $models[0];

            if ($model instanceof Activity) {
                return;
            }

            if (!auth()->check()) {
                return;
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'attributes' => $model->getAttributes(),
                    'old' => $model->getOriginal(),
                ])
                ->batch(Str::uuid())
                ->log(class_basename($model) . ' updated');

        });


        Event::listen('eloquent.deleted: *', function ($event, $models) {

            $model = $models[0];

            if ($model instanceof Activity) {
                return;
            }

            if (!auth()->check()) {
                return;
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'old' => $model->getOriginal(),
                ])
                ->batch(Str::uuid())
                ->log(class_basename($model) . ' deleted');
            
            // activity()
            //     ->causedBy(auth()->user())
            //     ->performedOn($model)
            //     ->log(class_basename($model) . ' deleted');

        });

    }*/

    public function boot(): void
    {   

        Event::listen('eloquent.created: *', function ($event, $models) {

            $model = $models[0];

            if ($model instanceof Activity || !auth()->check()) {
                return;
            }

            $user = auth()->user();

            activity()
                ->useLog('Resource') // ✅ log_name
                ->causedBy($user)
                ->performedOn($model)
                ->withProperties($model->getAttributes()) // ✅ exact properties format
                ->tap(function ($activity) {
                    $activity->event = 'Created';
                    $activity->batch_uuid = Str::uuid();
                })
                ->log(class_basename($model) . ' Created by ' . $user->name);

        });

        Event::listen('eloquent.updated: *', function ($event, $models) {

            $model = $models[0];

            if ($model instanceof Activity || !auth()->check()) {
                return;
            }

            $user = auth()->user();

            activity()
                ->useLog('Resource')
                ->causedBy($user)
                ->performedOn($model)
                ->withProperties($model->getAttributes())
                ->tap(function ($activity) {
                    $activity->event = 'Updated';
                    $activity->batch_uuid = Str::uuid();
                })
                ->log(class_basename($model) . ' Updated by ' . $user->name);

        });

        Event::listen('eloquent.deleted: *', function ($event, $models) {

            $model = $models[0];

            if ($model instanceof Activity || !auth()->check()) {
                return;
            }

            $user = auth()->user();

            activity()
                ->useLog('Resource')
                ->causedBy($user)
                ->performedOn($model)
                ->withProperties($model->getOriginal())
                ->tap(function ($activity) {
                    $activity->event = 'Deleted';
                    $activity->batch_uuid = Str::uuid();
                })
                ->log(class_basename($model) . ' Deleted by ' . $user->name);

        });
        
        /*Event::listen('eloquent.created: *', function ($event, $models) {

            $model = $models[0];

            if ($model instanceof \Spatie\Activitylog\Models\Activity) {
                return;
            }

            if (!auth()->check()) {
                return;
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'attributes' => $model->getAttributes(),
                ])
                ->tap(function ($activity) {
                    $activity->batch_uuid = \Illuminate\Support\Str::uuid();
                })
                ->log(class_basename($model) . ' created');

        });


        Event::listen('eloquent.updated: *', function ($event, $models) {

            $model = $models[0];

            if ($model instanceof \Spatie\Activitylog\Models\Activity) {
                return;
            }

            if (!auth()->check()) {
                return;
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'attributes' => $model->getAttributes(),
                    'old' => $model->getOriginal(),
                ])
                ->tap(function ($activity) {
                    $activity->batch_uuid = \Illuminate\Support\Str::uuid();
                })
                ->log(class_basename($model) . ' updated');

        });


        Event::listen('eloquent.deleted: *', function ($event, $models) {

            $model = $models[0];

            if ($model instanceof \Spatie\Activitylog\Models\Activity) {
                return;
            }

            if (!auth()->check()) {
                return;
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'old' => $model->getOriginal(),
                ])
                ->tap(function ($activity) {
                    $activity->batch_uuid = \Illuminate\Support\Str::uuid();
                })
                ->log(class_basename($model) . ' deleted');

        });*/

    }
}
