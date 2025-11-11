<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class YoutubeURL implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $valid = true;
        $url = parse_url($value);
        if(isset($url['scheme']) && isset($url['host'])){
            if(!in_array($url['scheme'], ['https','http',null])){$valid = false;}
            if(!in_array($url['host'], ['www.youtube.com','youtube.com','youtu.be'])){$valid = false;}
        }else{
            $valid = false;
        }
        return $valid;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid Youtube URL.';
    }
}
