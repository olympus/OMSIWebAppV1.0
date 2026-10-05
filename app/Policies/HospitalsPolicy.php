<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Hospitals;
use Illuminate\Auth\Access\HandlesAuthorization;

class HospitalsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Hospitals');
    }

    public function view(AuthUser $authUser, Hospitals $hospitals): bool
    {
        return $authUser->can('View:Hospitals');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Hospitals');
    }

    public function update(AuthUser $authUser, Hospitals $hospitals): bool
    {
        return $authUser->can('Update:Hospitals');
    }

    public function delete(AuthUser $authUser, Hospitals $hospitals): bool
    {
        return $authUser->can('Delete:Hospitals');
    }

    public function restore(AuthUser $authUser, Hospitals $hospitals): bool
    {
        return $authUser->can('Restore:Hospitals');
    }

    public function forceDelete(AuthUser $authUser, Hospitals $hospitals): bool
    {
        return $authUser->can('ForceDelete:Hospitals');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Hospitals');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Hospitals');
    }

    public function replicate(AuthUser $authUser, Hospitals $hospitals): bool
    {
        return $authUser->can('Replicate:Hospitals');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Hospitals');
    }
}
