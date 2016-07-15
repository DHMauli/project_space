<?php
/*
function bcrypt_encode ( $email, $password, $rounds='08' )
{
    $string = hash_hmac ( "whirlpool", str_pad ( $password, strlen ( $password ) * 4, sha1 ( $email ), STR_PAD_BOTH ), SALT, true );
    $salt = substr ( str_shuffle ( './0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ) , 0, 22 );
    return crypt ( $string, '$2a$' . $rounds . '$' . $salt );
}
*/

	class ModulePassword
	{
		var $pepper = '5Hpc9p30kHcAY';

		public function bcrypt_encode($email, $password, $rounds='08')
		{
			$string = hash_hmac("whirlpool", str_pad($password, strlen($password) * 4, sha1($email), STR_PAD_BOTH), $this->pepper, true);
			$salt = substr(str_shuffle('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 22 );
			return crypt($string, '$2a$'.$rounds.'$'.$salt);
		}

		public function bcrypt_check($email, $password, $stored)
		{
			$string = hash_hmac("whirlpool", str_pad($password, strlen($password) * 4, sha1($email), STR_PAD_BOTH), $this->pepper, true);
			return $this->hash_compare(crypt($string, substr($stored, 0, 30)), $stored);
		}

	    public function hash_compare($a, $b)
	    {
	        if (!is_string($a) || !is_string($b)) {
	            return false;
			}
	       
	        $len = strlen($a);
	        if ($len !== strlen($b)) {
	            return false;
			}

	        $status = 0;
	        for ($i = 0; $i < $len; $i++) {
	            $status |= ord($a[$i]) ^ ord($b[$i]);
			}

	        return $status === 0;
	    }
	}
?>