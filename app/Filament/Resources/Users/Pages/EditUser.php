<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\AdminPasswordHistory;
use Carbon\Carbon;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Custom password validation logic
        if (isset($data['password'])) {
            $request = request();
            $id = $this->record->id;

            // Check for white space
            if (str_contains($data['password'], ' ')) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use any white space in password.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            // Extract alphabetic characters for blacklist check
            $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $data['password']));
            $string = $password;
            $blacklistArray = ['abc', 'bcd', 'cde', 'def', 'efg', 'fgh', 'ghi', 'hij', 'Ijk', 'jkl', 'klm', 'lmn', 'mno', 'nop', 'opq', 'pqr', 'qrs', 'rst', 'stu', 'tuv', 'uvw', 'vwx', 'wxy', 'xyz', 'yza', 'zab','abc','ABC', 'BCD', 'CDE', 'DEF', 'EFG', 'FGH', 'GHI', 'HIJ', 'IJK', 'JKL', 'KLM', 'LMN', 'MNO', 'NOP', 'OPQ', 'PQR', 'QRS', 'RST', 'STU', 'TUV', 'UVW', 'VWX', 'WXY', 'XYZ', 'YZA', 'ZAB','ABC'];
            $flag = false;
            foreach ($blacklistArray as $k => $v) {
                if (str_contains($string, $v)) {
                    $flag = true;
                    break;
                }
            }
            if ($flag == true) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('Password should not contain 3 sequence alphabetic characters. For eg: abc, bcd etc.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            // Get user data for checks
            $userEmailCheck = \App\Models\User::where('id', $id)->first();
            $name = strtolower($userEmailCheck->name);
            $email = strtolower($userEmailCheck->email);

            $parts = explode('@', $email);
            $namePart = $parts[0];

            $name_match = explode(' ', $name);

            // Check for name, email in password
            $name_flag = false;
            foreach ($name_match as $name_matchs) {
                if (str_contains(strtolower($string), strtolower($name_matchs))) {
                    $name_flag = true;
                    break;
                }
            }
            if ($name_flag == true) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use name and email in password.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            $email_flag = false;
            if (str_contains(strtolower($data['password']), strtolower($email))) {
                $email_flag = true;
            }
            if ($email_flag == true) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use name and email in password.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            $email_flag_start = false;
            if (str_contains(strtolower($data['password']), strtolower($namePart))) {
                $email_flag_start = true;
            }
            if ($email_flag_start == true) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use name and email in password.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            // Check last 5 passwords - always check if password is provided
            $user = $this->record;
            $get_latest_password = AdminPasswordHistory::where('user_id', $user->id)->orderBy('id', 'desc')->take(5)->get();
            foreach ($get_latest_password as $get_latest_passwords) {
                if (Hash::check($data['password'], $get_latest_passwords->password)) {
                    Notification::make()
                        ->title('Password Validation Error')
                        ->body('You cannot use last 5 passwords.')
                        ->danger()
                        ->send();
                    $this->halt();
                }
            }

            // If password is changed, hash it and handle history
            if (Hash::check($data['password'], $this->record->password) === false) {
                // Hash the password
                $data['password'] = bcrypt($data['password']);
                $data['password_updated_at'] = Carbon::now();
                $data['is_expired'] = 0;

                // Handle password history (keep only last 5)
                $total_password = AdminPasswordHistory::where('user_id', $user->id)->count();
                if ($total_password >= 5) {
                    $old_pass_delete = AdminPasswordHistory::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->skip(4) // Keep the latest 4, delete the rest
                        ->get();
                    foreach ($old_pass_delete as $old_pass_deletes) {
                        AdminPasswordHistory::where('id', $old_pass_deletes->id)->delete();
                    }
                }

                // Insert new password history
                $pass = new AdminPasswordHistory();
                $pass->user_id = $user->id;
                $pass->password = bcrypt($data['password']);
                $pass->save();
            } else {
                // If password not changed, remove it from data to avoid updating
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use last 5 passwords.')
                    ->danger()
                    ->send();
                $this->halt();
                unset($data['password']);
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $roles = $this->form->getState()['roles'] ?? [];
        Log::info('User roles saved:', $roles);
    }

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
