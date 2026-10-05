<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Feedback;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeedbackPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Feedback');
    }

    public function view(AuthUser $authUser, Feedback $feedback): bool
    {
        return $authUser->can('View:Feedback');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Feedback');
    }

    public function update(AuthUser $authUser, Feedback $feedback): bool
    {
        return $authUser->can('Update:Feedback');
    }

    public function delete(AuthUser $authUser, Feedback $feedback): bool
    {
        return $authUser->can('Delete:Feedback');
    }

    public function restore(AuthUser $authUser, Feedback $feedback): bool
    {
        return $authUser->can('Restore:Feedback');
    }

    public function forceDelete(AuthUser $authUser, Feedback $feedback): bool
    {
        return $authUser->can('ForceDelete:Feedback');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Feedback');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Feedback');
    }

    public function replicate(AuthUser $authUser, Feedback $feedback): bool
    {
        return $authUser->can('Replicate:Feedback');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Feedback');
    }
}
