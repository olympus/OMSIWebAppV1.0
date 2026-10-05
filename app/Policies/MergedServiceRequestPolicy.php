<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MergedServiceRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class MergedServiceRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MergedServiceRequest');
    }

    public function view(AuthUser $authUser, MergedServiceRequest $mergedServiceRequest): bool
    {
        return $authUser->can('View:MergedServiceRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MergedServiceRequest');
    }

    public function update(AuthUser $authUser, MergedServiceRequest $mergedServiceRequest): bool
    {
        return $authUser->can('Update:MergedServiceRequest');
    }

    public function delete(AuthUser $authUser, MergedServiceRequest $mergedServiceRequest): bool
    {
        return $authUser->can('Delete:MergedServiceRequest');
    }

    public function restore(AuthUser $authUser, MergedServiceRequest $mergedServiceRequest): bool
    {
        return $authUser->can('Restore:MergedServiceRequest');
    }

    public function forceDelete(AuthUser $authUser, MergedServiceRequest $mergedServiceRequest): bool
    {
        return $authUser->can('ForceDelete:MergedServiceRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MergedServiceRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MergedServiceRequest');
    }

    public function replicate(AuthUser $authUser, MergedServiceRequest $mergedServiceRequest): bool
    {
        return $authUser->can('Replicate:MergedServiceRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MergedServiceRequest');
    }
}
