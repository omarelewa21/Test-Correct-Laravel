<?php namespace tcCore;

use Closure;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Jobs\CountAccountManagerAccounts;
use tcCore\Jobs\CountAccountManagerActiveLicenses;
use tcCore\Jobs\CountAccountManagerExpiredLicenses;
use tcCore\Jobs\CountAccountManagerLicenses;
use tcCore\Jobs\CountAccountManagerStudents;
use tcCore\Jobs\CountAccountManagerTeachers;
use tcCore\Jobs\CountSchoolActiveLicenses;
use tcCore\Jobs\CountSchoolActiveTeachers;
use tcCore\Jobs\CountSchoolExpiredLicenses;
use tcCore\Jobs\CountSchoolLicenses;
use tcCore\Jobs\CountSchoolQuestions;
use tcCore\Jobs\CountSchoolStudents;
use tcCore\Jobs\CountSchoolTeachers;
use tcCore\Jobs\CountSchoolTests;
use tcCore\Jobs\CountSchoolTestsTaken;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class SchoolLocation extends BaseModel implements AccessCheckable {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'allow_inbrowser_testing' => 'boolean',
        'intense' => 'boolean',
        'school_language' => 'string',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'school_locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_code', 'name', 'school_id', 'grading_scale_id', 'user_id', 'number_of_students',
        'number_of_teachers', 'activated', 'main_address', 'main_postal', 'main_city', 'main_country', 'invoice_address',
        'invoice_postal', 'invoice_city', 'invoice_country', 'visit_address', 'visit_postal', 'visit_city', 'visit_country',
        'is_rtti_school_location', 'external_main_code','external_sub_code','is_open_source_content_creator',
        'is_allowed_to_view_open_source_content','allow_inbrowser_testing', 'allow_new_player_access', 'school_language'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $schoolYears;
    protected $sections;
    protected $educationLevels;
    protected $mainAddresses;
    protected $invoiceAddresses;
    protected $visitAddresses;
    protected $otherAddresses;
    protected $financialContacts;
    protected $technicalContacts;
    protected $implementationContacts;
    protected $otherContacts;

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if(array_key_exists('school_years', $attributes)) {
            $this->schoolYears = $attributes['school_years'];
        } elseif(array_key_exists('add_school_year', $attributes) || array_key_exists('delete_school_year', $attributes)) {
            $this->schoolYears = $this->schoolLocationSchoolYears()->pluck('school_year_id')->all();
            if (array_key_exists('add_school_year', $attributes)) {
                array_push($this->schoolYears, $attributes['add_school_year']);
            }

            if (array_key_exists('delete_school_year', $attributes)) {
                if(($key = array_search($attributes['delete_school_year'], $this->schoolYears)) !== false) {
                    unset($this->schoolYears[$key]);
                }
            }
        }

        if(array_key_exists('sections', $attributes)) {
            $this->sections = $attributes['sections'];
        } elseif(array_key_exists('add_section', $attributes) || array_key_exists('delete_section', $attributes)) {
            $this->sections = $this->schoolLocationSections()->pluck('section_id')->all();
            if (array_key_exists('add_section', $attributes)) {
                array_push($this->sections, $attributes['add_section']);
            }

            if (array_key_exists('delete_section', $attributes)) {
                if(($key = array_search($attributes['delete_section'], $this->sections)) !== false) {
                    unset($this->sections[$key]);
                }
            }
        }

        if(array_key_exists('education_levels', $attributes)) {
            $this->educationLevels = $attributes['education_levels'];
        } elseif(array_key_exists('add_education_level', $attributes) || array_key_exists('delete_education_level', $attributes)) {
            $this->educationLevels = $this->schoolLocationEducationLevels()->pluck('education_level_id')->all();
            if (array_key_exists('add_education_level', $attributes)) {
                array_push($this->educationLevels, $attributes['add_education_level']);
            }

            if (array_key_exists('delete_education_level', $attributes)) {
                if(($key = array_search($attributes['delete_education_level'], $this->educationLevels)) !== false) {
                    unset($this->educationLevels[$key]);
                }
            }
        }

        if(array_key_exists('main_addresses', $attributes)) {
            $this->mainAddresses = $attributes['main_addresses'];
        } elseif(array_key_exists('add_main_address', $attributes) || array_key_exists('delete_main_address', $attributes)) {
            $this->mainAddresses = $this->schoolLocationAddresses()->where('type', 'MAIN')->pluck('address_id')->all();
            if (array_key_exists('add_main_address', $attributes)) {
                array_push($this->mainAddresses, $attributes['add_main_address']);
            }

            if (array_key_exists('delete_main_address', $attributes)) {
                if(($key = array_search($attributes['delete_main_address'], $this->mainAddresses)) !== false) {
                    unset($this->mainAddresses[$key]);
                }
            }
        }

        if(array_key_exists('invoice_addresses', $attributes)) {
            $this->invoiceAddresses = $attributes['invoice_addresses'];
        } elseif(array_key_exists('add_invoice_address', $attributes) || array_key_exists('delete_invoice_address', $attributes)) {
            $this->invoiceAddresses = $this->schoolLocationAddresses()->where('type', 'INVOICE')->pluck('address_id')->all();
            if (array_key_exists('add_invoice_address', $attributes)) {
                array_push($this->invoiceAddresses, $attributes['add_invoice_address']);
            }

            if (array_key_exists('delete_invoice_address', $attributes)) {
                if(($key = array_search($attributes['delete_invoice_address'], $this->invoiceAddresses)) !== false) {
                    unset($this->invoiceAddresses[$key]);
                }
            }
        }

        if(array_key_exists('visit_addresses', $attributes)) {
            $this->visitAddresses = $attributes['visit_addresses'];
        } elseif(array_key_exists('add_visit_address', $attributes) || array_key_exists('delete_visit_address', $attributes)) {
            $this->visitAddresses = $this->schoolLocationAddresses()->where('type', 'VISIT')->pluck('address_id')->all();
            if (array_key_exists('add_visit_address', $attributes)) {
                array_push($this->visitAddresses, $attributes['add_visit_address']);
            }

            if (array_key_exists('delete_visit_address', $attributes)) {
                if(($key = array_search($attributes['delete_visit_address'], $this->visitAddresses)) !== false) {
                    unset($this->visitAddresses[$key]);
                }
            }
        }

        if(array_key_exists('other_addresses', $attributes)) {
            $this->otherAddresses = $attributes['other_addresses'];
        } elseif(array_key_exists('add_other_address', $attributes) || array_key_exists('delete_other_address', $attributes)) {
            $this->otherAddresses = $this->schoolLocationAddresses()->where('type', 'OTHER')->pluck('address_id')->all();
            if (array_key_exists('add_other_address', $attributes)) {
                array_push($this->otherAddresses, $attributes['add_other_address']);
            }

            if (array_key_exists('delete_other_address', $attributes)) {
                if(($key = array_search($attributes['delete_other_address'], $this->otherAddresses)) !== false) {
                    unset($this->otherAddresses[$key]);
                }
            }
        }

        if(array_key_exists('financial_contacts', $attributes)) {
            $this->financialContacts = $attributes['financial_contacts'];
        } elseif(array_key_exists('add_financial_contact', $attributes) || array_key_exists('delete_financial_contact', $attributes)) {
            $this->financialContacts = $this->schoolLocationContacts()->where('type', 'FINANCE')->pluck('contact_id')->all();
            if (array_key_exists('add_financial_contact', $attributes)) {
                array_push($this->financialContacts, $attributes['add_financial_contact']);
            }

            if (array_key_exists('delete_financial_contact', $attributes)) {
                if(($key = array_search($attributes['delete_financial_contact'], $this->financialContacts)) !== false) {
                    unset($this->financialContacts[$key]);
                }
            }
        }

        if(array_key_exists('technical_contacts', $attributes)) {
            $this->technicalContacts = $attributes['technical_contacts'];
        } elseif(array_key_exists('add_technical_contact', $attributes) || array_key_exists('delete_technical_contact', $attributes)) {
            $this->technicalContacts = $this->schoolLocationContacts()->where('type', 'TECHNICAL')->pluck('contact_id')->all();
            if (array_key_exists('add_technical_contact', $attributes)) {
                array_push($this->technicalContacts, $attributes['add_technical_contact']);
            }

            if (array_key_exists('delete_technical_contact', $attributes)) {
                if(($key = array_search($attributes['delete_technical_contact'], $this->technicalContacts)) !== false) {
                    unset($this->technicalContacts[$key]);
                }
            }
        }

        if(array_key_exists('implementation_contacts', $attributes)) {
            $this->implementationContacts = $attributes['implementation_contacts'];
        } elseif(array_key_exists('add_implementation_contact', $attributes) || array_key_exists('delete_implementation_contact', $attributes)) {
            $this->implementationContacts = $this->schoolLocationContacts()->where('type', 'IMPLEMENTATION')->pluck('contact_id')->all();
            if (array_key_exists('add_implementation_contact', $attributes)) {
                array_push($this->implementationContacts, $attributes['add_implementation_contact']);
            }

            if (array_key_exists('delete_implementation_contact', $attributes)) {
                if(($key = array_search($attributes['delete_implementation_contact'], $this->implementationContacts)) !== false) {
                    unset($this->implementationContacts[$key]);
                }
            }
        }

        if(array_key_exists('other_contacts', $attributes)) {
            $this->otherContacts = $attributes['other_contacts'];
        } elseif(array_key_exists('add_other_contact', $attributes) || array_key_exists('delete_other_contact', $attributes)) {
            $this->otherContacts = $this->schoolLocationContacts()->where('type', 'OTHER')->pluck('contact_id')->all();
            if (array_key_exists('add_other_contact', $attributes)) {
                array_push($this->otherContacts, $attributes['add_other_contact']);
            }

            if (array_key_exists('delete_other_contact', $attributes)) {
                if(($key = array_search($attributes['delete_other_contact'], $this->otherContacts)) !== false) {
                    unset($this->otherContacts[$key]);
                }
            }
        }

        if (array_key_exists('school_id', $attributes) && empty($attributes['school_id'])) {
            $this->setAttribute('school_id', null);
        }
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (SchoolLocation $schoolLocation) {
            $school = $schoolLocation->getAttribute('school_id');
            $customerCode = $schoolLocation->getAttribute('customer_code');
            $userId = $schoolLocation->getAttribute('user_id');

            if (!empty($school) && (empty($customerCode) || empty($userId))) {
                $school = $schoolLocation->school;
                $schoolCustomerCode = $school->getAttribute('customer_code');
                $schoolUserId = $school->getAttribute('user_id');

                if (empty($customerCode) && !empty($schoolCustomerCode)) {
                    $schoolLocation->setAttribute('customer_code', $schoolCustomerCode);
                }

                if (empty($userId) && !empty($schoolUserId)) {
                    $schoolLocation->setAttribute('user_id', $schoolUserId);
                }
            }
        });

        static::created(function (SchoolLocation $schoolLocation){
            (new DemoHelper())->createDemoPartsForSchool($schoolLocation);
        });

        // Progress additional answers
        static::saved(function(SchoolLocation $schoolLocation)
        {
            if ($schoolLocation->sections !== null) {
                $schoolLocation->saveSections();
            }

            if ($schoolLocation->educationLevels !== null) {
                $schoolLocation->saveEducationLevels();
            }

            if ($schoolLocation->schoolYears !== null) {
                $schoolLocation->saveSchoolYears();
            }

            if ($schoolLocation->mainAddresses !== null) {
                $schoolLocation->saveMainAdresses();
            }

            if ($schoolLocation->invoiceAddresses !== null) {
                $schoolLocation->saveInvoiceAdresses();
            }

            if ($schoolLocation->visitAddresses !== null) {
                $schoolLocation->saveVisitAdresses();
            }

            if ($schoolLocation->otherAddresses !== null) {
                $schoolLocation->saveOtherAdresses();
            }

            if ($schoolLocation->financialContacts !== null) {
                $schoolLocation->saveFinancialContacts();
            }

            if ($schoolLocation->technicalContacts !== null) {
                $schoolLocation->saveTechnicalContacts();
            }

            if ($schoolLocation->implementationContacts !== null) {
                $schoolLocation->saveImplementationContacts();
            }

            if ($schoolLocation->otherContacts !== null) {
                $schoolLocation->saveOtherContacts();
            }

            $schoolLocation->dispatchJobs();
        });

        static::deleted(function(SchoolLocation $schoolLocation)
        {
            $schoolLocation->dispatchJobs(true);
        });

        static::updated(function(SchoolLocation $schoolLocation){
           $originalCustomerCode = $schoolLocation->getOriginal('customer_code');
           if($originalCustomerCode !== $schoolLocation->customer_code){
//               logger('change code');
               (new DemoHelper())->changeDemoUsersAsSchoolLocationCustomerCodeChanged($schoolLocation,$originalCustomerCode);
           }
        });

        static::deleting(function(SchoolLocation $schoolLocation){
            if(Str::lower($schoolLocation->getOriginal('customer_code')) === 'tc-tijdelijke-docentaccounts'){
                return false;// the TC tijdelijke docentaccounts school location should not be deleted
            }
        });

        static::updating(function(SchoolLocation $schoolLocation){
            if(Str::lower($schoolLocation->getOriginal('customer_code')) === 'tc-tijdelijke-docentaccounts' &&
                $schoolLocation->customer_code !== $schoolLocation->getOriginal('customer_code')){
                return false;// the TC tijdelijke docentaccounts school location should not be changed
            }
        });
    }

    public function school() {
        return $this->belongsTo('tcCore\School');
    }

    public function licenses() {
        return $this->hasMany('tcCore\License');
    }

    // Account manager
    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    // Users of this school
    public function users() {
        return $this->hasMany('tcCore\User');
    }

    public function schoolLocationIps() {
        return $this->hasMany('tcCore\SchoolLocationIp');
    }

    public function schoolClasses() {
        return $this->hasMany('tcCore\SchoolClass');
    }

    public function schoolLocationSections() {
        return $this->hasMany('tcCore\SchoolLocationSection', 'school_location_id');
    }

    public function sharedSections()
    {
        return $this->belongsToMany(Section::class,'school_location_shared_sections','school_location_id','section_id')->withTimestamps();
    }

    protected function saveSections()
    {
        $schoolLocationSections = $this->schoolLocationSections()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationSections, $this->sections, 'section_id', function ($schoolLocation, $sectionId) {
            SchoolLocationSection::create(['school_location_id' => $schoolLocation->getKey(), 'section_id' => $sectionId]);
        });

        $this->sections = null;
    }


    public function schoolLocationEducationLevels() {
        return $this->hasMany('tcCore\SchoolLocationEducationLevel', 'school_location_id');
    }

    public function educationLevels() {
        return $this->belongsToMany('tcCore\EducationLevel', 'school_location_education_levels', 'school_location_id', 'education_level_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }


    protected function saveEducationLevels() {
        $schoolLocationEducationLevels = $this->schoolLocationEducationLevels()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationEducationLevels, $this->educationLevels, 'education_level_id', function($schoolLocation, $educationLevelId) {
            SchoolLocationEducationLevel::create(['school_location_id' => $schoolLocation->getKey(), 'education_level_id' => $educationLevelId]);
        });

        $this->educationLevels = null;
    }

    public function schoolLocationSchoolYears() {
        return $this->hasMany('tcCore\SchoolLocationSchoolYear', 'school_location_id');
    }

    protected function saveSchoolYears() {
        $schoolLocationSchoolYears = $this->schoolLocationSchoolYears()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationSchoolYears, $this->schoolYears, 'school_year_id', function($schoolLocation, $schoolYearId) {
            SchoolLocationSchoolYear::create(['school_location_id' => $schoolLocation->getKey(), 'school_year_id' => $schoolYearId]);
        });

        $this->schoolYears = null;
    }

    public function schoolLocationAddresses() {
        return $this->hasMany('tcCore\SchoolLocationAddress', 'school_location_id');
    }

    private function saveMainAdresses() {
        $mainAddresses = $this->schoolLocationAddresses()->withTrashed()->where('type', '=', 'MAIN')->get();

        $this->syncTcRelation($mainAddresses, $this->mainAddresses, 'address_id', function($schoolLocation, $addressId) {
            SchoolLocationAddress::create(['address_id' => $addressId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'MAIN']);
        });

        $this->mainAddresses = null;
    }

    private function saveInvoiceAdresses() {
        $invoiceAddresses = $this->schoolLocationAddresses()->withTrashed()->where('type', '=', 'INVOICE')->get();

        $this->syncTcRelation($invoiceAddresses, $this->invoiceAddresses, 'address_id', function($schoolLocation, $addressId) {
            SchoolLocationAddress::create(['address_id' => $addressId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'INVOICE']);
        });

        $this->invoiceAddresses = null;
    }

    private function saveVisitAdresses() {
        $visitAddresses = $this->schoolLocationAddresses()->withTrashed()->where('type', '=', 'VISIT')->get();

        $this->syncTcRelation($visitAddresses, $this->visitAddresses, 'address_id', function($schoolLocation, $addressId) {
            SchoolLocationAddress::create(['address_id' => $addressId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'VISIT']);
        });

        $this->visitAddresses = null;
    }

    private function saveOtherAdresses() {
        $otherAddresses = $this->schoolLocationAddresses()->withTrashed()->where('type', '=', 'OTHER')->get();

        $this->syncTcRelation($otherAddresses, $this->otherAddresses, 'user_id', function($schoolLocation, $addressId) {
            SchoolLocationAddress::create(['address_id' => $addressId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'OTHER']);
        });

        $this->otherAddresses = null;
    }

    public function schoolLocationContacts() {
        return $this->hasMany('tcCore\SchoolLocationContact', 'school_location_id');
    }

    private function saveFinancialContacts() {
        $financialContacts = $this->schoolLocationContacts()->withTrashed()->where('type', '=', 'FINANCE')->get();

        $this->syncTcRelation($financialContacts, $this->financialContacts, 'contact_id', function($schoolLocation, $contactId) {
            SchoolLocationContact::create(['contact_id' => $contactId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'FINANCE']);
        });

        $this->financialContacts = null;
    }

    private function saveTechnicalContacts() {
        $technicalContacts = $this->schoolLocationContacts()->withTrashed()->where('type', '=', 'TECHNICAL')->get();

        $this->syncTcRelation($technicalContacts, $this->technicalContacts, 'contact_id', function($schoolLocation, $contactId) {
            SchoolLocationContact::create(['contact_id' => $contactId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'TECHNICAL']);
        });

        $this->technicalContacts = null;
    }

    private function saveImplementationContacts() {
        $implementationContacts = $this->schoolLocationContacts()->withTrashed()->where('type', '=', 'IMPLEMENTATION')->get();

        $this->syncTcRelation($implementationContacts, $this->implementationContacts, 'contact_id', function($schoolLocation, $contactId) {
            SchoolLocationContact::create(['contact_id' => $contactId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'IMPLEMENTATION']);
        });

        $this->implementationContacts = null;
    }

    private function saveOtherContacts() {
        $otherContacts = $this->schoolLocationContact()->withTrashed()->where('type', '=', 'OTHER')->get();

        $this->syncTcRelation($otherContacts, $this->otherContacts, 'contact_id', function($schoolLocation, $contactId) {
            SchoolLocationContact::create(['contact_id' => $contactId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'OTHER']);
        });

        $this->otherContacts = null;
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
            })->pluck('id')->all();

            $query->where(function ($query) use ($schoolIds, $userId) {
                $query->whereIn('school_id', $schoolIds)
                    ->orWhere('user_id', $userId);
            });
        } elseif (!in_array('Administrator', $roles)) {
            $user = ActingAsHelper::getInstance()->getUser();
        if ($user->getAttribute('school_id') !== null && $user->getAttribute('school_location_id') !== null) {
                $query->where(function ($query) use ($user) {
                    $query->where('id', $user->getAttribute('school_location_id'))
                        ->orWhere('school_id', $user->getAttribute('school_id'));
                });
            } elseif ($user->getAttribute('school_location_id') !== null) {
                $query->where('id', $user->getAttribute('school_location_id'));
            } elseif ($user->getAttribute('school_id') !== null) {
                $query->where('school_id', $user->getAttribute('school_id'));
            }
        }

        foreach($filters as $key => $value) {
            switch($key) {
                default:
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'name':
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
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
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
            })->pluck('id')->all();

            return (in_array($this->getAttribute('school_id'), $schoolIds) || $this->getAttribute('user_id') == $userId);
        }

        if (in_array('School manager', $roles)) {
            $user = Auth::user();
            return (($this->getKey() == $user->getAttribute('school_location_id') && $user->getAttribute('school_location_id') !== null) || ($this->getAttribute('school_id') == $user->getAttribute('school_id')) && $user->getAttribute('school_id') !== null);
        }
        if (in_array('Teacher', $roles)) {
            $user = Auth::user();
            return (($this->getKey() == $user->getAttribute('school_location_id') && $user->getAttribute('school_location_id') !== null) || ($this->getAttribute('school_id') == $user->getAttribute('school_id')) && $user->getAttribute('school_id') !== null);
        }

        return false;
    }

    public function canAccessBoundResource($request, Closure $next) {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to school location denied');
    }

    protected function dispatchJobs($isDeleted = false) {
        $triggerParent = false;
        if ($isDeleted === false) {
            foreach (array('school_id', 'count_active_teachers', 'count_active_licenses', 'count_expired_licenses', 'count_licenses', 'count_questions', 'count_students', 'count_teachers', 'count_tests', 'count_test_taken') as $variable) {
                if ($this->getAttribute($variable) !== $this->getOriginal($variable)) {
                    $triggerParent = true;
                    break;
                }
            }
        }

        $triggerAccountManager = false;
        if ($isDeleted === false) {
            foreach (array('user_id', 'count_active_licenses', 'count_expired_licenses', 'count_licenses', 'count_students', 'count_teachers') as $variable) {
                if ($this->getAttribute($variable) !== $this->getOriginal($variable)) {
                    $triggerAccountManager = true;
                    break;
                }
            }
        }

        $school = null;
        if ($isDeleted || $triggerAccountManager || $triggerParent) {
            $school = $this->school;
        }

        if ($isDeleted || $triggerParent) {

            if ($isDeleted || $this->getAttribute('school_id') !== $this->getOriginal('school_id')) {
                $prevSchool = School::find($this->getOriginal('school_id'));

                if ($school !== null) {
                    Queue::push(new CountSchoolActiveTeachers($school));
                    Queue::push(new CountSchoolActiveLicenses($school));
                    Queue::push(new CountSchoolExpiredLicenses($school));
                    Queue::push(new CountSchoolLicenses($school));
                    Queue::push(new CountSchoolQuestions($school));
                    Queue::push(new CountSchoolStudents($school));
                    Queue::push(new CountSchoolTeachers($school));
                    Queue::push(new CountSchoolTests($school));
                    Queue::push(new CountSchoolTestsTaken($school));
                }

                if ($prevSchool !== null) {
                    Queue::push(new CountSchoolActiveTeachers($prevSchool));
                    Queue::push(new CountSchoolActiveLicenses($prevSchool));
                    Queue::push(new CountSchoolExpiredLicenses($prevSchool));
                    Queue::push(new CountSchoolLicenses($prevSchool));
                    Queue::push(new CountSchoolQuestions($prevSchool));
                    Queue::push(new CountSchoolStudents($prevSchool));
                    Queue::push(new CountSchoolTeachers($prevSchool));
                    Queue::push(new CountSchoolTests($prevSchool));
                    Queue::push(new CountSchoolTestsTaken($prevSchool));
                }
            } elseif($isDeleted === false && $school !== null) {
                if ($this->getAttribute('count_active_teachers') !== $this->getOriginal('count_active_teachers')) {
                    Queue::push(new CountSchoolActiveTeachers($school));
                }

                if ($this->getAttribute('count_active_licenses') !== $this->getOriginal('count_active_licenses')) {
                    Queue::push(new CountSchoolActiveLicenses($school));
                }

                if ($this->getAttribute('count_expired_licenses') !== $this->getOriginal('count_expired_licenses')) {
                    Queue::push(new CountSchoolExpiredLicenses($school));
                }

                if ($this->getAttribute('count_licenses') !== $this->getOriginal('count_licenses')) {
                    Queue::push(new CountSchoolLicenses($school));
                }

                if ($this->getAttribute('count_questions') !== $this->getOriginal('count_questions')) {
                    Queue::push(new CountSchoolQuestions($school));
                }

                if ($this->getAttribute('count_students') !== $this->getOriginal('count_students')) {
                    Queue::push(new CountSchoolStudents($school));
                }

                if ($this->getAttribute('count_teachers') !== $this->getOriginal('count_teachers')) {
                    Queue::push(new CountSchoolTeachers($school));
                }

                if ($this->getAttribute('count_tests') !== $this->getOriginal('count_tests')) {
                    Queue::push(new CountSchoolTests($school));
                }

                if ($this->getAttribute('count_test_taken') !== $this->getOriginal('count_test_taken')) {
                    Queue::push(new CountSchoolTestsTaken($school));
                }
            }
        }

        if (($isDeleted || $triggerAccountManager) && $school === null) {
            $user = $this->user;

            if ($isDeleted || $this->getAttribute('user_id') !== $this->getOriginal('user_id')) {
                $prevUser = User::find($this->getOriginal('user_id'));

                if ($user !== null) {
                    Queue::push(new CountAccountManagerAccounts($user));
                    Queue::push(new CountAccountManagerActiveLicenses($user));
                    Queue::push(new CountAccountManagerExpiredLicenses($user));
                    Queue::push(new CountAccountManagerLicenses($user));
                    Queue::push(new CountAccountManagerStudents($user));
                    Queue::push(new CountAccountManagerTeachers($user));
                }


                if ($prevUser !== null) {
                    Queue::push(new CountAccountManagerAccounts($prevUser));
                    Queue::push(new CountAccountManagerActiveLicenses($prevUser));
                    Queue::push(new CountAccountManagerExpiredLicenses($prevUser));
                    Queue::push(new CountAccountManagerLicenses($prevUser));
                    Queue::push(new CountAccountManagerStudents($prevUser));
                    Queue::push(new CountAccountManagerTeachers($prevUser));
                }
            } elseif($isDeleted === false && $user !== null) {
                if ($this->getAttribute('count_active_licenses') !== $this->getOriginal('count_active_licenses')) {
                    Queue::push(new CountAccountManagerActiveLicenses($user));
                }

                if ($this->getAttribute('count_expired_licenses') !== $this->getOriginal('count_expired_licenses')) {
                    Queue::push(new CountAccountManagerExpiredLicenses($user));
                }

                if ($this->getAttribute('count_licenses') !== $this->getOriginal('count_licenses')) {
                    Queue::push(new CountAccountManagerLicenses($user));
                }

                if ($this->getAttribute('count_students') !== $this->getOriginal('count_students')) {
                    Queue::push(new CountAccountManagerStudents($user));
                }

                if ($this->getAttribute('count_teachers') !== $this->getOriginal('count_teachers')) {
                    Queue::push(new CountAccountManagerTeachers($user));
                }
            }
        }
    }


}
