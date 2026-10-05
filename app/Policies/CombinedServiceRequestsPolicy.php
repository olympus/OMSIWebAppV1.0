<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CombinedServiceRequests;
use Illuminate\Auth\Access\HandlesAuthorization;

class CombinedServiceRequestsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CombinedServiceRequests');
    }

    public function view(AuthUser $authUser, CombinedServiceRequests $combinedServiceRequests): bool
    {
        return $authUser->can('View:CombinedServiceRequests');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CombinedServiceRequests');
    }

    public function update(AuthUser $authUser, CombinedServiceRequests $combinedServiceRequests): bool
    {
        return $authUser->can('Update:CombinedServiceRequests');
    }

    public function delete(AuthUser $authUser, CombinedServiceRequests $combinedServiceRequests): bool
    {
        return $authUser->can('Delete:CombinedServiceRequests');
    }

    public function restore(AuthUser $authUser, CombinedServiceRequests $combinedServiceRequests): bool
    {
        return $authUser->can('Restore:CombinedServiceRequests');
    }

    public function forceDelete(AuthUser $authUser, CombinedServiceRequests $combinedServiceRequests): bool
    {
        return $authUser->can('ForceDelete:CombinedServiceRequests');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CombinedServiceRequests');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CombinedServiceRequests');
    }

    public function replicate(AuthUser $authUser, CombinedServiceRequests $combinedServiceRequests): bool
    {
        return $authUser->can('Replicate:CombinedServiceRequests');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CombinedServiceRequests');
    }
}
