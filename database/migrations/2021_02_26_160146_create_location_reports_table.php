<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('location_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('location_id')->nullable();
            $table->integer('nr_licenses')->nullable();                                      
            $table->integer('nr_activated_licenses')->nullable();                      
            $table->integer('nr_browsealoud_licenses')->nullable();               
            $table->integer('nr_approved_test_files_7')->nullable();                
            $table->integer( 'nr_approved_test_files_30')->nullable();                
            $table->integer('nr_approved_test_files_60')->nullable();               
            $table->integer('nr_approved_test_files_90')->nullable();               
            $table->integer('total_approved_test_files')->nullable();                 
            $table->integer('nr_added_question_items_7')->nullable();              
            $table->integer('nr_added_question_items_30')->nullable();           
            $table->integer( 'nr_added_question_items_60')->nullable();            
            $table->integer('nr_added_question_items_90')->nullable();          
            $table->integer('total_added_question_items_files')->nullable();      
            $table->integer('nr_approved_classes_7')->nullable();                     
            $table->integer( 'nr_approved_classes_30')->nullable();              
            $table->integer('nr_approved_classes_60')->nullable();                  
            $table->integer('nr_approved_classes_90')->nullable();                   
            $table->integer('total_approved_classes')->nullable();                
            $table->integer( 'nr_tests_taken_7')->nullable();                            
            $table->integer('nr_tests_taken_30')->nullable();                          
            $table->integer('nr_tests_taken_60')->nullable();                           
            $table->integer('nr_tests_taken_90')->nullable();                             
            $table->integer( 'total_tests_taken')->nullable();                               
            $table->integer('nr_tests_checked_7')->nullable();                            
            $table->integer('nr_tests_checked_30')->nullable();                          
            $table->integer('nr_tests_checked_60')->nullable();                      
            $table->integer('nr_tests_checked_90')->nullable();                         
            $table->integer('total_tests_checked')->nullable();                          
            $table->integer('nr_tests_rated_7')->nullable();                             
            $table->integer('nr_tests_rated_30')->nullable();                             
            $table->integer('nr_tests_rated_60')->nullable();                          
            $table->integer('nr_tests_rated_90')->nullable();                             
            $table->integer('total_tests_rated')->nullable();                                
            $table->integer('nr_colearning_sessions_7')->nullable();                     
            $table->integer('nr_colearning_sessions_30')->nullable();                
            $table->integer('nr_colearning_sessions_60')->nullable();                   
            $table->integer('nr_colearning_sessions_90')->nullable();                   
            $table->integer('total_colearning_sessions')->nullable();                    
            $table->integer('in_browser_tests_allowed')->nullable();                  
            $table->integer('nr_active_teachers')->nullable();    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_reports');
    }
}
