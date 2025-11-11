<?php

namespace App\Filament\Resources\OlympusCustomers\Pages;

use App\Filament\Resources\OlympusCustomers\OlympusCustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\StatusTimeline;
use App\NotifyCustomer;
use App\Services\SFDC;
use App\Events\RequestStatusUpdated;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use App\PasswordHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EditOlympusCustomer extends EditRecord
{
    protected static string $resource = OlympusCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            // DeleteAction::make(),
            // ForceDeleteAction::make(),
            // RestoreAction::make(),
        ];
    }

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

            // Get customer data for checks
            $customerEmailCheck = Customers::where('id', $id)->first();
            $first_name = strtolower($customerEmailCheck->first_name);
            $last_name = strtolower($customerEmailCheck->last_name);
            $email = strtolower($customerEmailCheck->email);
            $mobile_number = $customerEmailCheck->mobile_number;

            $parts = explode('@', $email);
            $namePart = $parts[0];

            $mobile_number_parts = explode('91', $mobile_number);
            $mobileamePart = $mobile_number_parts[1];

            $first_name_match = explode(' ', $first_name);
            $last_name_match = explode(' ', $last_name);

            // Check for name, email, phone in password
            $first_lat_name_flag = false;
            if (str_contains(strtolower($string), strtolower($first_name.$last_name))) {
                $first_lat_name_flag = true;
            }
            if ($first_lat_name_flag == true) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use name, email and phone number in password.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            $first_name_flag = false;
            foreach ($first_name_match as $first_name_matchs) {
                if (str_contains(strtolower($string), strtolower($first_name_matchs))) {
                    $first_name_flag = true;
                    break;
                }
            }
            if ($first_name_flag == true) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use name, email and phone number in password.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            $last_name_flag = false;
            foreach ($last_name_match as $last_name_matchs) {
                if (str_contains(strtolower($string), strtolower($last_name_matchs))) {
                    $last_name_flag = true;
                    break;
                }
            }
            if ($last_name_flag == true) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use name, email and phone number in password.')
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
                    ->body('You cannot use name, email and phone number in password.')
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
                    ->body('You cannot use name, email and phone number in password.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            $mobile_number_flag = false;
            if (str_contains($data['password'], $mobileamePart)) {
                $mobile_number_flag = true;
            }
            if ($mobile_number_flag == true) {
                Notification::make()
                    ->title('Password Validation Error')
                    ->body('You cannot use name, email and phone number in password.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            // Check last 5 passwords - always check if password is provided
            $customer = $this->record;
            $get_latest_password = PasswordHistory::where('customer_id', $customer->id)->orderBy('id', 'desc')->take(5)->get();
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
                $total_password = PasswordHistory::where('customer_id', $customer->id)->count();
                if ($total_password >= 5) {
                    $old_pass_delete = PasswordHistory::where('customer_id', $customer->id)
                        ->orderBy('created_at', 'desc')
                        ->skip(4) // Keep the latest 4, delete the rest
                        ->get();
                    foreach ($old_pass_delete as $old_pass_deletes) {
                        PasswordHistory::where('id', $old_pass_deletes->id)->delete();
                    }
                }

                // Insert new password history
                $pass = new PasswordHistory();
                $pass->customer_id = $customer->id;
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
}
