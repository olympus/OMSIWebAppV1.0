<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProductInfo;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductInfoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductInfo');
    }

    public function view(AuthUser $authUser, ProductInfo $productInfo): bool
    {
        return $authUser->can('View:ProductInfo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductInfo');
    }

    public function update(AuthUser $authUser, ProductInfo $productInfo): bool
    {
        return $authUser->can('Update:ProductInfo');
    }

    public function delete(AuthUser $authUser, ProductInfo $productInfo): bool
    {
        return $authUser->can('Delete:ProductInfo');
    }

    public function restore(AuthUser $authUser, ProductInfo $productInfo): bool
    {
        return $authUser->can('Restore:ProductInfo');
    }

    public function forceDelete(AuthUser $authUser, ProductInfo $productInfo): bool
    {
        return $authUser->can('ForceDelete:ProductInfo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductInfo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductInfo');
    }

    public function replicate(AuthUser $authUser, ProductInfo $productInfo): bool
    {
        return $authUser->can('Replicate:ProductInfo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductInfo');
    }
}
