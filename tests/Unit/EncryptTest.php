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
use tcCore\User;
use Tests\TestCase;

class EncryptTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

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
 $ivlen = openssl_cipher_iv_length($method);
 $iv = openssl_random_pseudo_bytes($ivlen);
dump($iv);
// av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
        $encrypted = base64_encode(openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv));

// My secret message 1234
        $decrypted = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);

        dump('plaintext=' . $plaintext);
        dump('cipher=' . $method);
        dump('encrypted to: ' . $encrypted);
        dump('decrypted to: ' . $decrypted);
        dump($iv);
        dump(strlen($encrypted));
        $this->assertTrue(strlen($encrypted)>0);
        $this->assertTrue(strlen($encrypted)<50);
    }

    /** @test */
    public function after_create_a_teacher_has_a_encrypted_eck_id()
    {
        $data =[
            'school_location_id' => '2',
            'name_first' => 'a',
            'name_suffix' => '',
            'name' => 'bc',
            'abbreviation' => 'abcc',
            'username' => 'abc@test-correct.nl',
            'password' => 'aa',
            'external_id' => 'abc',
            'note' => '',
            'user_roles' => [1],
            'eckid' => 'hoi'
        ];

        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        //dump($response->getContent());
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $user = User::find($rData['id']);
        dump($user->eckid);
        dump($user->eckidFromRelation);
        //$this->assertEquals('hoi',$user->eckid);
        $row = \DB::table('eckid_user')->where('user_id',$user->id)->first();
        dump($row);
        $this->assertNotEquals('hoi',$row->eckid);
    }

}
