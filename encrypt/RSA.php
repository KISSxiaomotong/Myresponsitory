<?php

class RSA
{
    /**
    生成私钥文件:
    openssl genrsa -out rsa_private_key.pem 1024
     *
    利用私钥，生成公钥:
    openssl rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem
     * 
    通过生成的公钥和私钥就可以对数据进行加解密：
     */

    /**
     * @param string $str 要加密的数据
     * @param string $public_key 公钥
     * @return string
     */
    static public function encrypt($str, $public_key) {
        $encrypted = '';
        $pu_key = openssl_pkey_get_public($public_key);
        openssl_public_encrypt($str, $encrypted, $pu_key);//公钥加密
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

    /**
     * 解密
     * @param string $str 要解密的数据
     * @param string $private_key 私钥
     * @return string
     */
    static public function decrypt($str, $private_key) {
        $decrypted = '';
        $pi_key =  openssl_pkey_get_private($private_key);
        openssl_private_decrypt(base64_decode($str), $decrypted, $pi_key);//私钥解密
        return $decrypted;
    }
}
