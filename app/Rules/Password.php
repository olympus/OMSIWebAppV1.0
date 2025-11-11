<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    private $name;
    private $email;

    public function __construct($name, $email)
    {
        $this->name = strtolower(str_replace(' ', '', $name));
        $this->email = strtolower($email);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check minimum length
        if (strlen($value) < 20) {
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }

        // Check for at least one digit
        if (!preg_match('/[0-9]/', $value)) {
            return false;
        }

        // Check for at least one special character
        if (!preg_match('/[#?!@$%^&*-]/', $value)) {
            return false;
        }

        // Check for no spaces
        if (str_contains($value, ' ')) {
            return false;
        }   

        $password = strtolower(preg_replace("/[^a-zA-Z]+/", "", $value));
        $string = $password;
        $blacklistArray = ['abc', 'bcd', 'cde', 'def', 'efg', 'fgh', 'ghi', 'hij', 'Ijk', 'jkl', 'klm', 'lmn', 'mno', 'nop', 'opq', 'pqr', 'qrs', 'rst', 'stu', 'tuv', 'uvw', 'vwx', 'wxy', 'xyz', 'yza', 'zab','abc','ABC', 'BCD', 'CDE', 'DEF', 'EFG', 'FGH', 'GHI', 'HIJ', 'IJK', 'JKL', 'KLM', 'LMN', 'MNO', 'NOP', 'OPQ', 'PQR', 'QRS', 'RST', 'STU', 'TUV', 'UVW', 'VWX', 'WXY', 'XYZ', 'YZA', 'ZAB','ABC'];
        foreach ($blacklistArray as $k => $v) {
            if (str_contains($string, $v)) {
                return false;
            }
        }

        $parts = explode('@', $this->email);
        $namePart = $parts[0];

        $first_name_match = explode(' ', $this->name);

        foreach($first_name_match as $first_name_matchs){
            if(str_contains(strtolower($string), strtolower($first_name_matchs))){
                return false;
            }
        }

        if(str_contains(strtolower($string), strtolower($this->name))){
            return false;
        }

        if(str_contains(strtolower($value), strtolower($this->email))){
            return false;
        }

        if(str_contains(strtolower($value), strtolower($namePart))){
            return false;
        }

        $chk_email_rule = preg_split("/[?&@#.]/", $namePart);

        foreach($chk_email_rule as $chk_email_rules){
            if(str_contains(strtolower($string), strtolower($chk_email_rules))){
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid password. Password should be in minimum 20 length characters and should contain at least one uppercase letter, one lowercase letter, one number and one special character. Also, password should not contain 3 sequence alphabetic characters. For eg: abc, bcd etc. You can not use name and email in password.';
    }
}
