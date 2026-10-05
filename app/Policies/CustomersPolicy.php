<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Customers;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomersPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Customers');
    }

    public function view(AuthUser $authUser, Customers $customers): bool
    {
        return $authUser->can('View:Customers');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Customers');
    }

    public function update(AuthUser $authUser, Customers $customers): bool
    {
        return $authUser->can('Update:Customers');
    }

    public function delete(AuthUser $authUser, Customers $customers): bool
    {
        return $authUser->can('Delete:Customers');
    }

    public function restore(AuthUser $authUser, Customers $customers): bool
    {
        return $authUser->can('Restore:Customers');
    }

    public function forceDelete(AuthUser $authUser, Customers $customers): bool
    {
        return $authUser->can('ForceDelete:Customers');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Customers');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Customers');
    }

    public function replicate(AuthUser $authUser, Customers $customers): bool
    {
        return $authUser->can('Replicate:Customers');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Customers');
    }
}
