<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EnquiryMaster;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnquiryMasterPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EnquiryMaster');
    }

    public function view(AuthUser $authUser, EnquiryMaster $enquiryMaster): bool
    {
        return $authUser->can('View:EnquiryMaster');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EnquiryMaster');
    }

    public function update(AuthUser $authUser, EnquiryMaster $enquiryMaster): bool
    {
        return $authUser->can('Update:EnquiryMaster');
    }

    public function delete(AuthUser $authUser, EnquiryMaster $enquiryMaster): bool
    {
        return $authUser->can('Delete:EnquiryMaster');
    }

    public function restore(AuthUser $authUser, EnquiryMaster $enquiryMaster): bool
    {
        return $authUser->can('Restore:EnquiryMaster');
    }

    public function forceDelete(AuthUser $authUser, EnquiryMaster $enquiryMaster): bool
    {
        return $authUser->can('ForceDelete:EnquiryMaster');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EnquiryMaster');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EnquiryMaster');
    }

    public function replicate(AuthUser $authUser, EnquiryMaster $enquiryMaster): bool
    {
        return $authUser->can('Replicate:EnquiryMaster');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EnquiryMaster');
    }
}
