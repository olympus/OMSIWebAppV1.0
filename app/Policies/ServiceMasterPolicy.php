<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ServiceMaster;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceMasterPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ServiceMaster');
    }

    public function view(AuthUser $authUser, ServiceMaster $serviceMaster): bool
    {
        return $authUser->can('View:ServiceMaster');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ServiceMaster');
    }

    public function update(AuthUser $authUser, ServiceMaster $serviceMaster): bool
    {
        return $authUser->can('Update:ServiceMaster');
    }

    public function delete(AuthUser $authUser, ServiceMaster $serviceMaster): bool
    {
        return $authUser->can('Delete:ServiceMaster');
    }

    public function restore(AuthUser $authUser, ServiceMaster $serviceMaster): bool
    {
        return $authUser->can('Restore:ServiceMaster');
    }

    public function forceDelete(AuthUser $authUser, ServiceMaster $serviceMaster): bool
    {
        return $authUser->can('ForceDelete:ServiceMaster');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ServiceMaster');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ServiceMaster');
    }

    public function replicate(AuthUser $authUser, ServiceMaster $serviceMaster): bool
    {
        return $authUser->can('Replicate:ServiceMaster');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ServiceMaster');
    }
}
