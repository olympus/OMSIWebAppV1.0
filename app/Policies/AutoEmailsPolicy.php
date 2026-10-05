<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AutoEmails;
use Illuminate\Auth\Access\HandlesAuthorization;

class AutoEmailsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AutoEmails');
    }

    public function view(AuthUser $authUser, AutoEmails $autoEmails): bool
    {
        return $authUser->can('View:AutoEmails');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AutoEmails');
    }

    public function update(AuthUser $authUser, AutoEmails $autoEmails): bool
    {
        return $authUser->can('Update:AutoEmails');
    }

    public function delete(AuthUser $authUser, AutoEmails $autoEmails): bool
    {
        return $authUser->can('Delete:AutoEmails');
    }

    public function restore(AuthUser $authUser, AutoEmails $autoEmails): bool
    {
        return $authUser->can('Restore:AutoEmails');
    }

    public function forceDelete(AuthUser $authUser, AutoEmails $autoEmails): bool
    {
        return $authUser->can('ForceDelete:AutoEmails');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AutoEmails');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AutoEmails');
    }

    public function replicate(AuthUser $authUser, AutoEmails $autoEmails): bool
    {
        return $authUser->can('Replicate:AutoEmails');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AutoEmails');
    }
}
