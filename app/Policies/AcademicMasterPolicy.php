<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AcademicMaster;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcademicMasterPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AcademicMaster');
    }

    public function view(AuthUser $authUser, AcademicMaster $academicMaster): bool
    {
        return $authUser->can('View:AcademicMaster');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AcademicMaster');
    }

    public function update(AuthUser $authUser, AcademicMaster $academicMaster): bool
    {
        return $authUser->can('Update:AcademicMaster');
    }

    public function delete(AuthUser $authUser, AcademicMaster $academicMaster): bool
    {
        return $authUser->can('Delete:AcademicMaster');
    }

    public function restore(AuthUser $authUser, AcademicMaster $academicMaster): bool
    {
        return $authUser->can('Restore:AcademicMaster');
    }

    public function forceDelete(AuthUser $authUser, AcademicMaster $academicMaster): bool
    {
        return $authUser->can('ForceDelete:AcademicMaster');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AcademicMaster');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AcademicMaster');
    }

    public function replicate(AuthUser $authUser, AcademicMaster $academicMaster): bool
    {
        return $authUser->can('Replicate:AcademicMaster');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AcademicMaster');
    }
}
