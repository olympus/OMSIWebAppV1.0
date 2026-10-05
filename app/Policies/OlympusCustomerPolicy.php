<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OlympusCustomer;
use Illuminate\Auth\Access\HandlesAuthorization;

class OlympusCustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OlympusCustomer');
    }

    public function view(AuthUser $authUser, OlympusCustomer $olympusCustomer): bool
    {
        return $authUser->can('View:OlympusCustomer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OlympusCustomer');
    }

    public function update(AuthUser $authUser, OlympusCustomer $olympusCustomer): bool
    {
        return $authUser->can('Update:OlympusCustomer');
    }

    public function delete(AuthUser $authUser, OlympusCustomer $olympusCustomer): bool
    {
        return $authUser->can('Delete:OlympusCustomer');
    }

    public function restore(AuthUser $authUser, OlympusCustomer $olympusCustomer): bool
    {
        return $authUser->can('Restore:OlympusCustomer');
    }

    public function forceDelete(AuthUser $authUser, OlympusCustomer $olympusCustomer): bool
    {
        return $authUser->can('ForceDelete:OlympusCustomer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OlympusCustomer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OlympusCustomer');
    }

    public function replicate(AuthUser $authUser, OlympusCustomer $olympusCustomer): bool
    {
        return $authUser->can('Replicate:OlympusCustomer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OlympusCustomer');
    }
}
