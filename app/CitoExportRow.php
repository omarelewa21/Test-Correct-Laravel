<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class CitoExportRow extends Model
{
    protected $guarded = [];

    protected $casts = [
        'answered_at' => 'datetime',
        'export' => 'boolean',
    ];

    public function getUserIdHash(): string
    {
        return $this->my_simple_crypt($this->attributes['user_id']);
    }

    public function getBrinHash(): string
    {
        return $this->my_simple_crypt($this->attributes['brin']);
    }

    /**
     * Encrypt and decrypt
     *
     * @author Nazmul Ahsan <n.mukto@gmail.com>
     * @link http://nazmulahsan.me/simple-two-way-function-encrypt-decrypt-string/
     *
     * @param string $string string to be encrypted/decrypted
     * @param string $action what to do with this? e for encrypt, d for decrypt
     */
    protected function my_simple_crypt( $string, $action = 'e' ) {
        // you may change these values to your own
        $secret_key = 'CqwF3ZgcVgnYkq3xateqb8vzpwKd5QFa';
        $secret_iv = 'RQTPFtp9ABkwVBycFX94VC9mdQvNzV8c';

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }

        return $output;
    }
}
