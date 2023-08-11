<?php namespace tcCore;

use Closure;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Jobs\CountAccountManagerAccounts;
use tcCore\Jobs\CountAccountManagerActiveLicenses;
use tcCore\Jobs\CountAccountManagerExpiredLicenses;
use tcCore\Jobs\CountAccountManagerLicenses;
use tcCore\Jobs\CountAccountManagerStudents;
use tcCore\Jobs\CountAccountManagerTeachers;
use tcCore\Jobs\CountUmbrellaOrganizationActiveLicenses;
use tcCore\Jobs\CountUmbrellaOrganizationActiveTeachers;
use tcCore\Jobs\CountUmbrellaOrganizationExpiredLicenses;
use tcCore\Jobs\CountUmbrellaOrganizationLicenses;
use tcCore\Jobs\CountUmbrellaOrganizationQuestions;
use tcCore\Jobs\CountUmbrellaOrganizationStudents;
use tcCore\Jobs\CountUmbrellaOrganizationTeachers;
use tcCore\Jobs\CountUmbrellaOrganizationTests;
use tcCore\Jobs\CountUmbrellaOrganizationTestsTaken;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class School extends BaseModel implements AccessCheckable {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'schools';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['umbrella_organization_id', 'user_id', 'customer_code', 'name', 'main_address', 'main_postal', 'main_city', 'main_country', 'invoice_address', 'invoice_postal', 'invoice_city', 'invoice_country', 'external_main_code',
        'main_phonenumber','internetaddress'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $mainAddresses;
    protected $invoiceAddresses;
    protected $otherAddresses;
    protected $financialContacts;
    protected $technicalContacts;
    protected $implementationContacts;
    protected $otherContacts;

    public static function boot()
    {
        parent::boot();

        static::creating(function (School $school) {
            $umbrellaOrganizationId = $school->getAttribute('umbrella_organization_id');
            $customerCode = $school->getAttribute('customer_code');
            $userId = $school->getAttribute('user_id');
            if (!empty($umbrellaOrganizationId) && (empty($customerCode) || empty($userId))) {
                $umbrellaOrganization = $school->umbrellaOrganization;
                $umbrellaOrganizationCustomerCode = $umbrellaOrganization->getAttribute('customer_code');
                $umbrellaOrganizationUserId = $umbrellaOrganization->getAttribute('user_id');

                if (empty($customerCode) && !empty($umbrellaOrganizationCustomerCode)) {
                    $school->setAttribute('customer_code', $umbrellaOrganizationCustomerCode);
                }

                if (empty($userId) && !empty($umbrellaOrganizationUserId)) {
                    $school->setAttribute('user_id', $umbrellaOrganizationUserId);
                }
            }
        });

        static::saved(function(School $school) {
            if ($school->mainAddresses !== null) {
                $school->saveMainAdresses();
            }

            if ($school->invoiceAddresses !== null) {
                $school->saveInvoiceAdresses();
            }

            if ($school->otherAddresses !== null) {
                $school->saveOtherAdresses();
            }

            if ($school->financialContacts !== null) {
                $school->saveFinancialContacts();
            }

            if ($school->technicalContacts !== null) {
                $school->saveTechnicalContacts();
            }

            if ($school->implementationContacts !== null) {
                $school->saveImplementationContacts();
            }

            if ($school->otherContacts !== null) {
                $school->saveOtherContacts();
            }

            $school->dispatchJobs();
        });

        static::deleted(function(School $school)
        {
            $school->dispatchJobs(true);
        });
    }

    public function fill(array $attributes) {
        parent::fill($attributes);

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

        if(array_key_exists('school_years', $attributes)) {
            $this->sections = $attributes['school_years'];
        } elseif(array_key_exists('add_school_year', $attributes) || array_key_exists('delete_school_year', $attributes)) {
            $this->sections = $this->schoolLocationSections()->pluck('school_year_id')->all();
            if (array_key_exists('add_school_year', $attributes)) {
                array_push($this->sections, $attributes['add_school_year']);
            }

            if (array_key_exists('delete_school_year', $attributes)) {
                if(($key = array_search($attributes['delete_school_year'], $this->sections)) !== false) {
                    unset($this->sections[$key]);
                }
            }
        }

        if(array_key_exists('main_addresses', $attributes)) {
            $this->mainAddresses = $attributes['main_addresses'];
        }

        if(array_key_exists('invoice_addresses', $attributes)) {
            $this->invoiceAddresses = $attributes['invoice_addresses'];
        }

        if(array_key_exists('other_addresses', $attributes)) {
            $this->otherAddresses = $attributes['other_addresses'];
        }

        if(array_key_exists('financial_contacts', $attributes)) {
            $this->financialContacts = $attributes['financial_contacts'];
        } elseif(array_key_exists('add_financial_contact', $attributes) || array_key_exists('delete_financial_contact', $attributes)) {
            $this->financialContacts = $this->schoolContacts()->where('type', 'FINANCE')->pluck('contact_id')->all();
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
            $this->technicalContacts = $this->schoolContacts()->where('type', 'TECHNICAL')->pluck('contact_id')->all();
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
            $this->implementationContacts = $this->schoolContacts()->where('type', 'IMPLEMENTATION')->pluck('contact_id')->all();
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
            $this->otherContacts = $this->schoolContacts()->where('type', 'OTHER')->pluck('contact_id')->all();
            if (array_key_exists('add_other_contact', $attributes)) {
                array_push($this->otherContacts, $attributes['add_other_contact']);
            }

            if (array_key_exists('delete_other_contact', $attributes)) {
                if(($key = array_search($attributes['delete_other_contact'], $this->otherContacts)) !== false) {
                    unset($this->otherContacts[$key]);
                }
            }
        }

        if (array_key_exists('umbrella_organization_id', $attributes) && empty($attributes['umbrella_organization_id'])) {
            $this->setAttribute('umbrella_organization_id', null);
        }
    }

    public function schoolAddresses() {
        return $this->hasMany('tcCore\SchoolAddress', 'school_id');
    }

    private function saveMainAdresses() {
        $mainAddresses = $this->schoolAddresses()->withTrashed()->where('type', '=', 'MAIN')->get();

        $this->syncTcRelation($mainAddresses, $this->mainAddresses, 'address_id', function($school, $addressId) {
            SchoolAddress::create(['address_id' => $addressId, 'school_id' => $school->getKey(), 'type' => 'MAIN']);
        });

        $this->mainAddresses = null;
    }

    private function saveInvoiceAdresses() {
        $invoiceAddresses = $this->schoolAddresses()->withTrashed()->where('type', '=', 'INVOICE')->get();

        $this->syncTcRelation($invoiceAddresses, $this->invoiceAddresses, 'address_id', function($school, $addressId) {
            SchoolAddress::create(['address_id' => $addressId, 'school_id' => $school->getKey(), 'type' => 'INVOICE']);
        });

        $this->invoiceAddresses = null;
    }

    private function saveOtherAdresses() {
        $otherAddresses = $this->schoolAddresses()->withTrashed()->where('type', '=', 'OTHER')->get();

        $this->syncTcRelation($otherAddresses, $this->otherAddresses, 'address_id', function($school, $addressId) {
            SchoolAddress::create(['address_id' => $addressId, 'school_id' => $school->getKey(), 'type' => 'OTHER']);
        });

        $this->otherAddresses = null;
    }

    public function schoolContacts() {
        return $this->hasMany('tcCore\SchoolContact', 'school_id');
    }

    private function saveFinancialContacts() {
        $financialContacts = $this->schoolContacts()->withTrashed()->where('type', '=', 'FINANCE')->get();

        $this->syncTcRelation($financialContacts, $this->financialContacts, 'contact_id', function($school, $contactId) {
            SchoolContact::create(['contact_id' => $contactId, 'school_id' => $school->getKey(), 'type' => 'FINANCE']);
        });

        $this->financialContacts = null;
    }

    private function saveTechnicalContacts() {
        $technicalContacts = $this->schoolContacts()->withTrashed()->where('type', '=', 'TECHNICAL')->get();

        $this->syncTcRelation($technicalContacts, $this->technicalContacts, 'contact_id', function($school, $contactId) {
            SchoolContact::create(['contact_id' => $contactId, 'school_id' => $school->getKey(), 'type' => 'TECHNICAL']);
        });

        $this->technicalContacts = null;
    }

    private function saveImplementationContacts() {
        $implementationContacts = $this->schoolContacts()->withTrashed()->where('type', '=', 'IMPLEMENTATION')->get();

        $this->syncTcRelation($implementationContacts, $this->implementationContacts, 'contact_id', function($school, $contactId) {
            SchoolContact::create(['contact_id' => $contactId, 'school_id' => $school->getKey(), 'type' => 'IMPLEMENTATION']);
        });

        $this->implementationContacts = null;
    }

    private function saveOtherContacts() {
        $otherContacts = $this->schoolContacts()->withTrashed()->where('type', '=', 'OTHER')->get();

        $this->syncTcRelation($otherContacts, $this->otherContacts, 'contact_id', function($school, $contactId) {
            SchoolContact::create(['contact_id' => $contactId, 'school_id' => $school->getKey(), 'type' => 'OTHER']);
        });

        $this->otherContacts = null;
    }

    public function umbrellaOrganization() {
        return $this->belongsTo('tcCore\UmbrellaOrganization');
    }

    // Account manager
    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    // Users of this school
    public function users() {
        return $this->hasMany('tcCore\User');
    }

    public function schoolLocations() {
        return $this->hasMany('tcCore\SchoolLocation');
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        if (!in_array('Administrator', $roles) && in_array('Account manager', $roles)) {
            $userId = Auth::user()->getKey();
            $query->where(function ($query) use ($userId) {
                $query->whereIn('umbrella_organization_id', function ($query) use ($userId) {
                    $query->select('id')
                        ->from(with(new UmbrellaOrganization())->getTable())
                        ->where('user_id', $userId)
                        ->whereNull('deleted_at');
                })
                    ->orWhere('user_id', $userId)
                    ->orWhereIn('id', function ($query) use ($userId) {
                        $query->select('school_id')
                            ->from(with(new SchoolLocation())->getTable())
                            ->where('user_id', $userId)
                            ->whereNull('deleted_at');
                    });

            });
        } elseif (!in_array('Administrator', $roles)) {
            $user = Auth::user();
            $query->where(function ($query) use ($user) {
                if ($user->getAttribute('school_location_id') !== null) {
                    $query->whereIn('id', function ($query) use ($user) {
                        $query->select('school_id')
                            ->from(with(new SchoolLocation())->getTable())
                            ->where('id', $user->getAttribute('school_location_id'))
                            ->whereNull('deleted_at');
                    });
                } elseif ($user->getAttribute('school_id') !== null) {
                    $query->where('id', $user->getAttribute('school_id'));
                }

                if ($user->getAttribute('school_location_id') !== null && $user->getAttribute('school_id') !== null) {
                    $query->orWhere('id', $user->getAttribute('school_id'));
                }
            });
        }


        foreach($filters as $key => $value) {
            switch($key) {
                case 'combined_admin_grid_search':
                    $query->when($value, function ($query, $value) {
                        return $query->where(function ($query) use ($value) {
                            $query->where('customer_code', 'LIKE', "%$value%")
                                ->orWhere('name', 'like', "%$value%")
                                ->orWhereIn('umbrella_organization_id',
                                    UmbrellaOrganization::where('umbrella_organizations.name', 'LIKE', "%$value%")
                                        ->select('id')
                                )
                                ->orWhere('external_main_code', 'like', "%$value%");
                        });
                    });
                    break;
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
                case 'customer_code':
                case 'main_city':
                case 'external_main_code':
                case 'count_questions':
                    $query->orderBy($key, $value);
                    break;
                case 'umbrella_organization_name':
                    $query->orderBy(
                        UmbrellaOrganization::select('umbrella_organizations.name')
                            ->whereColumn('umbrella_organizations.id', 'schools.umbrella_organization_id')
                            ->orderBy('umbrella_organizations.name', $value)
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
            $umbrellaOrganizationIds = UmbrellaOrganization::where('user_id', $userId)->pluck('id')->all();
            $schoolIds = array_unique(SchoolLocation::where('user_id', $userId)->pluck('school_id')->all());

            return ($this->getAttribute('user_id') == $userId || in_array($this->getAttribute('umbrella_organization_id'), $umbrellaOrganizationIds) || in_array($this->getKey(), $schoolIds));
        }

        if (in_array('School manager', $roles)) {
            $user = Auth::user();

            $schoolId = null;

            if ($user->getAttribute('school_location_id') !== null) {
                $schoolId = SchoolLocation::where('id', $user->getAttribute('school_location_id'))->value('school_id');
            }

            return (($user->getAttribute('school_location_id') !== null && $this->getKey() == $schoolId) || ($user->getAttribute('school_id') !== null && $this->getKey() == $user->getAttribute('school_id')));
        }

        return false;
    }


    public function canAccessBoundResource($request, Closure $next) {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to school denied');
    }

    protected function dispatchJobs($isDeleted = false) {
        $triggerParent = false;
        if ($isDeleted === false) {
            foreach (array('umbrella_organization_id', 'count_active_teachers', 'count_active_licenses', 'count_expired_licenses', 'count_licenses', 'count_questions', 'count_students', 'count_teachers', 'count_tests', 'count_test_taken') as $variable) {
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

        $umbrellaOrganization = null;
        if ($isDeleted || $triggerAccountManager || $triggerParent) {
            $umbrellaOrganization = $this->umbrellaOrganization;
        }

        if ($isDeleted || $triggerParent) {
            if ($isDeleted || $this->getAttribute('umbrella_organization_id') !== $this->getOriginal('umbrella_organization_id')) {
                $prevSchool = UmbrellaOrganization::find($this->getOriginal('umbrella_organization_id'));

                if ($umbrellaOrganization !== null) {
                    Queue::push(new CountUmbrellaOrganizationActiveTeachers($umbrellaOrganization));
                    Queue::push(new CountUmbrellaOrganizationActiveLicenses($umbrellaOrganization));
                    Queue::push(new CountUmbrellaOrganizationExpiredLicenses($umbrellaOrganization));
                    Queue::push(new CountUmbrellaOrganizationLicenses($umbrellaOrganization));
                    Queue::push(new CountUmbrellaOrganizationQuestions($umbrellaOrganization));
                    Queue::push(new CountUmbrellaOrganizationStudents($umbrellaOrganization));
                    Queue::push(new CountUmbrellaOrganizationTeachers($umbrellaOrganization));
                    Queue::push(new CountUmbrellaOrganizationTests($umbrellaOrganization));
                    Queue::push(new CountUmbrellaOrganizationTestsTaken($umbrellaOrganization));
                }


                if ($prevSchool !== null) {
                    Queue::push(new CountUmbrellaOrganizationActiveTeachers($prevSchool));
                    Queue::push(new CountUmbrellaOrganizationActiveLicenses($prevSchool));
                    Queue::push(new CountUmbrellaOrganizationExpiredLicenses($prevSchool));
                    Queue::push(new CountUmbrellaOrganizationLicenses($prevSchool));
                    Queue::push(new CountUmbrellaOrganizationQuestions($prevSchool));
                    Queue::push(new CountUmbrellaOrganizationStudents($prevSchool));
                    Queue::push(new CountUmbrellaOrganizationTeachers($prevSchool));
                    Queue::push(new CountUmbrellaOrganizationTests($prevSchool));
                    Queue::push(new CountUmbrellaOrganizationTestsTaken($prevSchool));
                }
            } elseif($isDeleted === false && $umbrellaOrganization !== null) {
                if ($this->getAttribute('count_active_teachers') !== $this->getOriginal('count_active_teachers')) {
                    Queue::push(new CountUmbrellaOrganizationActiveTeachers($umbrellaOrganization));
                }

                if ($this->getAttribute('count_active_licenses') !== $this->getOriginal('count_active_licenses')) {
                    Queue::push(new CountUmbrellaOrganizationActiveLicenses($umbrellaOrganization));
                }

                if ($this->getAttribute('count_expire_licenses') !== $this->getOriginal('count_expire_licenses')) {
                    Queue::push(new CountUmbrellaOrganizationExpiredLicenses($umbrellaOrganization));
                }

                if ($this->getAttribute('count_licenses') !== $this->getOriginal('count_licenses')) {
                    Queue::push(new CountUmbrellaOrganizationLicenses($umbrellaOrganization));
                }

                if ($this->getAttribute('count_questions') !== $this->getOriginal('count_questions')) {
                    Queue::push(new CountUmbrellaOrganizationQuestions($umbrellaOrganization));
                }

                if ($this->getAttribute('count_students') !== $this->getOriginal('count_students')) {
                    Queue::push(new CountUmbrellaOrganizationStudents($umbrellaOrganization));
                }

                if ($this->getAttribute('count_teachers') !== $this->getOriginal('count_teachers')) {
                    Queue::push(new CountUmbrellaOrganizationTeachers($umbrellaOrganization));
                }

                if ($this->getAttribute('count_tests') !== $this->getOriginal('count_tests')) {
                    Queue::push(new CountUmbrellaOrganizationTests($umbrellaOrganization));
                }

                if ($this->getAttribute('count_test_taken') !== $this->getOriginal('count_test_taken')) {
                    Queue::push(new CountUmbrellaOrganizationTestsTaken($umbrellaOrganization));
                }
            }
        }

        if (($isDeleted || $triggerAccountManager) && $umbrellaOrganization === null) {
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

    public function canDelete(User $user)
    {
        return $user->isA('Administrator');
    }
}
