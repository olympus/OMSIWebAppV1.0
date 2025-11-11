<?php

namespace App\Filament\Pages;

use App\Autoemail_Setting;
use App\Reportsetting;
use App\SettingModel;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\{
    AutoEmails,
    Customers,
    EmployeeTeam,
    User
};

class CheckEmail extends Page implements Forms\Contracts\HasForms
{

    use Forms\Concerns\InteractsWithForms;

    protected string $view = 'filament.pages.check-email';
    protected static ?string $navigationLabel = 'Check Email';
    protected static ?string $slug = 'check-email';
    protected static ?string $title = 'Check Email';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-envelope';
    protected static ?int $navigationSort = 1;
    protected static ?int $navigationGroupSort = 5;

    // protected static string|UnitEnum|null $navigationGroup = 'Tools';

    public ?string $email = null;
    public ?string $message = null;
    public array $errorsList = [];

    public function submit()
    {
        $email = $this->email;

        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email|regex:/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i']
        );

        if ($validator->fails()) {
            $this->errorsList = [$validator->messages()->first()];
            $this->message = null;
            return;
        }

        $email_q = "%{$email}%";

        $config = substr_count(file_get_contents(base_path('config/oly.php')), $email);
        $autoemail_settings = Autoemail_Setting::where('user_email', 'LIKE', $email_q)->count();
        $autoemaillist = AutoEmails::where('to_emails', 'LIKE', $email_q)
            ->orWhere('cc_emails', 'LIKE', $email_q)->count();
        $customers = Customers::where('email', 'LIKE', $email_q)->count();
        $employee_team = EmployeeTeam::where('escalation_1', 'LIKE', $email_q)
            ->orWhere('escalation_2', 'LIKE', $email_q)
            ->orWhere('escalation_3', 'LIKE', $email_q)
            ->orWhere('escalation_4', 'LIKE', $email_q)->count();
        $employee_enabled = EmployeeTeam::where('email', 'LIKE', $email_q)->value('disabled');
        $reportsettings = Reportsetting::where('to_emails', 'LIKE', $email_q)
            ->orWhere('cc_emails', 'LIKE', $email_q)->count();
        $settings = SettingModel::where('value', 'LIKE', $email_q)->count();
        $users = User::where('email', 'LIKE', $email_q)->count();
        $service_requests = EmployeeTeam::join('service_requests', 'service_requests.employee_code', '=', 'employee_team.employee_code')
            ->where('email', $email)
            ->where('status', '!=', 'Closed')
            ->count();

        $errors = [];
        if (!empty($config)) $errors[] = "$config found in configuration file.";
        if (!empty($autoemail_settings)) $errors[] = "$autoemail_settings found in autoemail_settings.";
        if (!empty($autoemaillist)) $errors[] = "$autoemaillist found in autoemaillist.";
        if (!empty($customers)) $errors[] = "$customers found in customers.";
        if ($employee_enabled == "0") $errors[] = "$email is still enabled.";
        if (!empty($employee_team)) $errors[] = "$employee_team found in employee_team as escalation.";
        if (!empty($reportsettings)) $errors[] = "$reportsettings found in reportsettings.";
        if (!empty($settings)) $errors[] = "$settings found in settings.";
        if (!empty($users)) $errors[] = "$users found in users.";
        if (!empty($service_requests)) $errors[] = "$service_requests unclosed service_requests.";

        if ($errors) {
            $this->errorsList = $errors;
            $this->message = null;
        } else {
            $this->message = "No occurrences found for {$email}.";
            $this->errorsList = [];
        }
    }
}
