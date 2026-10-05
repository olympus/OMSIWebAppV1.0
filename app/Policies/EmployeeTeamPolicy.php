<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EmployeeTeam;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeTeamPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmployeeTeam');
    }

    public function view(AuthUser $authUser, EmployeeTeam $employeeTeam): bool
    {
        return $authUser->can('View:EmployeeTeam');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmployeeTeam');
    }

    public function update(AuthUser $authUser, EmployeeTeam $employeeTeam): bool
    {
        return $authUser->can('Update:EmployeeTeam');
    }

    public function delete(AuthUser $authUser, EmployeeTeam $employeeTeam): bool
    {
        return $authUser->can('Delete:EmployeeTeam');
    }

    public function restore(AuthUser $authUser, EmployeeTeam $employeeTeam): bool
    {
        return $authUser->can('Restore:EmployeeTeam');
    }

    public function forceDelete(AuthUser $authUser, EmployeeTeam $employeeTeam): bool
    {
        return $authUser->can('ForceDelete:EmployeeTeam');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmployeeTeam');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmployeeTeam');
    }

    public function replicate(AuthUser $authUser, EmployeeTeam $employeeTeam): bool
    {
        return $authUser->can('Replicate:EmployeeTeam');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmployeeTeam');
    }
}
