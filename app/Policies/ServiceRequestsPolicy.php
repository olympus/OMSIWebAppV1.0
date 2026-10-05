<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ServiceRequests;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceRequestsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ServiceRequests');
    }

    public function view(AuthUser $authUser, ServiceRequests $serviceRequests): bool
    {
        return $authUser->can('View:ServiceRequests');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ServiceRequests');
    }

    public function update(AuthUser $authUser, ServiceRequests $serviceRequests): bool
    {
        return $authUser->can('Update:ServiceRequests');
    }

    public function delete(AuthUser $authUser, ServiceRequests $serviceRequests): bool
    {
        return $authUser->can('Delete:ServiceRequests');
    }

    public function restore(AuthUser $authUser, ServiceRequests $serviceRequests): bool
    {
        return $authUser->can('Restore:ServiceRequests');
    }

    public function forceDelete(AuthUser $authUser, ServiceRequests $serviceRequests): bool
    {
        return $authUser->can('ForceDelete:ServiceRequests');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ServiceRequests');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ServiceRequests');
    }

    public function replicate(AuthUser $authUser, ServiceRequests $serviceRequests): bool
    {
        return $authUser->can('Replicate:ServiceRequests');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ServiceRequests');
    }
}
