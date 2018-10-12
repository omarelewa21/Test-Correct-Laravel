<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/**
 * Temporary Mass Generation Code
 */
/*$lines = array(
	'AnswerRating' => array(
		'upperCamelCasePlural' => 'AnswerRatings',
		'underscorePlural' => 'answer_ratings',
		'underscoreSingular' => 'answer_rating'
	),
	'Answer' => array(
		'upperCamelCasePlural' => 'Answers',
		'underscorePlural' => 'answers',
		'underscoreSingular' => 'answer'
	),
	'Attachment' => array(
		'upperCamelCasePlural' => 'Attachments',
		'underscorePlural' => 'attachments',
		'underscoreSingular' => 'attachment'
	),
	'CompletionQuestionAnswer' => array(
		'upperCamelCasePlural' => 'CompletionQuestionAnswers',
		'underscorePlural' => 'completion_question_answers',
		'underscoreSingular' => 'completion_question_answer'
	),
	'CompletionQuestion' => array(
		'upperCamelCasePlural' => 'CompletionQuestions',
		'underscorePlural' => 'completion_questions',
		'underscoreSingular' => 'completion_question'
	),
	'DatabaseQuestion' => array(
		'upperCamelCasePlural' => 'DatabaseQuestions',
		'underscorePlural' => 'database_questions',
		'underscoreSingular' => 'database_question'
	),
	'EducationLevel' => array(
		'upperCamelCasePlural' => 'EducationLevels',
		'underscorePlural' => 'education_levels',
		'underscoreSingular' => 'education_level'
	),
	'License' => array(
		'upperCamelCasePlural' => 'Licenses',
		'underscorePlural' => 'licenses',
		'underscoreSingular' => 'license'
	),
	'MatchingQuestionAnswer' => array(
		'upperCamelCasePlural' => 'MatchingQuestionAnswers',
		'underscorePlural' => 'matching_question_answers',
		'underscoreSingular' => 'matching_question_answer'
	),
	'MatchingQuestion' => array(
		'upperCamelCasePlural' => 'MatchingQuestions',
		'underscorePlural' => 'matching_questions',
		'underscoreSingular' => 'matching_question'
	),
	'MultipleChoiceQuestionAnswer' => array(
		'upperCamelCasePlural' => 'MultipleChoiceQuestionAnswers',
		'underscorePlural' => 'multiple_choice_question_answers',
		'underscoreSingular' => 'multiple_choice_question_answer'
	),
	'MultipleChoiceQuestion' => array(
		'upperCamelCasePlural' => 'MultipleChoiceQuestions',
		'underscorePlural' => 'multiple_choice_questions',
		'underscoreSingular' => 'multiple_choice_question'
	),
	'OpenQuestion' => array(
		'upperCamelCasePlural' => 'OpenQuestions',
		'underscorePlural' => 'open_questions',
		'underscoreSingular' => 'open_question'
	),
	'Period' => array(
		'upperCamelCasePlural' => 'Periods',
		'underscorePlural' => 'periods',
		'underscoreSingular' => 'period'
	),
	'QuestionGroup' => array(
		'upperCamelCasePlural' => 'QuestionGroups',
		'underscorePlural' => 'question_groups',
		'underscoreSingular' => 'question_group'
	),
	'Question' => array(
		'upperCamelCasePlural' => 'Questions',
		'underscorePlural' => 'questions',
		'underscoreSingular' => 'question'
	),
	'RankingQuestionAnswer' => array(
		'upperCamelCasePlural' => 'RankingQuestionAnswers',
		'underscorePlural' => 'ranking_question_answers',
		'underscoreSingular' => 'ranking_question_answer'
	),
	'RankingQuestion' => array(
		'upperCamelCasePlural' => 'RankingQuestions',
		'underscorePlural' => 'ranking_questions',
		'underscoreSingular' => 'ranking_question'
	),
	'Role' => array(
		'upperCamelCasePlural' => 'Roles',
		'underscorePlural' => 'roles',
		'underscoreSingular' => 'role'
	),
	//'SchoolClass' => array(
	//	'upperCamelCasePlural' => 'SchoolClasses',
	//	'underscorePlural' => 'school_classes',
	//	'underscoreSingular' => 'school_class'
	//),
	'SalesOrganisation' => array(
		'upperCamelCasePlural' => 'SalesOrganisations',
		'underscorePlural' => 'sales_organisations',
		'underscoreSingular' => 'sales_organisation'
	),
	'SchoolLocation' => array(
		'upperCamelCasePlural' => 'SchoolLocations',
		'underscorePlural' => 'school_locations',
		'underscoreSingular' => 'school_location'
	),
	'SchoolYear' => array(
		'upperCamelCasePlural' => 'SchoolYears',
		'underscorePlural' => 'school_years',
		'underscoreSingular' => 'school_year'
	),
	'School' => array(
		'upperCamelCasePlural' => 'Schools',
		'underscorePlural' => 'schools',
		'underscoreSingular' => 'school'
	),
	'Section' => array(
		'upperCamelCasePlural' => 'Sections',
		'underscorePlural' => 'sections',
		'underscoreSingular' => 'section'
	),
	'Student' => array(
		'upperCamelCasePlural' => 'Students',
		'underscorePlural' => 'students',
		'underscoreSingular' => 'student'
	),
	'Subject' => array(
		'upperCamelCasePlural' => 'Subjects',
		'underscorePlural' => 'subjects',
		'underscoreSingular' => 'subject'
	),
	'Tag' => array(
		'upperCamelCasePlural' => 'Tags',
		'underscorePlural' => 'tags',
		'underscoreSingular' => 'tag'
	),
	'TagsRelation' => array(
		'upperCamelCasePlural' => 'TagsRelations',
		'underscorePlural' => 'tags_relations',
		'underscoreSingular' => 'tags_relation'
	),
	'Teacher' => array(
		'upperCamelCasePlural' => 'Teachers',
		'underscorePlural' => 'teachers',
		'underscoreSingular' => 'teacher'
	),
	'TestParticipant' => array(
		'upperCamelCasePlural' => 'TestParticipants',
		'underscorePlural' => 'test_participants',
		'underscoreSingular' => 'test_participant'
	),
	'TestRatingParticipant' => array(
		'upperCamelCasePlural' => 'TestRatingParticipants',
		'underscorePlural' => 'test_rating_participants',
		'underscoreSingular' => 'test_rating_participant'
	),
	'TestRating' => array(
		'upperCamelCasePlural' => 'TestRatings',
		'underscorePlural' => 'test_ratings',
		'underscoreSingular' => 'test_rating'
	),
	'TestTakeGroup' => array(
		'upperCamelCasePlural' => 'TestTakeGroups',
		'underscorePlural' => 'test_take_groups',
		'underscoreSingular' => 'test_take_group'
	),
	'TestTakeStatus' => array(
		'upperCamelCasePlural' => 'TestTakeStatuses',
		'underscorePlural' => 'test_take_statuses',
		'underscoreSingular' => 'test_take_status'
	),
	'TestTake' => array(
		'upperCamelCasePlural' => 'TestTakes',
		'underscorePlural' => 'test_takes',
		'underscoreSingular' => 'test_take'
	),
	'Test' => array(
		'upperCamelCasePlural' => 'Tests',
		'underscorePlural' => 'tests',
		'underscoreSingular' => 'test'
	),
	'UmbrellaOrganization' => array(
		'upperCamelCasePlural' => 'UmbrellaOrganizations',
		'underscorePlural' => 'umbrella_organizations',
		'underscoreSingular' => 'umbrella_organization'
	),
	'UserRole' => array(
		'upperCamelCasePlural' => 'UserRoles',
		'underscorePlural' => 'user_roles',
		'underscoreSingular' => 'user_role'
	),
	'User' => array(
		'upperCamelCasePlural' => 'Users',
		'underscorePlural' => 'users',
		'underscoreSingular' => 'user'
	)
);
?><h1>Commands</h1><?php
foreach($lines as $upperCamelCaseSingular => $otherForms) {
	extract($otherForms);
	$lowerCamelCasePlural = lcfirst($upperCamelCasePlural);
	$lowerCamelCaseSingular = lcfirst($upperCamelCaseSingular);
	$lowerSpacedPlural = str_replace('_', ' ', $underscorePlural);
	$lowerSpacedSingular = str_replace('_', ' ', $underscoreSingular);
	$upperSpacedPlural = ucfirst($lowerSpacedPlural);
	$upperSpacedSingular = ucfirst($lowerSpacedSingular);

	foreach(array(
				'app/SchoolClass.php' => 'app/'.$upperCamelCaseSingular.'.php',
				'app/Http/Requests/CreateSchoolClassRequest.php' => 'app/Http/Requests/Create'.$upperCamelCaseSingular.'Request.php',
				'app/Http/Requests/UpdateSchoolClassRequest.php' => 'app/Http/Requests/Update'.$upperCamelCaseSingular.'Request.php',
				'app/Http/Controllers/SchoolClassesController.php' => 'app/Http/Controllers/'.$upperCamelCasePlural.'Controller.php',
			) as $input => $output) {
		if ($output === 'app/User.php') {
			continue;
		}
		echo 'sed -e \'s/SchoolClasses/'.$upperCamelCasePlural.'/g\' '.$input.' | sed -e \'s/SchoolClass/'.$upperCamelCaseSingular.'/g\' | sed -e \'s/school classes/'.$lowerSpacedPlural.'/g\' | sed -e \'s/school class/'.$lowerSpacedSingular.'/g\' | sed -e \'s/School classes/'.$upperSpacedPlural.'/g\' | sed -e \'s/School class/'.$upperSpacedSingular.'/g\' | sed -e \'s/schoolClasses/'.$lowerCamelCasePlural.'/g\' | sed -e \'s/schoolClass/'.$lowerCamelCaseSingular.'/g\' | sed -e \'s/school_classes/'.$underscorePlural.'/g\' | sed -e \'s/school_class/'.$underscoreSingular.'/g\' > '.$output.';'."<br/>\n";
	}
}
?><h1>Round Binds</h1><pre><?php
foreach($lines as $upperCamelCaseSingular => $otherForms) {
	extract($otherForms);
	$lowerCamelCasePlural = lcfirst($upperCamelCasePlural);
	$lowerCamelCaseSingular = lcfirst($upperCamelCaseSingular);
	$lowerSpacedPlural = str_replace('_', ' ', $underscorePlural);
	$lowerSpacedSingular = str_replace('_', ' ', $underscoreSingular);
	$upperSpacedPlural = ucfirst($lowerSpacedPlural);
	$upperSpacedSingular = ucfirst($lowerSpacedSingular);

	echo 'Route::bind(\''.$underscoreSingular.'\', function($id) {'."\n";
	echo '	return tcCore\\'.$upperCamelCaseSingular.'::findOrFail($id);'."\n";
	echo '});'."\n";
	echo "\n";
}
?></pre><h1>Routes</h1><?php

die();*/
/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can simply call the run method,
| which will execute the request and send the response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

$response = $kernel->handle(
	$request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
