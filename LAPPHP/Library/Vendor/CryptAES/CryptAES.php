<?php

class CryptAES
{
    protected $cipher = MCRYPT_RIJNDAEL_128;
    protected $mode = MCRYPT_MODE_ECB;
    protected $pad_method = 'pkcs5';
    protected $secret_key = '5824313879379126'; // 加密key
    protected $iv = '';

    public static function hex2bin($hexdata)
    {
        $bindata = '';
        $length = strlen($hexdata);
        for ($i = 0; $i < $length; $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }

    public static function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }

    public function set_cipher($cipher)
    {
        $this->cipher = $cipher;
    }

    public function set_mode($mode)
    {
        $this->mode = $mode;
    }

    public function set_iv($iv)
    {
        $this->iv = $iv;
    }


    //加密

    public function encrypt($str)
    {
        $str = $this->pad($str);
        $td = mcrypt_module_open($this->cipher, '', $this->mode, '');

        if (empty($this->iv)) {
            $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        } else {
            $iv = $this->iv;
        }

        mcrypt_generic_init($td, $this->secret_key, $iv);
        $cyper_text = mcrypt_generic($td, $str);
        $rt = base64_encode($cyper_text);
        //$rt = bin2hex($cyper_text);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $rt;
    }

    //解密

    protected function pad($str)
    {
        return $this->pad_or_unpad($str, '');
    }

    protected function pad_or_unpad($str, $ext)
    {
        if (is_null($this->pad_method)) {
            return $str;
        } else {
            $func_name = __CLASS__ . '::' . $this->pad_method . '_' . $ext . 'pad';
            if (is_callable($func_name)) {
                $size = mcrypt_get_block_size($this->cipher, $this->mode);
                return call_user_func($func_name, $str, $size);
            }
        }
        return $str;
    }

    public function decrypt($str)
    {
        $td = mcrypt_module_open($this->cipher, '', $this->mode, '');

        if (empty($this->iv)) {
            $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        } else {
            $iv = $this->iv;
        }

        mcrypt_generic_init($td, $this->secret_key, $iv);
        //$decrypted_text = mdecrypt_generic($td, self::hex2bin($str));
        $decrypted_text = mdecrypt_generic($td, base64_decode($str));
        $rt = $decrypted_text;
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $this->unpad($rt);
    }

    protected function unpad($str)
    {
        return $this->pad_or_unpad($str, 'un');
    }
}






//执行加密

//$aes=new CryptAES();
//
//echo  $encText = $aes->encrypt('UKQSERUKHTCRVTHWR66LSEL6OLPHCEW3MRSXH6GBQWZJVJY5Q6NRMGFYE2JJTPFB');
//echo  $encText = $aes->decrypt($encText);





//执行解密


function jiemi($method, $begin)
{
    $urlPath = strstr($_SERVER['REQUEST_URI'], $begin);   //url路径
    $urlPath = str_replace($begin, '', $urlPath);

    $aes = new CryptAES();

    $decString = $aes->decrypt($urlPath);//需要解密的url

    $url = explode('/', $decString);   // 解密出来的url

    if ($method == 'GET') {

        foreach ($url as $key => $value) {

            if ($key % 2 == 0) {
                $_GET["$value"] = $url[$key + 1];

            }

        }

        return $_GET;
    }
    if ($method == 'POST') {

        $decString = $aes->decrypt($_POST['key']);//需要解密的url
//
//        var_dump($decString);
        $url = explode('/', $decString);   // 解密出来的url
        foreach ($url as $key => $value) {
            if ($key % 2 == 0) {
                $_POST["$value"] = $url[$key + 1];
            }

        }
        return $_POST;


    }
    // return $decString;
}


/**
 * @param $str
 * @return string
 * 加密
 */
function jiami($str)
{

    $aes = new CryptAES();
    return $aes->encrypt($str);

}

function decString($str){
    $aes = new CryptAES();
    return $aes->decrypt("jwdDLSXiOMIGa2nnHWUGI+gtTz9R0EJNGlvy/vog/m/pLtzCNRvub1T7IfASUnQU5wJs5w9wfsigwZKD7rhaDybKl7KByWuVVO4mMaE4+ZY=");

}


?>
