<?php

namespace FluentForm\App\Helpers;

class Protector
{
    /**
     * Get the salt for the encryption and decryption.
     */
    public static function getSalt()
    {
        $salt = get_option('_fluentform_security_salt');

        if (!$salt) {
            $salt = wp_generate_password();

            update_option('_fluentform_security_salt', $salt, 'no');
        }

        return $salt;
    }

    /**
     * Encryp a text using a predefined salt.
     *
     * @param string $text
     *
     * @return string $text
     */
    public static function encrypt($text)
    {
        $key = static::getSalt();

        $cipher = 'AES-128-CBC';

        $ivlen = openssl_cipher_iv_length($cipher);

        $iv = openssl_random_pseudo_bytes($ivlen);

        $ciphertext_raw = openssl_encrypt($text, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);

        $hmac = hash_hmac('sha256', $iv . $ciphertext_raw, $key, $as_binary = true);

        return base64_encode($iv . $hmac . $ciphertext_raw);
    }

    /**
     * Decrypt a text using a predefined salt.
     *
     * @param string $text
     *
     * @return string $text
     */
    public static function decrypt($text)
    {
        $key = static::getSalt();

        $c = base64_decode($text, true);

        $cipher = 'AES-128-CBC';

        $ivlen = openssl_cipher_iv_length($cipher);

        $sha2len = 32;

        if ($c === false || strlen($c) < $ivlen + $sha2len) {
            return null;
        }

        $iv = substr($c, 0, $ivlen);

        $hmac = substr($c, $ivlen, $sha2len);

        $ciphertext_raw = substr($c, $ivlen + $sha2len);

        $calcmac = hash_hmac('sha256', $iv . $ciphertext_raw, $key, $as_binary = true);

        if (!hash_equals($hmac, $calcmac)) {
            return null;
        }

        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);

        return $original_plaintext !== false ? $original_plaintext : null;
    }
}
