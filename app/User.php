<?php namespace tcCore;

use Carbon\Carbon;
use Closure;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Jobs\CountSchoolActiveTeachers;
use tcCore\Jobs\CountSchoolLocationActiveTeachers;
use tcCore\Jobs\CountSchoolLocationQuestions;
use tcCore\Jobs\CountSchoolLocationStudents;
use tcCore\Jobs\CountSchoolLocationTeachers;
use tcCore\Jobs\CountSchoolLocationTests;
use tcCore\Jobs\CountSchoolLocationTestsTaken;
use tcCore\Jobs\CountSchoolQuestions;
use tcCore\Jobs\CountSchoolStudents;
use tcCore\Jobs\CountSchoolTeachers;
use tcCore\Jobs\CountSchoolTests;
use tcCore\Jobs\CountSchoolTestsTaken;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use tcCore\Lib\User\Roles;

class User extends BaseModel implements AuthenticatableContract, CanResetPasswordContract, AccessCheckable {

	use Authenticatable;
	use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

    protected $appends = ['has_text2speech','active_text2speech'];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['sales_organization_id', 'school_id', 'school_location_id', 'username', 'name_first', 'name_suffix', 'name', 'password', 'external_id', 'gender', 'time_dispensation', 'text2speech','abbreviation', 'note'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token', 'session_hash', 'api_key'];

	/**
	 * @var array Array with school class IDs of which this user is student, for saving
	 */
	protected $studentSchoolClasses;

	/**
	 * @var array Array with school class IDs of which this user is manager, for saving
	 */
	protected $managerSchoolClasses;

	/**
	 * @var array Array with school class IDs of which this user is mentor, for saving
	 */
	protected $mentorSchoolClasses;

	/**
	 * @var array Array with role IDs, for saving
	 */
	protected $userRoles;

	/**
	 * @var array Array with parent user IDs, for saving
	 */
	protected $studentParents;

	/**
	 * @var array Array with student user IDs, for saving
	 */
	protected $studentParentsOf;

	/**
	 * @var
	 */
	protected $profileImage;

	public function fill(array $attributes) {
		parent::fill($attributes);

		if (array_key_exists('student_school_classes', $attributes)) {
			$this->studentSchoolClasses = $attributes['student_school_classes'];
		} elseif(array_key_exists('add_student_school_class', $attributes) || array_key_exists('delete_student_school_class', $attributes)) {
			$this->studentSchoolClasses = $this->students()->lists('class_id')->all();
			if (array_key_exists('add_student_school_class', $attributes)) {
				array_push($this->studentSchoolClasses, $attributes['add_student_school_class']);
			}

			if (array_key_exists('delete_student_school_class', $attributes)) {
				if(($key = array_search($attributes['delete_student_school_class'], $this->studentSchoolClasses)) !== false) {
					unset($this->studentSchoolClasses[$key]);
				}
			}
		}

		if (array_key_exists('manager_school_classes', $attributes)) {
			$this->managerSchoolClasses = $attributes['manager_school_classes'];
		} elseif(array_key_exists('add_manager_school_class', $attributes) || array_key_exists('delete_manager_school_class', $attributes)) {
			$this->managerSchoolClasses = $this->managers()->lists('school_class_id')->all();
			if (array_key_exists('add_manager_school_class', $attributes)) {
				array_push($this->managerSchoolClasses, $attributes['add_manager_school_class']);
			}

			if (array_key_exists('delete_manager_school_class', $attributes)) {
				if(($key = array_search($attributes['delete_manager_school_class'], $this->managerSchoolClasses)) !== false) {
					unset($this->managerSchoolClasses[$key]);
				}
			}
		}

		if (array_key_exists('mentor_school_classes', $attributes)) {
			$this->mentorSchoolClasses = $attributes['mentor_school_classes'];
		} elseif(array_key_exists('add_mentor_school_class', $attributes) || array_key_exists('delete_mentor_school_class', $attributes)) {
			$this->mentorSchoolClasses = $this->mentors()->lists('school_class_id')->all();
			if (array_key_exists('add_mentor_school_class', $attributes)) {
				array_push($this->mentorSchoolClasses, $attributes['add_mentor_school_class']);
			}

			if (array_key_exists('delete_mentor_school_class', $attributes)) {
				if(($key = array_search($attributes['delete_mentor_school_class'], $this->mentorSchoolClasses)) !== false) {
					unset($this->mentorSchoolClasses[$key]);
				}
			}
		}

		if (array_key_exists('student_parents', $attributes)) {
			$this->studentParents = $attributes['student_parents'];
		} elseif(array_key_exists('add_student_parent', $attributes) || array_key_exists('delete_student_parent', $attributes)) {
			$this->studentParents = $this->studentParents()->lists('student_parent_id')->all();
			if (array_key_exists('add_student_parent', $attributes)) {
				array_push($this->studentParents, $attributes['add_student_parent']);
			}

			if (array_key_exists('delete_student_parent', $attributes)) {
				if(($key = array_search($attributes['delete_student_parent'], $this->studentParents)) !== false) {
					unset($this->studentParents[$key]);
				}
			}
		}

		if (array_key_exists('student_parents_of', $attributes)) {
			$this->studentParentsOf = $attributes['student_parents_of'];
		} elseif(array_key_exists('add_student_parent_of', $attributes) || array_key_exists('delete_student_parent_of', $attributes)) {
			$this->studentParentsOf = $this->studentParentsOf()->lists('user_id')->all();
			if (array_key_exists('add_student_parent_of', $attributes)) {
				array_push($this->studentParentsOf, $attributes['add_student_parent_of']);
			}

			if (array_key_exists('delete_student_parent_of', $attributes)) {
				if(($key = array_search($attributes['delete_student_parent_of'], $this->studentParentsOf)) !== false) {
					unset($this->studentParentsOf[$key]);
				}
			}
		}

		if(array_key_exists('user_roles', $attributes)) {
			$this->userRoles = $attributes['user_roles'];
		} elseif(array_key_exists('add_user_role', $attributes) || array_key_exists('delete_user_role', $attributes)) {
			$this->userRoles = $this->userRoles()->lists('role_id')->all();
			if (array_key_exists('add_user_role', $attributes)) {
				array_push($this->userRoles, $attributes['add_user_role']);
			}

			if (array_key_exists('delete_user_role', $attributes)) {
				if(($key = array_search($attributes['delete_user_role'], $this->userRoles)) !== false) {
					unset($this->userRoles[$key]);
				}
			}
		}

		if (is_array($attributes) && array_key_exists('profile_image', $attributes) && $attributes['profile_image'] instanceof UploadedFile) {
			$this->fillFileProfileImage($attributes['profile_image']);
		}
	}

    public function text2SpeechDetails(){
        return $this->hasOne(Text2speech::class);
    }

    public function text2SpeechLog(){
        return $this->hasMany(Text2speechLog::class);
    }

	public function hasText2Speech(){
	    return (bool) $this->text2speech;
    }

    public function hasActiveText2Speech(){
	    if(!$this->hasText2Speech()) return false;
	    return (bool) $this->text2SpeechDetails->active;
    }

    public function getHasText2speechAttribute(){
	    return $this->hasText2Speech();
    }

    public function getActiveText2speechAttribute(){
	    return $this->hasActiveText2Speech();
    }

	public static function boot()
	{
		parent::boot();

		static::saving(function(User $user) {
			if ($user->getAttribute('school_id') !== $user->getOriginal('school_id') || $user->getAttribute('school_location_id') !== $user->getOriginal('school_location_id')) {
				if ($user->studentSchoolClasses === null) {
					$user->studentSchoolClasses = array();
				}

				if ($user->managerSchoolClasses === null) {
					$user->managerSchoolClasses = array();
				}

				if ($user->mentorSchoolClasses === null) {
					$user->mentorSchoolClasses = array();
				}
			}
		});

		// Progress additional answers
		static::saved(function(User $user)
		{
		    $oldText2Speech = (bool) $user->getOriginal('text2speech');
		    if(!$oldText2Speech && (bool) request()->input('text2speech')){
		        // we've got a new user with time dispensation
                Text2speech::create([
                    'user_id'   => $user->getKey(),
                    'active'    => true,
                    'acceptedby'=> Auth::user()->getKey(),
                    'price'     => config('custom.text2speech.price')
                ]);
                Text2speechLog::create([
                   'user_id'    => $user->getKey(),
                   'action'     => 'ACCEPTED',
                   'who'        => Auth::user()->getKey()
                ]);
            } else if($oldText2Speech && request()->has('active_text2speech')){
                // we've got a student with time dispensation and there might be a change in the active status
                // we only change these settings if there is a active_time_dispensation value, otherwise it would be changed on password update as well for instance
                $newActiveText2Speech = (bool) request()->input('active_text2speech');
                $oldActiveText2Speech = (bool) $user->hasActiveText2Speech();
                if($newActiveText2Speech !== $oldActiveText2Speech){
                    $user->text2SpeechDetails->active = $newActiveText2Speech;
                    $user->text2SpeechDetails->save();

                    Text2speechLog::create([
                        'user_id'    => $user->getKey(),
                        'action'     => ($newActiveText2Speech) ? 'ENABLED' : 'DISABLED',
                        'who'        => Auth::user()->getKey()
                    ]);
                }
            }

			if ($user->userRoles !== null) {
				$user->saveUserRoles();
			}

			if ($user->studentSchoolClasses !== null) {
				$user->saveStudentSchoolClasses();
			}

			if ($user->managerSchoolClasses !== null) {
				$user->saveManagerSchoolClasses();
			}

			if ($user->mentorSchoolClasses !== null) {
				$user->saveMentorSchoolClasses();
			}

			if ($user->studentParents !== null) {
				$user->saveStudentParents();
			}

			if ($user->studentParentsOf !== null) {
				$user->saveStudentParentsOf();
			}

			if ($user->profileImage instanceof UploadedFile) {
				$original = $user->getOriginalProfileImagePath();
				if (File::exists($original)) {
					File::delete($original);
				}

				$user->profileImage->move(storage_path('user_profile_images'), $user->getKey().' - '.$user->getAttribute('profile_image_name'));
			}

			//Trigger jobs
			if ($user->getAttribute('school_id') !== $user->getOriginal('school_id') || $user->getAttribute('school_location_id') !== $user->getOriginal('school_location_id')) {
				// Reload roles of this user!
				$user->load('roles');
				$roles = Roles::getUserRoles($user);

				$school = $user->school;
				$schoolLocation = $user->schoolLocation;

				if ($user->getAttribute('school_id') !== $user->getOriginal('school_id')) {
					$prevSchool = School::find($user->getOriginal('school_id'));
				} else {
					$prevSchool = null;
				}

				if ($user->getAttribute('school_location_id') !== $user->getOriginal('school_location_id')) {
					$prevSchoolLocation = School::find($user->getOriginal('school_location_id'));
				} else {
					$prevSchoolLocation = null;
				}

				if (in_array('Student', $roles)) {
					if ($school !== null) {
						Queue::push(new CountSchoolStudents($school));
					}

					if ($schoolLocation !== null) {
						Queue::push(new CountSchoolLocationStudents($schoolLocation));
					}

					if ($prevSchool !== null) {
						Queue::push(new CountSchoolStudents($prevSchool));
					}

					if ($prevSchoolLocation !== null) {
						Queue::push(new CountSchoolLocationStudents($prevSchoolLocation));
					}

					//Delete from future test takes
					TestParticipant::where('user_id', $user->getKey())->whereIn('test_take_id', function ($query) {
						$testTake = new TestTake();
						$date = new Carbon();
						$date->setTime(0,0);

						$query->select('id')->from($testTake->getTable())
							->where('test_take_status_id', TestTakeStatus::where('name', '=', 'Planned')->value('id'))
							->where('time_start', '>', $date->format('Y-m-d H:i:s'));
					})->delete();
				}

				if (in_array('Teacher', $roles)) {
					if ($school !== null) {
						Queue::push(new CountSchoolTeachers($school));
						Queue::push(new CountSchoolActiveTeachers($school));
						Queue::push(new CountSchoolQuestions($school));
						Queue::push(new CountSchoolTests($school));
						Queue::push(new CountSchoolTestsTaken($school));
					}

					if ($schoolLocation !== null) {
						Queue::push(new CountSchoolLocationTeachers($schoolLocation));
						Queue::push(new CountSchoolLocationActiveTeachers($schoolLocation));
						Queue::push(new CountSchoolLocationQuestions($schoolLocation));
						Queue::push(new CountSchoolLocationTests($schoolLocation));
						Queue::push(new CountSchoolLocationTestsTaken($schoolLocation));
					}

					if ($prevSchool !== null) {
						Queue::push(new CountSchoolTeachers($prevSchool));
						Queue::push(new CountSchoolActiveTeachers($prevSchool));
						Queue::push(new CountSchoolQuestions($prevSchool));
						Queue::push(new CountSchoolTests($prevSchool));
						Queue::push(new CountSchoolTestsTaken($prevSchool));
					}

					if ($prevSchoolLocation !== null) {
						Queue::push(new CountSchoolLocationTeachers($prevSchoolLocation));
						Queue::push(new CountSchoolLocationActiveTeachers($prevSchoolLocation));
						Queue::push(new CountSchoolLocationQuestions($prevSchoolLocation));
						Queue::push(new CountSchoolLocationTests($prevSchoolLocation));
						Queue::push(new CountSchoolLocationTestsTaken($prevSchoolLocation));
					}
				}
			} else {
				$school = $user->school;
				$schoolLocation = $user->schoolLocation;

				if ($user->getAttribute('count_last_test_taken') !== $user->getOriginal('count_last_test_taken')) {
					if ($school !== null) {
						Queue::push(new CountSchoolActiveTeachers($school));
					} elseif($schoolLocation !== null) {
						Queue::push(new CountSchoolLocationActiveTeachers($schoolLocation));
					}
				}

				if ($user->getAttribute('count_questions') !== $user->getOriginal('count_questions')) {
					if ($school !== null) {
						Queue::push(new CountSchoolQuestions($school));
					} elseif($schoolLocation !== null) {
						Queue::push(new CountSchoolLocationQuestions($schoolLocation));
					}
				}

				if ($user->getAttribute('count_tests') !== $user->getOriginal('count_tests')) {
					if ($school !== null) {
						Queue::push(new CountSchoolTests($school));
					} elseif($schoolLocation !== null) {
						Queue::push(new CountSchoolLocationTests($schoolLocation));
					}
				}

				if ($user->getAttribute('count_tests_taken') !== $user->getOriginal('count_tests_taken')) {
					if ($school !== null) {
						Queue::push(new CountSchoolTestsTaken($school));
					} elseif($schoolLocation !== null) {
						Queue::push(new CountSchoolLocationTestsTaken($schoolLocation));
					}
				}
			}
		});

		static::deleted(function(User $user)
		{
			if ($user->forceDeleting) {
				$original = $user->getOriginalProfileImagePath();
				if (File::exists($original)) {
					File::delete($original);
				}
			}

			//Trigger jobs
			$roles = Roles::getUserRoles($user);

			$school = $user->school;
			$schoolLocation = $user->schoolLocation;

			if ($user->getAttribute('school_id') !== $user->getOriginal('school_id')) {
				$prevSchool = School::find($user->getOriginal('school_id'));
			} else {
				$prevSchool = null;
			}

			if ($user->getAttribute('school_location_id') !== $user->getOriginal('school_location_id')) {
				$prevSchoolLocation = School::find($user->getOriginal('school_location_id'));
			} else {
				$prevSchoolLocation = null;
			}

			if (in_array('Student', $roles)) {
				if ($school !== null) {
					Queue::push(new CountSchoolStudents($school));
				}

				if ($schoolLocation !== null) {
					Queue::push(new CountSchoolLocationStudents($schoolLocation));
				}

				if ($prevSchool !== null) {
					Queue::push(new CountSchoolStudents($prevSchool));
				}

				if ($prevSchoolLocation !== null) {
					Queue::push(new CountSchoolLocationStudents($prevSchoolLocation));
				}
			}

			if (in_array('Teacher', $roles)) {
				if ($school !== null) {
					Queue::push(new CountSchoolTeachers($school));
					Queue::push(new CountSchoolActiveTeachers($school));
					Queue::push(new CountSchoolQuestions($school));
					Queue::push(new CountSchoolTests($school));
					Queue::push(new CountSchoolTestsTaken($school));
				}

				if ($schoolLocation !== null) {
					Queue::push(new CountSchoolLocationTeachers($schoolLocation));
					Queue::push(new CountSchoolLocationActiveTeachers($schoolLocation));
					Queue::push(new CountSchoolLocationQuestions($schoolLocation));
					Queue::push(new CountSchoolLocationTests($schoolLocation));
					Queue::push(new CountSchoolLocationTestsTaken($schoolLocation));
				}

				if ($prevSchool !== null) {
					Queue::push(new CountSchoolTeachers($prevSchool));
					Queue::push(new CountSchoolActiveTeachers($prevSchool));
					Queue::push(new CountSchoolQuestions($prevSchool));
					Queue::push(new CountSchoolTests($prevSchool));
					Queue::push(new CountSchoolTestsTaken($prevSchool));
				}

				if ($prevSchoolLocation !== null) {
					Queue::push(new CountSchoolLocationTeachers($prevSchoolLocation));
					Queue::push(new CountSchoolLocationActiveTeachers($prevSchoolLocation));
					Queue::push(new CountSchoolLocationQuestions($prevSchoolLocation));
					Queue::push(new CountSchoolLocationTests($prevSchoolLocation));
					Queue::push(new CountSchoolLocationTestsTaken($prevSchoolLocation));
				}
			}

		});
	}

	public function getOriginalProfileImagePath() {
		return ((substr(storage_path('user_profile_images'), -1) === DIRECTORY_SEPARATOR) ? storage_path('user_profile_images') : storage_path('user_profile_images') . DIRECTORY_SEPARATOR) . $this->getOriginal($this->getKeyName()) . ' - ' . $this->getOriginal('profile_image_name');
	}

	public function getCurrentProfileImagePath() {
		return ((substr(storage_path('user_profile_images'), -1) === DIRECTORY_SEPARATOR) ? storage_path('user_profile_images') : storage_path('user_profile_images') . DIRECTORY_SEPARATOR) . $this->getKey() . ' - ' . $this->getAttribute('profile_image_name');
	}

	public function fillFileProfileImage(UploadedFile $file)
	{
		if ($file->isValid()) {
			$this->profileImage = $file;
			$this->setAttribute('profile_image_name', $file->getClientOriginalName());
			$this->setAttribute('profile_image_size', $file->getSize());
			$this->setAttribute('profile_image_extension', $file->getClientOriginalExtension());
			$this->setAttribute('profile_image_mime_type', $file->getMimeType());
		}
	}

	public function roles() {
		return $this->belongsToMany('tcCore\Role', 'user_roles')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
	}

	protected function saveUserRoles() {
		$userRoles = $this->userRoles()->withTrashed()->get();
		$this->syncTcRelation($userRoles, $this->userRoles, 'role_id', function($user, $userRole) {
			UserRole::create(['role_id' => $userRole, 'user_id' => $user->getKey()]);
		});

		$this->userRoles = null;
	}

	public function userRoles() {
		return $this->hasMany('tcCore\UserRole');
	}

	public function salesOrganization() {
		return $this->belongsTo('tcCore\SalesOrganization');
	}

	public function school() {
		return $this->belongsTo('tcCore\School');
	}

	public function schoolLocation() {
		return $this->belongsTo('tcCore\SchoolLocation');
	}

	public function mentors() {
		return $this->hasMany('tcCore\Mentor');
	}

	public function mentorSchoolClasses() {
		return $this->belongsToMany('tcCore\SchoolClass', 'mentors', 'user_id', 'school_class_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
	}

	protected function saveMentorSchoolClasses() {
		$schoolClasses = $this->mentors()->withTrashed()->get();
		$this->syncTcRelation($schoolClasses, $this->mentorSchoolClasses, 'school_class_id', function($user, $schoolClass) {
			Mentor::create(['school_class_id' => $schoolClass, 'user_id' => $user->getKey()]);
		});

		$this->mentorSchoolClasses = null;
	}


	public function managers() {
		return $this->hasMany('tcCore\Manager');
	}

	public function managerSchoolClasses() {
		return $this->belongsToMany('tcCore\SchoolClass', 'managers', 'user_id', 'school_class_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
	}

	protected function saveManagerSchoolClasses() {
		$schoolClasses = $this->managers()->withTrashed()->get();
		$this->syncTcRelation($schoolClasses, $this->managerSchoolClasses, 'school_class_id', function($user, $schoolClass) {
			Manager::create(['school_class_id' => $schoolClass, 'user_id' => $user->getKey()]);
		});

		$this->managerSchoolClasses = null;
	}


	public function students() {
		return $this->hasMany('tcCore\Student');
	}

	public function studentSchoolClasses() {
		return $this->belongsToMany('tcCore\SchoolClass', 'students', 'user_id', 'class_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
	}

	protected function saveStudentSchoolClasses() {
		$schoolClasses = $this->students()->withTrashed()->get();
		$this->syncTcRelation($schoolClasses, $this->studentSchoolClasses, 'class_id', function($user, $schoolClass) {
			Student::create(['class_id' => $schoolClass, 'user_id' => $user->getKey()]);
		});

		$this->studentSchoolClasses = null;
	}

	public function teacherSchoolClasses() {
		$userId = $this->getKey();
		return SchoolClass::whereIn('id', function ($query) use ($userId) {
			$query->select('class_id')
				->from(with(new Teacher())->getTable())
				->where('user_id', $userId)
				->where('deleted_at', null);
		});
	}

	public function studentParents() {
		return $this->hasMany('tcCore\StudentParent');
	}

	public function studentParentUsers() {
		return $this->belongsToMany('tcCore\Users', 'student_parents', 'user_id', 'parent_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
	}

	protected function saveStudentParents() {
		$studentParents = $this->studentParents()->withTrashed()->get();
		$this->syncTcRelation($studentParents, $this->studentParents, 'parent_id', function($user, $parent) {
			StudentParent::create(['parent_id' => $parent, 'user_id' => $user->getKey()]);
		});

		$this->studentParents = null;
	}

	public function studentParentsOf() {
		return $this->hasMany('tcCore\StudentParent', 'parent_id');
	}

	public function studentParentOfUsers() {
		return $this->belongsToMany('tcCore\Users', 'student_parents', 'parent_id', 'user_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
	}

	protected function saveStudentParentsOf() {
		$studentParentsOf = $this->StudentParentsOf()->withTrashed()->get();
		$this->syncTcRelation($studentParentsOf, $this->studentParentsOf, 'user_id', function($parent, $user) {
			StudentParent::create(['user_id' => $user, 'parent_id' => $parent->getKey()]);
		});

		$this->studentParentsOf = null;
	}

	public function subjects($query = null) {
		$userId = $this->getKey();

		if ($query === null) {
			$query = Subject::select();
		} else {
			$query->from(with(new Subject())->getTable())
				->where('deleted_at', null);
		}

		$query->whereIn('id', function ($query) use ($userId) {
			$query->select('subject_id')
				->from(with(new Teacher())->getTable())
				->where('user_id', $userId)
				->where('deleted_at', null);
		});

		return $query;
	}

	public function sections($query = null) {
		$user = $this;

		if ($query === null) {
			$query = Section::select();
		} else {
			$query->from(with(new Section())->getTable())
				->where('deleted_at', null);
		}

		$query->whereIn('id', function ($query) use ($user) {
			$user->subjects($query)->select('section_id');
		});

		return $query;
	}

	public function teacher() {
		return $this->hasMany('tcCore\Teacher');
	}

	public function tests() {
		return $this->hasMany('tcCore\Test', 'author_id');
	}

	public function invigilator() {
		return $this->hasMany('tcCore\Invigilator');
	}

	public function invigilatorTestTakes() {
		return $this->belongsToMany('tcCore\TestTake', 'invigilators')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()]);
	}

	public function testParticipants() {
		return $this->hasMany('tcCore\TestParticipant');
	}

	public function testRatings() {
		return $this->hasMany('tcCore\TestRating');
	}

	public function answerRating() {
		return $this->hasMany('tcCore\AnswerRating');
	}

	public function questionAuthors() {
		return $this->hasMany('tcCore\QuestionAuthor', 'user_id');
	}

	public function authors() {
		return $this->belongsToMany('tcCore\Question', 'question_authors', 'user_id', 'question_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
	}

	// Account manager's umbrella organizations
	public function umbrellaOrganizations() {
		return $this->hasMany('tcCore\UmbrellaOrganization');
	}

	// Account manager's schools
	public function schools() {
		return $this->hasMany('tcCore\School');
	}

	// Account manager's schoolLocations
	public function schoolLocations() {
		return $this->hasMany('tcCore\SchoolLocation');
	}

	public function ratings() {
		return $this->hasMany('tcCore\Rating');
	}

	public function averageRatings() {
		return $this->hasMany('tcCore\AverageRating');
	}


	/**
	 * Returns the private API key for the user, or false on failure.
	 *
	 * @return bool|mixed
	 */
	public function apiKey()
	{
		return !empty($this->api_key) ? $this->api_key : false;
	}

	public function getNameFullAttribute()
	{
		$result = '';
		if (array_key_exists('name_first', $this->attributes) && !empty($this->attributes['name_first'])) {
			$result .= $this->attributes['name_first'];
		}
		if (array_key_exists('name_first', $this->attributes) && !empty($this->attributes['name_first']) && array_key_exists('name_suffix', $this->attributes) && !empty($this->attributes['name_suffix'])) {
			$result .= ' '.$this->attributes['name_suffix'];
		}
		if ((array_key_exists('name_first', $this->attributes) && !empty($this->attributes['name_first']) || (array_key_exists('name_suffix', $this->attributes) && !empty($this->attributes['name_suffix']))) && array_key_exists('name', $this->attributes) && !empty($this->attributes['name'])) {
			$result .= ' '.$this->attributes['name'];
		}
		return $result;
	}

	public function scopeStudentFiltered($query, $filters = [], $sorting = [])
	{
		$query->join('user_roles', function($join)
		{
			$join->on('user_roles.user_id', '=', 'users.id')->whereNull('user_roles.deleted_at');
		})
			->join('roles', function($join)
			{
				$join->on('roles.id', '=', 'user_roles.role_id')->whereNull('roles.deleted_at');
			})
			->where('roles.name', 'Student');

		$user = Auth::user()->getAttributes();

		if (array_key_exists('school_id', $user) && $user['school_id'] !== null && array_key_exists('school_location_id', $user) && $user['school_location_id'] !== null) {
			$query->where(function ($query) use($user) {
				$query->where('users.school_id', $user['school_id'])
					->orWhere('users.school_location_id', $user['school_location_id']);
			});
		} elseif(array_key_exists('school_id', $user) && $user['school_id'] !== null) {
			$query->where('users.school_id', $user['school_id']);
		} elseif(array_key_exists('school_location_id', $user) && $user['school_location_id'] !== null) {
			$query->where('users.school_location_id', $user['school_location_id']);
		}

		foreach($filters as $key => $value) {
			switch($key) {
				case 'name':
					$query->where('name', 'LIKE', '%'.$value.'%');
					break;
				case 'school_class_id':
					$query->whereIn('users.id', function ($query) use ($value) {
						$query->select('students.user_id')
							->from(with(new SchoolClass())->getTable())
							->join(with(new Student())->getTable(), 'students.class_id', '=', 'school_classes.id')
							->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
							->where('school_classes.deleted_at', null)
							->where('students.deleted_at', null);
					});
					break;
				case 'test_take_id_participated':
				case 'test_take_id_made':
				case 'test_take_id_not_made':
					$testTakeIds = $value;

					$testTakeStatusses = null;
					$maxRating = null;
					$minRating = null;

					if ($key === 'test_id_made') {
						$testTakeStatusses = TestTakeStatus::whereIn('name', ['Taking test', 'Handed in', 'Taken away', 'Taken', 'Discussing', 'Discussed', 'Rated'])->list('id');
						if (array_key_exists('max_rating', $filters)) {
							$maxRating = $filters['max_rating'];
						}
						if (array_key_exists('min_rating', $filters)) {
							$minRating = $filters['min_rating'];
						}
					} elseif ($key === 'test_take_id_participated')  {
						$testTakeStatusses = TestTakeStatus::whereIn('name', ['Planned', 'Test not taken'])->list('id');
					}

					$subQuery = function ($query) use ($testTakeIds, $testTakeStatusses, $maxRating, $minRating) {
						$query->select('test_participants.user_id')
							->from(with(new SchoolClass())->getTable());
							if (is_array($testTakeIds)) {
								$query->whereIn('test_take_id', $testTakeIds);
							} else {
								$query->where('test_take_id', $testTakeIds);
							}
						if ($testTakeStatusses !== null) {
							$query->whereIn('test_take_status_id', $testTakeStatusses);
						}

						if ($maxRating !== null) {
							$query->where('rating', '<=', $maxRating);
						}

						if ($minRating !== null) {
							$query->where('rating', '>=', $minRating);
						}
					};

					$query->whereIn('users.id', $subQuery);
					break;
				case 'test_id_participated':
				case 'test_id_made':
				case 'test_id_not_made':
					// Todo: same filters as above but with test_ids
					break;
			}
		}

		foreach($sorting as $key => $value) {
			switch(strtolower($value)) {
				case 'id':
				case 'name':
				case 'abbreviation':
				case 'subject_id':
				case 'education_level_id':
				case 'education_level_year':
				case 'period_id':
				case 'test_kind_id':
				case 'status':
				case 'author_id':
					$key = $value;
					$value = 'asc';
					break;
				case 'asc':
				case 'desc':
					break;
				default:
					$value = 'asc';
			}
			switch(strtolower($key)) {
				case 'id':
				case 'name':
				case 'abbreviation':
				case 'subject_id':
				case 'education_level_id':
				case 'education_level_year':
				case 'period_id':
				case 'test_kind_id':
				case 'status':
				case 'author_id':
					$query->orderBy($key, $value);
					break;
			}
		}

		return $query;
	}

	public function scopeFiltered($query, $filters = [], $sorting = [])
	{
		$roles = Roles::getUserRoles();
		if (!in_array('Administrator', $roles) && in_array('Account manager', $roles)) {
			$userId = Auth::user()->getKey();

			$schoolIds = School::where(function ($query) use ($userId) {
				$query->whereIn('umbrella_organization_id', function ($query) use ($userId) {
					$query->select('id')
						->from(with(new UmbrellaOrganization())->getTable())
						->where('user_id', $userId)
						->whereNull('deleted_at');
				})->orWhere('user_id', $userId);
			})->lists('id')->all();

			$schoolLocationIds = SchoolLocation::where(function ($query) use ($schoolIds, $userId) {
				$query->whereIn('school_id', $schoolIds)
					->orWhere('user_id', $userId);
			})->lists('id')->all();

			$parentIds = StudentParent::whereIn('user_id', function($query) use ($schoolIds, $schoolLocationIds) {
				$query->select($this->getKeyName())
					->from($this->getTable())
					->where(function($query) use ($schoolIds, $schoolLocationIds) {
						$query->whereIn('school_id', $schoolIds);
						$query->orWhereIn('school_location_id', $schoolLocationIds);
					})
					->whereNull('deleted_at');
			})->lists('parent_id')->all();

			$query->where(function ($query) use ($schoolIds, $schoolLocationIds, $parentIds) {
				$query->whereIn('school_id', $schoolIds);
				$query->orWhereIn('school_location_id', $schoolLocationIds);
				$query->orWhereIn('id', $parentIds);
			});
		} elseif (!in_array('Administrator', $roles) && (in_array('School manager', $roles) || in_array('Teacher', $roles) || in_array('Invigilator', $roles) || in_array('School management', $roles) || in_array('Mentor', $roles))) {
			$user = Auth::user();
			$schoolId = $user->getAttribute('school_id');
			$schoolLocationId = $user->getAttribute('school_location_id');

			if ($schoolId !== null) {
				$schoolLocationIds = SchoolLocation::where(function ($query) use ($schoolId, $schoolLocationId) {
					$query->where('school_id', $schoolId)->orWhere('id', $schoolLocationId);
				})->lists('id')->all();
			} elseif($schoolLocationId !== null) {
				$schoolLocationIds = [$schoolLocationId];
			} else {
				$schoolLocationIds = [];
			}

			$parentIds = StudentParent::whereIn('user_id', function($query) use ($schoolId, $schoolLocationIds) {
				$query->select($this->getKeyName())
					->from($this->getTable())
					->where(function($query) use ($schoolId, $schoolLocationIds) {
						$query->where('school_id', $schoolId);
						$query->orWhereIn('school_location_id', $schoolLocationIds);
					})
					->whereNull('deleted_at');
			})->lists('parent_id')->all();

			$query->where(function ($query) use ($schoolId, $schoolLocationIds, $parentIds) {
				if ($schoolId !== null) {
					$query->where('school_id', $schoolId);
					$query->orWhereIn('school_location_id', $schoolLocationIds);
				} elseif($schoolLocationIds !== null) {
					$query->whereIn('school_location_id', $schoolLocationIds);
				}
				
				$query->orWhereIn('id', $parentIds);
			});
		} elseif (!in_array('Administrator', $roles)) {
			$query->where('id', Auth::user()->getKey());
		}

		foreach($filters as $key => $value) {
			switch($key) {
				case 'sales_organization_id':
					if (is_array($value)) {
						$query->whereIn('sales_organization_id', $value);
					} else {
						$query->where('sales_organization_id', '=', $value);
					}
					break;
				case 'school_id':
					if (is_array($value)) {
						$query->whereIn('school_id', $value);
					} else {
						$query->where('school_id', '=', $value);
					}
					break;
				case 'school_location_id':
					if (is_array($value)) {
						$query->whereIn('school_location_id', $value);
					} else {
						$query->where('school_location_id', '=', $value);
					}
					break;
				case 'school_class_id':
					$query->whereIn('users.id', function ($query) use ($value) {
						$query->select('students.user_id')
							->from(with(new SchoolClass())->getTable())
							->join(with(new Student())->getTable(), 'students.class_id', '=', 'school_classes.id')
							->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
							->where('school_classes.deleted_at', null)
							->where('students.deleted_at', null);
					});
					break;
				case 'external_id':
					if (is_array($value)) {
						$query->whereIn('external_id', $value);
					} else {
						$query->where('external_id', '=', $value);
					}
					break;
				case 'username':
					$query->where('username', 'LIKE', '%'.$value.'%');
					break;
				case 'name_full':
					$query->where(DB::raw('CONCAT_WS(\' \', name_first, name_suffix, name)'), 'LIKE', '%'.$value.'%');
					break;
				case 'name':
					$query->where('name', 'LIKE', '%'.$value.'%');
					break;
				case 'name_first':
					$query->where('name_first', 'LIKE', '%'.$value.'%');
					break;
				case 'send_welcome_email':
					$query->where('send_welcome_email', '=', $value);
					break;
				case 'gender':
					if (is_array($value)) {
						$query->whereIn('gender', $value);
					} else {
						$query->where('gender', '=', $value);
					}
					break;
				case 'student_school_class_id':
					$query->whereIn('users.id', function ($query) use ($value) {
						$query->select('students.user_id')
							->from(with(new SchoolClass())->getTable())
							->join(with(new Student())->getTable(), 'students.class_id', '=', 'school_classes.id')
							->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
							->where('school_classes.deleted_at', null)
							->where('students.deleted_at', null);
					});
					break;
				case 'mentor_school_class_id':
					$query->whereIn('users.id', function ($query) use ($value) {
						$query->select('mentors.user_id')
							->from(with(new SchoolClass())->getTable())
							->join(with(new Mentor())->getTable(), 'mentors.school_class_id', '=', 'school_classes.id')
							->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
							->where('school_classes.deleted_at', null)
							->where('mentors.deleted_at', null);
					});
					break;
				case 'manager_school_class_id':
					$query->whereIn('users.id', function ($query) use ($value) {
						$query->select('managers.user_id')
							->from(with(new SchoolClass())->getTable())
							->join(with(new Manager())->getTable(), 'managers.school_class_id', '=', 'school_classes.id')
							->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
							->where('school_classes.deleted_at', null)
							->where('managers.deleted_at', null);
					});
					break;
				case 'student_parent_id':
					$query->whereIn('users.id', function ($query) use ($value) {
						$query->select('users.id')
							->from(with(new User())->getTable())
							->join(with(new StudentParent())->getTable(), 'student_parents.user_id', '=', 'users.id')
							->whereIn('student_parents.parent_id', (is_array($value)) ? $value : [$value])
							->where('users.deleted_at', null)
							->where('student_parents.deleted_at', null);
					});
					break;
				case 'student_parent_of_id':
					$query->whereIn('users.id', function ($query) use ($value) {
						$query->select('users.id')
							->from(with(new User())->getTable())
							->join(with(new StudentParent())->getTable(), 'student_parents.parent_id', '=', 'users.id')
							->whereIn('student_parents.user_id', (is_array($value)) ? $value : [$value])
							->where('users.deleted_at', null)
							->where('student_parents.deleted_at', null);
					});
					break;
				case 'role':
					$query->whereIn('id', function ($query) use ($value) {
						$query->select('user_id')
						->from(with(new UserRole())->getTable());
						if (is_array($value)) {
							$query->whereIn('role_id', $value);
						} else {
							$query->where('role_id', '=', $value);
						}
					});
					break;
				default:
					break;
			}
		}

		foreach($sorting as $key => $value) {
			switch(strtolower($value)) {
				case 'name':
				case 'name_first':
					$key = $value;
					$value = 'asc';
					break;
				case 'asc':
				case 'desc':
					break;
				default:
					$value = 'asc';
			}
			switch(strtolower($key)) {
				case 'name':
				case 'name_first':
					$query->orderBy($key, $value);
					break;
			}
		}

		return $query;
	}

	public function generateSessionHash() {
		// didn't work out to be safe
		// return str_random(100);
		// new
		return sprintf('%s%d',str_random(85),$this->id);
	}


	public function canAccess()
	{
		$roles = Roles::getUserRoles();

		if (in_array('Administrator', $roles)) {
			return true;
		}

		if (in_array('Account manager', $roles)) {
			$userId = Auth::user()->getKey();

			$schoolIds = School::where(function ($query) use ($userId) {
				$query->whereIn('umbrella_organization_id', function ($query) use ($userId) {
					$query->select('id')
						->from(with(new UmbrellaOrganization())->getTable())
						->where('user_id', $userId)
						->whereNull('deleted_at');
				})->orWhere('user_id', $userId);
			})->lists('id')->all();

			$schoolLocationIds = SchoolLocation::where(function ($query) use ($schoolIds, $userId) {
				$query->whereIn('school_id', $schoolIds)
					->orWhere('user_id', $userId);
			})->lists('id')->all();

			if (!in_array($this->getAttribute('school_id'), $schoolIds) && !in_array($this->getAttribute('school_location_id'), $schoolLocationIds)){
				$parentCount = StudentParent::whereIn('user_id', function($query) use ($schoolIds, $schoolLocationIds) {
					$query->select($this->getKeyName())
						->from($this->getTable())
						->where(function($query) use ($schoolIds, $schoolLocationIds) {
							$query->whereIn('school_id', $schoolIds);
							$query->orWhereIn('school_location_id', $schoolLocationIds);
						})
						->whereNull('deleted_at');
				})->where('parent_id', $this->getKey())->count();

				return ($parentCount >= 1);
			} else {
				return true;
			}
		}

		if (in_array('School manager', $roles) || in_array('Teacher', $roles) || in_array('Invigilator', $roles) || in_array('School management', $roles) || in_array('Mentor', $roles)) {
			$user = Auth::user();
			$schoolId = $user->getAttribute('school_id');
			$schoolLocationId = $user->getAttribute('school_location_id');

			if ($schoolId !== null) {
				$schoolLocationIds = SchoolLocation::where(function ($query) use ($schoolId, $schoolLocationId) {
					$query->where('school_id', $schoolId)->orWhere('id', $schoolLocationId);
				})->lists('id')->all();
			} elseif($schoolLocationId !== null) {
				$schoolLocationIds = [$schoolLocationId];
			} else {
				$schoolLocationIds = [];
			}

			if ($this->getAttribute('school_id') != $user->getAttribute('school_id') && !in_array($this->getAttribute('school_location_id'), $schoolLocationIds)) {
				$parentCount = StudentParent::whereIn('user_id', function($query) use ($schoolId, $schoolLocationIds) {
					$query->select($this->getKeyName())
						->from($this->getTable())
						->where(function($query) use ($schoolId, $schoolLocationIds) {
							$query->where('school_id', $schoolId);
							$query->orWhereIn('school_location_id', $schoolLocationIds);
						})
						->whereNull('deleted_at');
				})->where('parent_id', $this->getKey())->count();

				return ($parentCount >= 1);
			} else {
				return true;
			}
		}

		if ($this->getKey() === Auth::user()->getKey()) {
			return true;
		}
		return false;
	}

	public function canAccessBoundResource($request, Closure $next) {
		return $this->canAccess();
	}

	public function getAccessDeniedResponse($request, Closure $next)
	{
		throw new AccessDeniedHttpException('Access to user denied');
	}

	/**
	 * Get the e-mail address where password reset links are sent.
	 *
	 * @return string
	 */
	public function getEmailForPasswordReset()
	{
		return $this->username;
	}


}
