<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RoiCalculatorSection;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoiCalculatorSectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RoiCalculatorSection');
    }

    public function view(AuthUser $authUser, RoiCalculatorSection $roiCalculatorSection): bool
    {
        return $authUser->can('View:RoiCalculatorSection');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RoiCalculatorSection');
    }

    public function update(AuthUser $authUser, RoiCalculatorSection $roiCalculatorSection): bool
    {
        return $authUser->can('Update:RoiCalculatorSection');
    }

    public function delete(AuthUser $authUser, RoiCalculatorSection $roiCalculatorSection): bool
    {
        return $authUser->can('Delete:RoiCalculatorSection');
    }

    public function restore(AuthUser $authUser, RoiCalculatorSection $roiCalculatorSection): bool
    {
        return $authUser->can('Restore:RoiCalculatorSection');
    }

    public function forceDelete(AuthUser $authUser, RoiCalculatorSection $roiCalculatorSection): bool
    {
        return $authUser->can('ForceDelete:RoiCalculatorSection');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RoiCalculatorSection');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RoiCalculatorSection');
    }

    public function replicate(AuthUser $authUser, RoiCalculatorSection $roiCalculatorSection): bool
    {
        return $authUser->can('Replicate:RoiCalculatorSection');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RoiCalculatorSection');
    }
}
