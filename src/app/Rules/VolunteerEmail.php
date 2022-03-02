<?php

namespace App\Rules;
use App\Volunteer;

use Illuminate\Contracts\Validation\Rule;

class VolunteerEmail implements Rule
{
    private $cnp;
    /**
     * Create a new rule instance.
     *
     * @param  string  $cnp
     *
     * @return void
     */
    public function __construct($cnp = "")
    {
        $this->cnp = $cnp;
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
        /** The email address should be unique unless the cnp is the same with an existing volunteer */
        $exist = Volunteer::query()->where('email', '=', $value)->first();
        if($exist){
            if($exist->ssn == $this->cnp){
                return true;
            }
            return false;
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
        return 'The email has already been taken.';
    }
}
