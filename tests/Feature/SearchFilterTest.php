<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\User;
use tcCore\SearchFilter;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_user_can_store_a_search_filter(){
        $this->assertEquals(0,SearchFilter::count());
        $response = $this->post(
            '/search_filter', $this->getValidAttributes()
            
        )->assertSuccessful();
        $this->assertEquals(1,SearchFilter::count());
        $searchfilter = SearchFilter::first();
        $this->assertEquals('new filter', $searchfilter->name);
        $this->assertNotEmpty($searchfilter->uuid);
    }

    /** @test */
    public function a_new_search_field_must_have_a_name(){
        $attr = $this->getValidAttributes();
        unset($attr['name']);
        $response = $this->post(
            '/search_filter', $attr 
            
        )->assertStatus(422);
        $this->assertEquals('The name field is required.',$response->decodeResponseJson()['errors']['name'][0]);
    }


    /** @test */
    public function a_user_can_get_a_search_filter_list_for_a_given_key(){
        $user = \tcCore\User::where('username','=',static::USER_TEACHER)->get()->first();
        $key = 'itembank_toetsen';
        $filters = factory(SearchFilter::class,10)->create([    'user_id'=>$user->id,
                                                                'key'=> $key]);
        $response = $this->get(
            static::authTeacherOneGetRequest(
                'search_filter/itembank_toetsen',
                []
            )
        )->assertStatus(200);
        $this->assertEquals(10, count($response->decodeResponseJson()));
    }



    /** @test */
    public function a_user_can_get_his_own_search_filter_list_for_a_given_key(){
        $user = \tcCore\User::where('username','=',static::USER_TEACHER)->get()->first();
        $key = 'itembank_toetsen';
        $filters = factory(SearchFilter::class,10)->create([    'user_id'=>$user->id,
                                                                'key'=> $key]);
        $filters = factory(SearchFilter::class,10)->create([    'key'=> $key]);
        $response = $this->get(
            static::authTeacherOneGetRequest(
                'search_filter/itembank_toetsen',
                []
            )
        )->assertStatus(200);
        $this->assertEquals(10, count($response->decodeResponseJson()));
    }

    /** @test */
    public function a_user_can_get_right_search_filter_list_for_a_given_key(){
        $user = \tcCore\User::where('username','=',static::USER_TEACHER)->get()->first();
        $key = 'itembank_toetsen';
        $key2 = 'iets_anders';
        $filters = factory(SearchFilter::class,10)->create([    'user_id'=>$user->id,
                                                                'key'=> $key]);
        $filters = factory(SearchFilter::class,10)->create([    'user_id'=>$user->id,
                                                                'key'=> $key2]);
        $response = $this->get(
            static::authTeacherOneGetRequest(
                'search_filter/itembank_toetsen',
                []
            )
        )->assertStatus(200);
        $this->assertEquals(10, count($response->decodeResponseJson()));
    }

    /** @test */
    public function a_user_cannot_get_a_search_filter_list_without_a_given_key(){
        $user = \tcCore\User::where('username','=',static::USER_TEACHER)->get()->first();
        $key = 'itembank_toetsen';
        $filters = factory(SearchFilter::class,10)->create([    'user_id'=>$user->id,
                                                                'key'=> $key]);
        $response = $this->get(
            static::authTeacherOneGetRequest(
                'search_filter/',
                []
            )
        )->assertStatus(200);
        $this->assertEquals(0, count($response->decodeResponseJson()));
    }

    /** @test */
    public function a_user_can_update_a_search_filter(){
        $user = \tcCore\User::where('username','=',static::USER_TEACHER)->get()->first();
        $key = 'itembank_toetsen';
        $filter = factory(SearchFilter::class)->create([    'user_id'=>$user->id,
                                                                'key'=> $key]);
        $response = $this->put(
            '/search_filter/'.$filter->uuid, $this->getValidAttributes()
        )->assertSuccessful();
        $searchfilter = SearchFilter::whereUuid($filter->uuid)->first();
        $this->assertEquals('new filter', $searchfilter->name);
    }



    private function getValidAttributes($overrides = []){
        return static::getTeacherOneAuthRequestData(array_merge([
                'name'                   => 'new filter',
                'filters'                  => json_encode(['name'=>'iets','subject'=>'anders']),
                'key'               => 'itembank_toetsen',
                ],$overrides));
    }
    
}
