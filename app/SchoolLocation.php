<?php namespace tcCore;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Http\Enums\SchoolLocationFeatureSetting;
use tcCore\Http\Enums\TestPackages;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\GlobalStateHelper;
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
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Mail\SendSamlNoMailAddressInRequestDetectedMail;
use tcCore\Traits\UuidTrait;
use tcCore\Traits\HasFeatureSettings;

class SchoolLocation extends BaseModel implements AccessCheckable
{

    use SoftDeletes;
    use UuidTrait;
    use HasFeatureSettings;

    const LVS_MAGISTER = 'Magister';
    const LVS_SOMTODAY = 'SOMTODAY';
    const SSO_ENTREE = 'Entreefederatie';
    const LICENSE_TYPE_TRIAL = 'TRIAL';
    const LICENSE_TYPE_CLIENT = 'CLIENT';
    const FEATURE_SETTING_ENUM = SchoolLocationFeatureSetting::class;

    protected $casts = [
        'uuid'                       => EfficientUuid::class,
        'allow_inbrowser_testing'    => 'boolean',
        'intense'                    => 'boolean',
        'lvs'                        => 'boolean',
        'lvs_active'                 => 'boolean',
        'sso'                        => 'boolean',
        'sso_active'                 => 'boolean',
        'lvs_active_no_mail_allowed' => 'boolean',
        'school_language'            => 'string',
        'auto_uwlr_import'           => 'boolean',
        'auto_uwlr_last_import'      => 'timestamp',
        'deleted_at'                 => 'datetime',
        'no_mail_request_detected'   => 'datetime',
    ];

    protected $appends = ['school_language_cake', 'feature_settings'];

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
    protected $fillable = [
        'customer_code',
        'name',
        'school_id',
        'grading_scale_id',
        'user_id',
        'number_of_students',
        'number_of_teachers',
        'activated',
        'main_address',
        'main_postal',
        'main_city',
        'main_country',
        'invoice_address',
        'invoice_postal',
        'invoice_city',
        'invoice_country',
        'visit_address',
        'visit_postal',
        'visit_city',
        'visit_country',
        'is_rtti_school_location',
        'external_main_code',
        'external_sub_code',
        'is_open_source_content_creator',
        'is_allowed_to_view_open_source_content',
        'allow_inbrowser_testing',
        'allow_new_player_access',
        'lvs_active',
        'lvs_type',
        'sso',
        'sso_type',
        'sso_active',
        'lvs_authorization_key',
        'school_language',
        'company_id',
        'allow_guest_accounts',
        'allow_new_student_environment',
        'allow_new_question_editor',
        'keep_out_of_school_location_report',
        'main_phonenumber',
        'internetaddress',
        'show_exam_material',
        'show_cito_quick_test_start',
        'show_national_item_bank',
        'allow_wsc',
        'allow_writing_assignment',
        'license_type',
        'auto_uwlr_import',
        'auto_uwlr_import_status',
        'auto_uwlr_last_import',
        'allow_cms_write_down_wsc_toggle',
        'allow_new_test_take_detail_page',
        'allow_mr_chadd',
        'allow_new_test_taken_pages',
        'block_local_login',
        'allow_relation_question',
    ];

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

    public function getSchoolLanguageCakeAttribute()
    {
        if ($this->school_language === 'en') {
            return 'eng';
        }
        if ($this->school_language === 'nl') {
            return 'nld';
        }
        return $this->school_language;
    }

    public function getWscLanguageAttribute()
    {
        return $this->school_language === 'nl' ? 'nl_NL' : 'en_GB';
    }

    public function fill(array $attributes)
    {
        $this->fillFeatureSettings($attributes);

        parent::fill($attributes);

        if (array_key_exists('school_years', $attributes)) {
            $this->schoolYears = $attributes['school_years'];
        } elseif (array_key_exists('add_school_year', $attributes) || array_key_exists('delete_school_year',
                $attributes)) {
            $this->schoolYears = $this->schoolLocationSchoolYears()->pluck('school_year_id')->all();
            if (array_key_exists('add_school_year', $attributes)) {
                array_push($this->schoolYears, $attributes['add_school_year']);
            }

            if (array_key_exists('delete_school_year', $attributes)) {
                if (($key = array_search($attributes['delete_school_year'], $this->schoolYears)) !== false) {
                    unset($this->schoolYears[$key]);
                }
            }
        }

        if (array_key_exists('sections', $attributes)) {
            $this->sections = $attributes['sections'];
        } elseif (array_key_exists('add_section', $attributes) || array_key_exists('delete_section', $attributes)) {
            $this->sections = $this->schoolLocationSections()->pluck('section_id')->all();
            if (array_key_exists('add_section', $attributes)) {
                array_push($this->sections, $attributes['add_section']);
            }

            if (array_key_exists('delete_section', $attributes)) {
                if (($key = array_search($attributes['delete_section'], $this->sections)) !== false) {
                    unset($this->sections[$key]);
                }
            }
        }

        if (array_key_exists('education_levels', $attributes)) {
            $this->educationLevels = $attributes['education_levels'];
        } elseif (array_key_exists('add_education_level', $attributes) || array_key_exists('delete_education_level',
                $attributes)) {
            $this->educationLevels = $this->schoolLocationEducationLevels()->pluck('education_level_id')->all();
            if (array_key_exists('add_education_level', $attributes)) {
                array_push($this->educationLevels, $attributes['add_education_level']);
            }

            if (array_key_exists('delete_education_level', $attributes)) {
                if (($key = array_search($attributes['delete_education_level'], $this->educationLevels)) !== false) {
                    unset($this->educationLevels[$key]);
                }
            }
        }

        if (array_key_exists('main_addresses', $attributes)) {
            $this->mainAddresses = $attributes['main_addresses'];
        } elseif (array_key_exists('add_main_address', $attributes) || array_key_exists('delete_main_address',
                $attributes)) {
            $this->mainAddresses = $this->schoolLocationAddresses()->where('type', 'MAIN')->pluck('address_id')->all();
            if (array_key_exists('add_main_address', $attributes)) {
                array_push($this->mainAddresses, $attributes['add_main_address']);
            }

            if (array_key_exists('delete_main_address', $attributes)) {
                if (($key = array_search($attributes['delete_main_address'], $this->mainAddresses)) !== false) {
                    unset($this->mainAddresses[$key]);
                }
            }
        }

        if (array_key_exists('invoice_addresses', $attributes)) {
            $this->invoiceAddresses = $attributes['invoice_addresses'];
        } elseif (array_key_exists('add_invoice_address', $attributes) || array_key_exists('delete_invoice_address',
                $attributes)) {
            $this->invoiceAddresses = $this->schoolLocationAddresses()->where('type',
                'INVOICE')->pluck('address_id')->all();
            if (array_key_exists('add_invoice_address', $attributes)) {
                array_push($this->invoiceAddresses, $attributes['add_invoice_address']);
            }

            if (array_key_exists('delete_invoice_address', $attributes)) {
                if (($key = array_search($attributes['delete_invoice_address'], $this->invoiceAddresses)) !== false) {
                    unset($this->invoiceAddresses[$key]);
                }
            }
        }

        if (array_key_exists('visit_addresses', $attributes)) {
            $this->visitAddresses = $attributes['visit_addresses'];
        } elseif (array_key_exists('add_visit_address', $attributes) || array_key_exists('delete_visit_address',
                $attributes)) {
            $this->visitAddresses = $this->schoolLocationAddresses()->where('type',
                'VISIT')->pluck('address_id')->all();
            if (array_key_exists('add_visit_address', $attributes)) {
                array_push($this->visitAddresses, $attributes['add_visit_address']);
            }

            if (array_key_exists('delete_visit_address', $attributes)) {
                if (($key = array_search($attributes['delete_visit_address'], $this->visitAddresses)) !== false) {
                    unset($this->visitAddresses[$key]);
                }
            }
        }

        if (array_key_exists('other_addresses', $attributes)) {
            $this->otherAddresses = $attributes['other_addresses'];
        } elseif (array_key_exists('add_other_address', $attributes) || array_key_exists('delete_other_address',
                $attributes)) {
            $this->otherAddresses = $this->schoolLocationAddresses()->where('type',
                'OTHER')->pluck('address_id')->all();
            if (array_key_exists('add_other_address', $attributes)) {
                array_push($this->otherAddresses, $attributes['add_other_address']);
            }

            if (array_key_exists('delete_other_address', $attributes)) {
                if (($key = array_search($attributes['delete_other_address'], $this->otherAddresses)) !== false) {
                    unset($this->otherAddresses[$key]);
                }
            }
        }

        if (array_key_exists('financial_contacts', $attributes)) {
            $this->financialContacts = $attributes['financial_contacts'];
        } elseif (array_key_exists('add_financial_contact', $attributes) || array_key_exists('delete_financial_contact',
                $attributes)) {
            $this->financialContacts = $this->schoolLocationContacts()->where('type',
                'FINANCE')->pluck('contact_id')->all();
            if (array_key_exists('add_financial_contact', $attributes)) {
                array_push($this->financialContacts, $attributes['add_financial_contact']);
            }

            if (array_key_exists('delete_financial_contact', $attributes)) {
                if (($key = array_search($attributes['delete_financial_contact'],
                        $this->financialContacts)) !== false) {
                    unset($this->financialContacts[$key]);
                }
            }
        }

        if (array_key_exists('technical_contacts', $attributes)) {
            $this->technicalContacts = $attributes['technical_contacts'];
        } elseif (array_key_exists('add_technical_contact', $attributes) || array_key_exists('delete_technical_contact',
                $attributes)) {
            $this->technicalContacts = $this->schoolLocationContacts()->where('type',
                'TECHNICAL')->pluck('contact_id')->all();
            if (array_key_exists('add_technical_contact', $attributes)) {
                array_push($this->technicalContacts, $attributes['add_technical_contact']);
            }

            if (array_key_exists('delete_technical_contact', $attributes)) {
                if (($key = array_search($attributes['delete_technical_contact'],
                        $this->technicalContacts)) !== false) {
                    unset($this->technicalContacts[$key]);
                }
            }
        }

        if (array_key_exists('implementation_contacts', $attributes)) {
            $this->implementationContacts = $attributes['implementation_contacts'];
        } elseif (array_key_exists('add_implementation_contact',
                $attributes) || array_key_exists('delete_implementation_contact', $attributes)) {
            $this->implementationContacts = $this->schoolLocationContacts()->where('type',
                'IMPLEMENTATION')->pluck('contact_id')->all();
            if (array_key_exists('add_implementation_contact', $attributes)) {
                array_push($this->implementationContacts, $attributes['add_implementation_contact']);
            }

            if (array_key_exists('delete_implementation_contact', $attributes)) {
                if (($key = array_search($attributes['delete_implementation_contact'],
                        $this->implementationContacts)) !== false) {
                    unset($this->implementationContacts[$key]);
                }
            }
        }

        if (array_key_exists('other_contacts', $attributes)) {
            $this->otherContacts = $attributes['other_contacts'];
        } elseif (array_key_exists('add_other_contact', $attributes) || array_key_exists('delete_other_contact',
                $attributes)) {
            $this->otherContacts = $this->schoolLocationContacts()->where('type', 'OTHER')->pluck('contact_id')->all();
            if (array_key_exists('add_other_contact', $attributes)) {
                array_push($this->otherContacts, $attributes['add_other_contact']);
            }

            if (array_key_exists('delete_other_contact', $attributes)) {
                if (($key = array_search($attributes['delete_other_contact'], $this->otherContacts)) !== false) {
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

        static::created(function (SchoolLocation $schoolLocation) {
            $schoolLocation->created = true;
        });

        // Progress additional answers
        static::saved(function (SchoolLocation $schoolLocation) {

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

            if (isset($schoolLocation->created) && $schoolLocation->created) {
                $origAuthUser = Auth::user();
                if (SchoolLocationSection::where('school_location_id', $schoolLocation->getKey())->count() <= 1) { // demo could be there
                    $user = $schoolLocation->addDefaultSchoolManagerIfNeeded();
                    if ($user) {
                        Auth::login($user);
                    }
                    $schoolLocation = $schoolLocation->addSchoolLocationExtras();
                }
                if (GlobalStateHelper::getInstance()->hasPreventDemoEnvironmentCreationForSchoolLocation() === false) {
                    (new DemoHelper())->createDemoForSchoolLocationIfNeeded($schoolLocation);
                }
                if ($origAuthUser) {
                    Auth::login($origAuthUser);
                }
            }

            $schoolLocation->dispatchJobs();
        });

        static::deleted(function (SchoolLocation $schoolLocation) {
            $schoolLocation->sharedSections()->detach();
            foreach ($schoolLocation->schoolLocationSections()->get() as $sharedSection) {
                SchoolLocationSharedSection::where('section_id', $sharedSection->section_id)->delete();
            }
            $schoolLocation->dispatchJobs(true);
        });

        static::updated(function (SchoolLocation $schoolLocation) {
            $originalCustomerCode = $schoolLocation->getOriginal('customer_code');
            if ($originalCustomerCode !== $schoolLocation->customer_code) {
//               logger('change code');
                (new DemoHelper())->changeDemoUsersAsSchoolLocationCustomerCodeChanged($schoolLocation,
                    $originalCustomerCode);
            }
            $schoolLocation->handleLicenseTypeUpdate();
        });

        static::deleting(function (SchoolLocation $schoolLocation) {
            if (Str::lower($schoolLocation->getOriginal('customer_code')) === 'tc-tijdelijke-docentaccounts') {
                return false;// the TC tijdelijke docentaccounts school location should not be deleted
            }
        });

        static::updating(function (SchoolLocation $schoolLocation) {
            if (Str::lower($schoolLocation->getOriginal('customer_code')) === 'tc-tijdelijke-docentaccounts' &&
                $schoolLocation->customer_code !== $schoolLocation->getOriginal('customer_code')) {
                return false;// the TC tijdelijke docentaccounts school location should not be changed
            }
        });
    }

    public function addSchoolLocationExtras()
    {
        $origAuthUser = Auth::user();
        $year = Date("Y");
        $nextYear = $year + 1;
        if (Date("m") < 8) {
            $nextYear = $year;
            $year--;
        }
        $userId = User::where('school_location_id', $this->getKey())->value('id');
        if ($userId) {
            Auth::loginUsingId($userId);
        }
        $this
            ->addSchoolYearAndPeriod($year, '01-08-' . $year, '31-07-' . $nextYear)
            ->addDefaultSectionsAndSubjects("VO");
        if ($origAuthUser) {
            Auth::login($origAuthUser);
        } else {
            Auth::logout();
        }
        return $this;
    }

    public function school()
    {
        return $this->belongsTo('tcCore\School');
    }

    public function licenses()
    {
        return $this->hasMany('tcCore\License');
    }

    // Account manager
    public function user()
    {
        return $this->belongsTo('tcCore\User');
    }

    // Users of this school
    public function users()
    {
        return $this->hasMany('tcCore\User');
    }

    public function schoolManagers()
    {
        return $this->users()->whereRelation('roles', 'name', '=', 'School manager');
    }

    public function schoolLocationIps()
    {
        return $this->hasMany('tcCore\SchoolLocationIp');
    }

    public function schoolClasses()
    {
        return $this->hasMany('tcCore\SchoolClass');
    }

    public function schoolLocationSections()
    {
        return $this->hasMany('tcCore\SchoolLocationSection', 'school_location_id');
    }

    public function sharedSections()
    {
        return $this->belongsToMany(Section::class, 'school_location_shared_sections', 'school_location_id',
            'section_id')->withTimestamps();
    }

    public function schoolYears()
    {
        return $this->belongsToMany(SchoolYear::class, 'school_location_school_years', 'school_location_id');
    }

    public function trialPeriods()
    {
        return $this->hasMany(TrialPeriod::class, 'school_location_id');
    }

    protected function saveSections()
    {
        $schoolLocationSections = $this->schoolLocationSections()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationSections, $this->sections, 'section_id',
            function ($schoolLocation, $sectionId) {
                SchoolLocationSection::create([
                    'school_location_id' => $schoolLocation->getKey(), 'section_id' => $sectionId
                ]);
            });

        $this->sections = null;
    }

    public function getPeriods()
    {
        return Period::select('periods.*')
            ->join('school_years', 'school_years.id', '=', 'periods.school_year_id')
            ->join('school_location_school_years', function ($join) {
                $join->on('school_location_school_years.school_year_id', '=', 'school_years.id')
                    ->where('school_location_id', $this->id);
            })->distinct()->whereNull('school_years.deleted_at')->get();
    }

    public function schoolLocationEducationLevels()
    {
        return $this->hasMany('tcCore\SchoolLocationEducationLevel', 'school_location_id');
    }

    public function educationLevels()
    {
        return $this->belongsToMany('tcCore\EducationLevel', 'school_location_education_levels', 'school_location_id',
            'education_level_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }


    protected function saveEducationLevels()
    {
        $schoolLocationEducationLevels = $this->schoolLocationEducationLevels()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationEducationLevels, $this->educationLevels, 'education_level_id',
            function ($schoolLocation, $educationLevelId) {
                SchoolLocationEducationLevel::create([
                    'school_location_id' => $schoolLocation->getKey(), 'education_level_id' => $educationLevelId
                ]);
            });

        $this->educationLevels = null;
    }

    public function schoolLocationSchoolYears()
    {
        return $this->hasMany('tcCore\SchoolLocationSchoolYear', 'school_location_id');
    }

    protected function saveSchoolYears()
    {
        $schoolLocationSchoolYears = $this->schoolLocationSchoolYears()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationSchoolYears, $this->schoolYears, 'school_year_id',
            function ($schoolLocation, $schoolYearId) {
                SchoolLocationSchoolYear::create([
                    'school_location_id' => $schoolLocation->getKey(), 'school_year_id' => $schoolYearId
                ]);
            });

        $this->schoolYears = null;
    }

    public function schoolLocationAddresses()
    {
        return $this->hasMany('tcCore\SchoolLocationAddress', 'school_location_id');
    }

    private function saveMainAdresses()
    {
        $mainAddresses = $this->schoolLocationAddresses()->withTrashed()->where('type', '=', 'MAIN')->get();

        $this->syncTcRelation($mainAddresses, $this->mainAddresses, 'address_id',
            function ($schoolLocation, $addressId) {
                SchoolLocationAddress::create([
                    'address_id' => $addressId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'MAIN'
                ]);
            });

        $this->mainAddresses = null;
    }

    private function saveInvoiceAdresses()
    {
        $invoiceAddresses = $this->schoolLocationAddresses()->withTrashed()->where('type', '=', 'INVOICE')->get();

        $this->syncTcRelation($invoiceAddresses, $this->invoiceAddresses, 'address_id',
            function ($schoolLocation, $addressId) {
                SchoolLocationAddress::create([
                    'address_id' => $addressId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'INVOICE'
                ]);
            });

        $this->invoiceAddresses = null;
    }

    private function saveVisitAdresses()
    {
        $visitAddresses = $this->schoolLocationAddresses()->withTrashed()->where('type', '=', 'VISIT')->get();

        $this->syncTcRelation($visitAddresses, $this->visitAddresses, 'address_id',
            function ($schoolLocation, $addressId) {
                SchoolLocationAddress::create([
                    'address_id' => $addressId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'VISIT'
                ]);
            });

        $this->visitAddresses = null;
    }

    private function saveOtherAdresses()
    {
        $otherAddresses = $this->schoolLocationAddresses()->withTrashed()->where('type', '=', 'OTHER')->get();

        $this->syncTcRelation($otherAddresses, $this->otherAddresses, 'user_id',
            function ($schoolLocation, $addressId) {
                SchoolLocationAddress::create([
                    'address_id' => $addressId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'OTHER'
                ]);
            });

        $this->otherAddresses = null;
    }

    public function schoolLocationContacts()
    {
        return $this->hasMany('tcCore\SchoolLocationContact', 'school_location_id');
    }

    private function saveFinancialContacts()
    {
        $financialContacts = $this->schoolLocationContacts()->withTrashed()->where('type', '=', 'FINANCE')->get();

        $this->syncTcRelation($financialContacts, $this->financialContacts, 'contact_id',
            function ($schoolLocation, $contactId) {
                SchoolLocationContact::create([
                    'contact_id' => $contactId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'FINANCE'
                ]);
            });

        $this->financialContacts = null;
    }

    private function saveTechnicalContacts()
    {
        $technicalContacts = $this->schoolLocationContacts()->withTrashed()->where('type', '=', 'TECHNICAL')->get();

        $this->syncTcRelation($technicalContacts, $this->technicalContacts, 'contact_id',
            function ($schoolLocation, $contactId) {
                SchoolLocationContact::create([
                    'contact_id' => $contactId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'TECHNICAL'
                ]);
            });

        $this->technicalContacts = null;
    }

    private function saveImplementationContacts()
    {
        $implementationContacts = $this->schoolLocationContacts()->withTrashed()->where('type', '=',
            'IMPLEMENTATION')->get();

        $this->syncTcRelation($implementationContacts, $this->implementationContacts, 'contact_id',
            function ($schoolLocation, $contactId) {
                SchoolLocationContact::create([
                    'contact_id' => $contactId, 'school_location_id' => $schoolLocation->getKey(),
                    'type'       => 'IMPLEMENTATION'
                ]);
            });

        $this->implementationContacts = null;
    }

    private function saveOtherContacts()
    {
        $otherContacts = $this->schoolLocationContact()->withTrashed()->where('type', '=', 'OTHER')->get();

        $this->syncTcRelation($otherContacts, $this->otherContacts, 'contact_id',
            function ($schoolLocation, $contactId) {
                SchoolLocationContact::create([
                    'contact_id' => $contactId, 'school_location_id' => $schoolLocation->getKey(), 'type' => 'OTHER'
                ]);
            });

        $this->otherContacts = null;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        if (!in_array('Administrator', $roles) && (in_array('Account manager', $roles))) {
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

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'id':
                    $query->where('school_locations.id', '=', $value);
                    break;
                case 'combined_search':
                    $query->when($value, function ($query, $value) {
                        return $query->where(function ($query) use ($value) {
                            $query->where('customer_code', 'LIKE', "%$value%")
                                ->orWhere('name', 'like', "%$value%")
                                ->orWhereIn('school_id',
                                    School::where('schools.name', 'LIKE', "%$value%")
                                        ->select('id')
                                )
                                ->orWhereRaw("TRIM(CONCAT_WS(' ', COALESCE(external_main_code,''), COALESCE(external_sub_code,''))) LIKE '%$value%'");
                        });
                    });
                    break;
                case 'name':
                    $query->where('school_locations.name', 'LIKE', '%' . $value . '%');
                    break;
                case 'license_type':
                    $query->whereIn('school_locations.license_type', Arr::wrap($value));
                    break;
                case 'lvs_active':
                    $query->whereIn('school_locations.lvs_active', Arr::wrap($value));
                    break;
                case 'sso_active':
                    $query->whereIn('school_locations.sso_active', Arr::wrap($value));
                    break;
                default:
                    break;
            }
        }

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
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
            switch (strtolower($key)) {
                case 'id':
                case 'name':
                case 'customer_code':
                case 'main_city':
                case 'external_main_code':
                case 'lvs_active':
                case 'sso_active':
                case 'count_questions':
                    $query->orderBy($key, $value);
                    break;
                case 'school_name':
                    $query->orderBy(
                        School::select('schools.name')
                            ->whereColumn('schools.id', 'school_locations.school_id')
                            ->orderBy('schools.name', $value)
                            ->take(1),
                        $value
                    );
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

            return (in_array($this->getAttribute('school_id'),
                    $schoolIds) || $this->getAttribute('user_id') == $userId);
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

    public function canAccessBoundResource($request, Closure $next)
    {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to school location denied');
    }

    protected function dispatchJobs($isDeleted = false)
    {
        $triggerParent = false;
        if ($isDeleted === false) {
            foreach (array(
                         'school_id', 'count_active_teachers', 'count_active_licenses', 'count_expired_licenses',
                         'count_licenses', 'count_questions', 'count_students', 'count_teachers', 'count_tests',
                         'count_test_taken'
                     ) as $variable) {
                if ($this->getAttribute($variable) !== $this->getOriginal($variable)) {
                    $triggerParent = true;
                    break;
                }
            }
        }

        $triggerAccountManager = false;
        if ($isDeleted === false) {
            foreach (array(
                         'user_id', 'count_active_licenses', 'count_expired_licenses', 'count_licenses',
                         'count_students', 'count_teachers'
                     ) as $variable) {
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
            } elseif ($isDeleted === false && $school !== null) {
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
            } elseif ($isDeleted === false && $user !== null) {
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

    public function hasRunManualImport(): bool
    {
        return UwlrSoapResult::schoolLocationHasRunImport($this);
    }

    public static function getLvsOptions()
    {
        return [SchoolLocation::LVS_MAGISTER, SchoolLocation::LVS_SOMTODAY];
    }

    public static function getSsoOptions()
    {
        return [SchoolLocation::SSO_ENTREE];
    }

    public function belongsToSameSchool(SchoolLocation $schoolLocation)
    {
        return !is_null($schoolLocation->school_id) && $this->school_id === $schoolLocation->school_id;
    }

    public function scopeVoOnly($query)
    {
        return $query->whereIn(
            'id',
            DB::table('school_location_education_levels')
                ->select(DB::raw('DISTINCT school_location_id'))
                ->whereRaw("education_level_id IN (
                    SELECT id FROM education_levels
                        WHERE  NOT NAME IN (
                            'uwlr_education_level','MBO-N1','MBO-N2', 'MBO-N3','MBO-N4','HBO Bachelor','HBO Master','WO Bachelor','WO Master', 'Demo','Groep'))"
                )
        );
    }

    public function scopeActiveOnly($query)
    {
        return $query->where('activated', 1);
    }

    public function scopeWithoutSchoolYear($query, $year)
    {
        return $query->whereNotIn(
            'id',
            SchoolYear::where('year', $year)->get()->map(function ($schoolYear) {
                return $schoolYear->schoolLocations->pluck('id');
            })->flatten()
        );
    }

    public function addSchoolYearAndPeriod($year, $startDate, $endDate)
    {
        $schoolYear = new SchoolYear();

        $schoolYear->fill([
            'year'             => $year,
            'school_locations' => [$this->getKey()],
        ]);
        $schoolYear->save();

        $periodLocation = (new Period());
        $periodLocation->fill([
            'school_year_id'     => $schoolYear->getKey(),
            'name'               => sprintf(
                '%d-%d',
                \Carbon\Carbon::parse($startDate)->year,
                \Carbon\Carbon::parse($endDate)->year
            ),
            'school_location_id' => $this->getKey(),
            'start_date'         => Carbon::parse($startDate),
            'end_date'           => Carbon::parse($endDate),
        ]);
        $periodLocation->save();

        return $this;
    }

    public function addDefaultSchoolManagerIfNeeded()
    {
        // for the time being always needed
        $userFactory = new Factory(new User());
        $user = $userFactory->generate(
            [
                'account_verified'   => Carbon::now(),
                'name'               => sprintf('TLC schoolbeheerder %s', $this->customer_code),
                'name_first'         => '',
                'username'           => sprintf('info+%sSchoolbeheerder@test-correct.nl', $this->customer_code),
                'send_welcome_email' => 1,
                'user_roles'         => [6],
                'school_location_id' => $this->getKey(),
            ]
        );
        $user->save();
        return $user;
    }

    public function addDefaultSectionsAndSubjects()
    {
        $this->refresh();
        // get default subjects
        if ($this->educationLevels()->count() > 0) {
            $this->addDefaultSubjectsAndSectionsBasedOnEducationLevels($this->educationLevels()->get());
        }
    }

    public function addDefaultSubjectsAndSectionsBasedOnEducationLevels($educationLevels)
    {
        // get default subjects
        $defaultSubjects = DefaultSubject::where(function ($query) use ($educationLevels) {
            $this->buildEducationLevelQueryLikeStatement($query, $educationLevels);
        })->get();

        $defaultSectionIds = $defaultSubjects->map(function (DefaultSubject $ds) {
            return $ds->default_section_id;
        });

        // we need to check if all sections are not deleted as deletion is based on section and not school location section
        $sectionIds = Section::whereIn('id', $this->schoolLocationSections()->select('section_id'))->pluck('id');
        $subjects = Subject::whereIn('section_id', $sectionIds)->pluck('name', 'id')->map(function ($name, $id) {
            return strtolower($name);
        })->flip();

        // get default sections
        $defaultSections = DefaultSection::whereIn('id', $defaultSectionIds)->get();
        // add sections

        $list = [];
        $defaultSections->each(function (DefaultSection $ds) use (&$list) {
            if ($schoolLocationSection = $this->schoolLocationSections->first(function (SchoolLocationSection $sls) use ($ds) {
                return Str::lower(optional($sls->section)->name) === Str::lower($ds->name);
            })) {
                $section = $schoolLocationSection->section;
            } else {
                $section = Section::create(
                    [
                        'name' => $ds->name,
                        'demo' => $ds->demo,
                    ]
                );
            }
            $list[$ds->getKey()] = $section->getKey();
        });

        // add sections to schoollocation
        $this->sections = array_merge(array_values($sectionIds->toArray() ?? []), array_values($list));
        $this->saveSections();


        // add subjects
        $defaultSubjects->each(function (DefaultSubject $ds) use ($list, $subjects) {
            // NOTE Erik 20220803
            // used to be updateOrCreate, but for some reason both the updated_at and the created_at were adjusted and we don't want that as we want to be able to see from when a subject was
            if (isset($subjects[Str::lower($ds->name)])) {
                Subject::find($subjects[Str::lower($ds->name)])->update([
                    'section_id'      => $list[$ds->default_section_id],
                    'base_subject_id' => $ds->base_subject_id,
                    'abbreviation'    => $ds->abbreviation,
//                    'demo' => $ds->demo,
                ]);
            } else {
                Subject::create(
                    [
                        'name'            => $ds->name,
                        'section_id'      => $list[$ds->default_section_id],
                        'base_subject_id' => $ds->base_subject_id,
                        'abbreviation'    => $ds->abbreviation,
                        'demo'            => $ds->demo,
                    ]
                );
            }
        });
    }

    protected function buildEducationLevelQueryLikeStatement($query, $educationLevels)
    {
        $first = true;
        $educationLevels->each(function (EducationLevel $el) use ($query, &$first) {
            if ($first) {
                $query->where('education_levels', 'like', '%' . $el->name . '%');
                $first = false;
            } else {
                $query->orwhere('education_levels', 'like', '%' . $el->name . '%');
            }
        });
    }

    public function addDefaultSubjectsAndSectionsBasedOnLevel($level)
    {
//        // get default sections
//        $defaultSections = DefaultSection::where('level',$level)->get();
//        // get base subjects for this level
//        $baseSubjects = BaseSubject::forLevel($level)->get();
//        // find the default section ids for these base subjects and make them unique
//        $defaultSectionIds = $baseSubjects->map(function(BaseSubject $bs) {
//            return $bs->default_section_id;
//        })->unique();
//        // get default sections based on the ids
//        $defaultSections = DefaultSection::find($defaultSectionIds->toArray());
//        $sectionIds = [];
//        // create sections based on the default sections
//        $defaultSections->each(function(DefaultSection $ds) use (&$sectionIds){
//            $section = Section::create($ds->toArray());
//            // default section id belongs to the new section id
//            $sectionIds[$ds->getKey()] = $section->getKey();
//        });
//        // create the sections based on the base subjects
//        $baseSubjects->each(function(BaseSubject $bs) use ($sectionIds){
//            // set some extra fields based on the settings passed along
//            Subject::create(
//                  array_merge($bs->toArray(),['section_id' => $sectionIds[$bs->default_section_id], 'base_subject_id' => $bs->getKey()])
//            );
//        });
    }

    public function scopeNoActivePeriodAtDate($query, $date)
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        if (!$date instanceof Carbon) {
            throw new \Exception(
                'date should be a valid date string or an instanceof Carbon'
            );
        }

        return $query->whereNotIn('id',
            Period::where(function ($query) use ($date) {
                return $query
                    ->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
            })
                ->join('school_years', 'school_year_id', 'school_years.id')
                ->whereNull('school_years.deleted_at')
                ->join('school_location_school_years', 'school_location_school_years.school_year_id', 'school_years.id')
                ->whereNull('school_location_school_years.deleted_at')
                ->select('school_location_id')
        );
    }

    private function canSendSamlNoMailAddressInRequestDetectedMail()
    {
        if (empty ($this->no_mail_request_detected)) {
            return true;
        }

        return true; // always as we have some issues with Groevenbeek and we want all the requests
        return ($this->no_mail_request_detected->diffInHours(now()) > 23);
    }

    public function sendSamlNoMailAddresInRequestDetectedMailIfAppropriate($attr = [])
    {
        if ($this->canSendSamlNoMailAddressInRequestDetectedMail() && $this->lvs_active_no_mail_allowed == false) {
            Mail::to(config('mail.from.address'))
                ->send(new SendSamlNoMailAddressInRequestDetectedMail($this->name, sprintf('Waarschuwing gebruiker van %s probeert in te loggen via Entree zonder emailadres.', $this->name), $attr));
            $this->no_mail_request_detected = now();
            $this->save();
        }
    }

    public function canUseCmsWithDrawer(): bool
    {
        return $this->allow_cms_drawer && $this->allow_new_drawing_question;
    }

    public static function getAvailableLicenseTypes(): array
    {
        return self::getPossibleEnumValues('license_type');
    }

    public function hasTrialLicense(): bool
    {
        return $this->license_type === self::LICENSE_TYPE_TRIAL;
    }

    public function hasClientLicense(): bool
    {
        return $this->license_type === self::LICENSE_TYPE_CLIENT;
    }

    private function handleLicenseTypeUpdate()
    {
        if ($this->isDirty('license_type') && $this->license_type === 'CLIENT') {
            TrialPeriod::where('school_location_id', $this->getKey())->delete();
        }
    }

    public function canDelete(User $user)
    {
        return $user->isA('Administrator');
    }

    public function addDefaultSettings()
    {
        SchoolLocationFeatureSetting::settingToDefaultSchool()->each(fn($setting) => $this->{$setting->value} = true);
    }

}
