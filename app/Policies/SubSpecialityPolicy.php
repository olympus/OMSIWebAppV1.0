<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SubSpeciality;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubSpecialityPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubSpeciality');
    }

    public function view(AuthUser $authUser, SubSpeciality $subSpeciality): bool
    {
        return $authUser->can('View:SubSpeciality');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubSpeciality');
    }

    public function update(AuthUser $authUser, SubSpeciality $subSpeciality): bool
    {
        return $authUser->can('Update:SubSpeciality');
    }

    public function delete(AuthUser $authUser, SubSpeciality $subSpeciality): bool
    {
        return $authUser->can('Delete:SubSpeciality');
    }

    public function restore(AuthUser $authUser, SubSpeciality $subSpeciality): bool
    {
        return $authUser->can('Restore:SubSpeciality');
    }

    public function forceDelete(AuthUser $authUser, SubSpeciality $subSpeciality): bool
    {
        return $authUser->can('ForceDelete:SubSpeciality');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubSpeciality');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubSpeciality');
    }

    public function replicate(AuthUser $authUser, SubSpeciality $subSpeciality): bool
    {
        return $authUser->can('Replicate:SubSpeciality');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubSpeciality');
    }
}
