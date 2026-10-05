<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ArchiveServiceRequests;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArchiveServiceRequestsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ArchiveServiceRequests');
    }

    public function view(AuthUser $authUser, ArchiveServiceRequests $archiveServiceRequests): bool
    {
        return $authUser->can('View:ArchiveServiceRequests');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ArchiveServiceRequests');
    }

    public function update(AuthUser $authUser, ArchiveServiceRequests $archiveServiceRequests): bool
    {
        return $authUser->can('Update:ArchiveServiceRequests');
    }

    public function delete(AuthUser $authUser, ArchiveServiceRequests $archiveServiceRequests): bool
    {
        return $authUser->can('Delete:ArchiveServiceRequests');
    }

    public function restore(AuthUser $authUser, ArchiveServiceRequests $archiveServiceRequests): bool
    {
        return $authUser->can('Restore:ArchiveServiceRequests');
    }

    public function forceDelete(AuthUser $authUser, ArchiveServiceRequests $archiveServiceRequests): bool
    {
        return $authUser->can('ForceDelete:ArchiveServiceRequests');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ArchiveServiceRequests');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ArchiveServiceRequests');
    }

    public function replicate(AuthUser $authUser, ArchiveServiceRequests $archiveServiceRequests): bool
    {
        return $authUser->can('Replicate:ArchiveServiceRequests');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ArchiveServiceRequests');
    }
}
