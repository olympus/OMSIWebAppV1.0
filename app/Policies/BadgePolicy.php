<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Badge;
use Illuminate\Auth\Access\HandlesAuthorization;

class BadgePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Badge');
    }

    public function view(AuthUser $authUser, Badge $badge): bool
    {
        return $authUser->can('View:Badge');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Badge');
    }

    public function update(AuthUser $authUser, Badge $badge): bool
    {
        return $authUser->can('Update:Badge');
    }

    public function delete(AuthUser $authUser, Badge $badge): bool
    {
        return $authUser->can('Delete:Badge');
    }

    public function restore(AuthUser $authUser, Badge $badge): bool
    {
        return $authUser->can('Restore:Badge');
    }

    public function forceDelete(AuthUser $authUser, Badge $badge): bool
    {
        return $authUser->can('ForceDelete:Badge');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Badge');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Badge');
    }

    public function replicate(AuthUser $authUser, Badge $badge): bool
    {
        return $authUser->can('Replicate:Badge');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Badge');
    }
}
