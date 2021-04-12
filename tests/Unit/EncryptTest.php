<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\TestTake;
use Tests\TestCase;

class EncryptTest extends TestCase
{
    /** @test */
    public function testAesEncryption()
    {
        $plaintext = 'My secret message 1234';
        $plaintext = 'joepie';
        $password = '3sc3RLrpd17';

// CBC has an IV and thus needs randomness every time a message is encrypted
        $method = 'aes-256-cbc';

// Must be exact 32 chars (256 bit)
// You must store this secret random key in a safe place of your system.
        $key = substr(hash('sha256', $password, true), 0, 32);
        dump("Password:" . $password);

// Most secure key
//$key = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

// IV must be exact 16 chars (128 bit)
        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

// Most secure iv
// Never ever use iv=0 in real life. Better use this iv:
// $ivlen = openssl_cipher_iv_length($method);
// $iv = openssl_random_pseudo_bytes($ivlen);

// av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
        $encrypted = base64_encode(openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv));

// My secret message 1234
        $decrypted = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);

        dump('plaintext=' . $plaintext);
        dump('cipher=' . $method);
        dump('encrypted to: ' . $encrypted);
        dump('decrypted to: ' . $decrypted);
        dump(strlen($encrypted));
        $this->assertTrue(strlen($encrypted)>0);
        $this->assertTrue(strlen($encrypted)<50);
    }

}
