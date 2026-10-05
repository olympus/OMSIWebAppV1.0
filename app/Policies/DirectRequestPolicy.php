<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DirectRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class DirectRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DirectRequest');
    }

    public function view(AuthUser $authUser, DirectRequest $directRequest): bool
    {
        return $authUser->can('View:DirectRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DirectRequest');
    }

    public function update(AuthUser $authUser, DirectRequest $directRequest): bool
    {
        return $authUser->can('Update:DirectRequest');
    }

    public function delete(AuthUser $authUser, DirectRequest $directRequest): bool
    {
        return $authUser->can('Delete:DirectRequest');
    }

    public function restore(AuthUser $authUser, DirectRequest $directRequest): bool
    {
        return $authUser->can('Restore:DirectRequest');
    }

    public function forceDelete(AuthUser $authUser, DirectRequest $directRequest): bool
    {
        return $authUser->can('ForceDelete:DirectRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DirectRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DirectRequest');
    }

    public function replicate(AuthUser $authUser, DirectRequest $directRequest): bool
    {
        return $authUser->can('Replicate:DirectRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DirectRequest');
    }
}
