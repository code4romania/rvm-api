<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Cnp implements Rule
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
      
        if (strlen($value) === 13) {
         
            $cnp = array_map('intval',str_split($value));
           
			$coefs = [2, 7, 9, 1, 4, 6, 3, 5, 8, 2, 7, 9];
			$idx = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
			$s = array_map(function($x) use ($coefs, $cnp){
                return  $coefs[$x] * $cnp[$x];
            },$idx);
            $res = array_reduce($s, function($a, $b) {
                return $a + $b;
            }, 0) % 11;
			if (($res < 10 && $res == $cnp[12]) || ($res == 10 && $cnp[12] == 1) ) {
				return true;
			}
        }
        
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'CNP invalid.';
    }
}
