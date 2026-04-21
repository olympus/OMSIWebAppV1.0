<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Speciality;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpecialityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Speciality');
    }

    public function view(AuthUser $authUser, Speciality $speciality): bool
    {
        return $authUser->can('View:Speciality');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Speciality');
    }

    public function update(AuthUser $authUser, Speciality $speciality): bool
    {
        return $authUser->can('Update:Speciality');
    }

    public function delete(AuthUser $authUser, Speciality $speciality): bool
    {
        return $authUser->can('Delete:Speciality');
    }

    public function restore(AuthUser $authUser, Speciality $speciality): bool
    {
        return $authUser->can('Restore:Speciality');
    }

    public function forceDelete(AuthUser $authUser, Speciality $speciality): bool
    {
        return $authUser->can('ForceDelete:Speciality');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Speciality');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Speciality');
    }

    public function replicate(AuthUser $authUser, Speciality $speciality): bool
    {
        return $authUser->can('Replicate:Speciality');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Speciality');
    }

}