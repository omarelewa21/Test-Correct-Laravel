
# TestTake Scenario Factories

Here you will find TestTake Scenario Factories, which can be used to quickly set-up a testing enviroment.

## Usage for creating a school with one TestTake for each TestTakeStatus

```php  
$teacherUser = FactoryScenarioSchool001::create()->getTeachers()->first();

FactoryScenarioTestTakeAllStatuses::create($teacherUser);
```

## Usage for creating a school
Using the following Scenario Factories you can create a complete school enviroment. 
To setup a scenario that fits your needs the best, select the right scenario from below.

To set up a school to test TestTakes, without adding unnecesairy complexity with shared sections and teachers that belong to multiple schools, 
you can create" *FactoryScenarioSchoolSimple* for instance.

To set up a school to Test the 'toetsenbank', and see if a teacher can see the tests of a collegue from another school location that shares a section with his school location, 
you can create *FactoryScenarioSchool001*. 

#### SchoolScenario 001, based on testscenario document:
*'welke toetsen kan een docent zien in de toestenbank'*

```php
\tcCore\FactoryScenarios\FactoryScenarioSchool001::create();
```
| test                           | yes/no | amount | extra                                                                         |
|--------------------------------|--------|--------|-------------------------------------------------------------------------------|
| Schools                        | yes    | 2      | main school and a second school purely as control                             |
| SchoolLocations                | yes    | 3      | main school 2, secondary school 1                                             |
| Tests                          | yes    | 12     | 1 for each teacher->subject                                                   
| TestTakes                      | **no** |        |                                                                               |
| SchoolYears                    | yes    | 9      | 3 per schoolLocation: current-, last-, year before last year. (1 period each) |
| shared sections                | yes    | 1      | second school shares section with the first/main school                       |
| Teacher in two schoolLocations | yes    | 1      | The first Teacher is in schoolLocation 1 and 2                                |


#### SimpleSchool scenario
```php
\tcCore\FactoryScenarios\FactoryScenarioSchoolSimple::create();
```
| test            | yes/no | amount | extra              |
|-----------------|--------|-----|--------------------|
| Schools         | yes    | 1   | One school         |
| SchoolLocations | yes    | 1   | One schoolLocation |
| Tests           | **no** |     |                    |
| TestTakes       | **no** |     |                    |
| SchoolYears     | yes    | 1   | (1 period)         |
| Shared Sections | **no** |     |                    |
| Teacher in two schoolLocations | **no** |     |                             |


#### ComplexRandomizedNames scenario
```php
\tcCore\FactoryScenarios\FactoryScenarioSchoolRandomComplex::create();
```
| test            | yes/no | amount | extra                                         |
|-----------------|--------|--------|-----------------------------------------------|
| Schools         | yes    | 1      | randomized school name                        |
| SchoolLocations | yes    | 2      | randomized school location names              |
| Tests           | **no** |        |
| TestTakes       | **no** |        |                                               |
|SchoolYears| yes    | 1      | (1 period)                                    |
| Shared Sections | yes    | 2      | back and forth between schoolLocation 1 and 2 |
| Teacher in two schoolLocations | yes    | 1      | The first Teacher is in schoolLocation 1 and 2                                |

## Usage for creating TestTakes

#### one TestTake at a time
Planned
```php
FactoryScenarioTestTakePlanned::create();
```
Taking Test
```php
FactoryScenarioTestTakeTakingTest::create();
```
Taken
```php
FactoryScenarioTestTakeTaken::create();
```
Discussing
```php
FactoryScenarioTestTakeDiscussing::create();
```
Discussed
```php
FactoryScenarioTestTakeDiscussed::create();
```
Rated 
```php
FactoryScenarioTestTakeRated::create();
```

## Setting up a full test suite
Use the following class methods to create a test suite, with:
* one TestTake for each of the test_take_statuses
* each TestTake contains (if applicable) testQuestions, testParticipants, Answers*, Ratings*
* each TestTake contains at least one TestQuestion with attachments

*ratings are ofcourse only applicable for TestTakes with status '9' Rated, etc. 
```php
FactoryScenarioTestTakePlanned::create();       //test_take_status_id: 1
FactoryScenarioTestTakeTakingTest::create();    //test_take_status_id: 3
FactoryScenarioTestTakeTaken::create();         //test_take_status_id: 6
FactoryScenarioTestTakeDiscussing::create();    //test_take_status_id: 7      
FactoryScenarioTestTakeDiscussed::create();     //test_take_status_id: 8
FactoryScenarioTestTakeRated::create();         //test_take_status_id: 9
```

## Usage/Examples for Tests
Create Test with all question types
```php
FactoryScenarioTestTestWithAllQuestionTypes::create();
```
Create Test with all question types and return Test Model, instead of scenario factory
```php
FactoryScenarioTestTestWithAllQuestionTypes::createTest();
```
Create Test with all question types and give the test a name
```php
FactoryScenarioTestTestWithAllQuestionTypes::create('test name');
FactoryScenarioTestTestWithAllQuestionTypes::createTest('test name');
```
Other Test Factories:
```php
FactoryScenarioTestTestWithTwoQuestions::create();
```
```php
FactoryScenarioTestTestWithOpenShortQuestion::create();
```

## In Depth Examples
For more examples and how to use the Factories,
see the Unit Tests in the following directories:
```
tests/Unit/TestFactory
```
```
tests/Unit/TestTakeFactory
```
```
tests/Unit/TestTakeScenarioFactory
```