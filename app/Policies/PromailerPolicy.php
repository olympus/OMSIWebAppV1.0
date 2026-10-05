<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Promailer;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromailerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Promailer');
    }

    public function view(AuthUser $authUser, Promailer $promailer): bool
    {
        return $authUser->can('View:Promailer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Promailer');
    }

    public function update(AuthUser $authUser, Promailer $promailer): bool
    {
        return $authUser->can('Update:Promailer');
    }

    public function delete(AuthUser $authUser, Promailer $promailer): bool
    {
        return $authUser->can('Delete:Promailer');
    }

    public function restore(AuthUser $authUser, Promailer $promailer): bool
    {
        return $authUser->can('Restore:Promailer');
    }

    public function forceDelete(AuthUser $authUser, Promailer $promailer): bool
    {
        return $authUser->can('ForceDelete:Promailer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Promailer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Promailer');
    }

    public function replicate(AuthUser $authUser, Promailer $promailer): bool
    {
        return $authUser->can('Replicate:Promailer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Promailer');
    }
}
