<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RoiCalculator;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoiCalculatorPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RoiCalculator');
    }

    public function view(AuthUser $authUser, RoiCalculator $roiCalculator): bool
    {
        return $authUser->can('View:RoiCalculator');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RoiCalculator');
    }

    public function update(AuthUser $authUser, RoiCalculator $roiCalculator): bool
    {
        return $authUser->can('Update:RoiCalculator');
    }

    public function delete(AuthUser $authUser, RoiCalculator $roiCalculator): bool
    {
        return $authUser->can('Delete:RoiCalculator');
    }

    public function restore(AuthUser $authUser, RoiCalculator $roiCalculator): bool
    {
        return $authUser->can('Restore:RoiCalculator');
    }

    public function forceDelete(AuthUser $authUser, RoiCalculator $roiCalculator): bool
    {
        return $authUser->can('ForceDelete:RoiCalculator');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RoiCalculator');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RoiCalculator');
    }

    public function replicate(AuthUser $authUser, RoiCalculator $roiCalculator): bool
    {
        return $authUser->can('Replicate:RoiCalculator');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RoiCalculator');
    }
}
