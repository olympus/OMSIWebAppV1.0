<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ApiRequests;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiRequestsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ApiRequests');
    }

    public function view(AuthUser $authUser, ApiRequests $apiRequests): bool
    {
        return $authUser->can('View:ApiRequests');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ApiRequests');
    }

    public function update(AuthUser $authUser, ApiRequests $apiRequests): bool
    {
        return $authUser->can('Update:ApiRequests');
    }

    public function delete(AuthUser $authUser, ApiRequests $apiRequests): bool
    {
        return $authUser->can('Delete:ApiRequests');
    }

    public function restore(AuthUser $authUser, ApiRequests $apiRequests): bool
    {
        return $authUser->can('Restore:ApiRequests');
    }

    public function forceDelete(AuthUser $authUser, ApiRequests $apiRequests): bool
    {
        return $authUser->can('ForceDelete:ApiRequests');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ApiRequests');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ApiRequests');
    }

    public function replicate(AuthUser $authUser, ApiRequests $apiRequests): bool
    {
        return $authUser->can('Replicate:ApiRequests');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ApiRequests');
    }
}
