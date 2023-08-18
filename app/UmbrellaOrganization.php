<?php namespace tcCore;

use Closure;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Jobs\CountAccountManagerAccounts;
use tcCore\Jobs\CountAccountManagerActiveLicenses;
use tcCore\Jobs\CountAccountManagerExpiredLicenses;
use tcCore\Jobs\CountAccountManagerLicenses;
use tcCore\Jobs\CountAccountManagerStudents;
use tcCore\Jobs\CountAccountManagerTeachers;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class UmbrellaOrganization extends BaseModel implements AccessCheckable {

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
    protected $table = 'umbrella_organizations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'customer_code', 'name', 'main_address', 'main_postal', 'main_city', 'main_country', 'invoice_address', 'invoice_postal', 'invoice_city', 'invoice_country','external_main_code'];

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


        static::saved(function(UmbrellaOrganization $umbrellaOrganization) {
            if ($umbrellaOrganization->mainAddresses !== null) {
                $umbrellaOrganization->saveMainAdresses();
            }

            if ($umbrellaOrganization->invoiceAddresses !== null) {
                $umbrellaOrganization->saveInvoiceAdresses();
            }

            if ($umbrellaOrganization->otherAddresses !== null) {
                $umbrellaOrganization->saveOtherAdresses();
            }

            if ($umbrellaOrganization->financialContacts !== null) {
                $umbrellaOrganization->saveFinancialContacts();
            }

            if ($umbrellaOrganization->technicalContacts !== null) {
                $umbrellaOrganization->saveTechnicalContacts();
            }

            if ($umbrellaOrganization->implementationContacts !== null) {
                $umbrellaOrganization->saveImplementationContacts();
            }

            if ($umbrellaOrganization->otherContacts !== null) {
                $umbrellaOrganization->saveOtherContacts();
            }

            $umbrellaOrganization->dispatchJobs();
        });

        static::deleted(function(UmbrellaOrganization $umbrellaOrganization)
        {
            $umbrellaOrganization->dispatchJobs(true);
        });
    }

    public function fill(array $attributes) {
        parent::fill($attributes);

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
            $this->financialContacts = $this->umbrellaOrganizationContacts()->where('type', 'FINANCE')->pluck('contact_id')->all();
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
            $this->technicalContacts = $this->umbrellaOrganizationContacts()->where('type', 'TECHNICAL')->pluck('contact_id')->all();
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
            $this->implementationContacts = $this->umbrellaOrganizationContacts()->where('type', 'IMPLEMENTATION')->pluck('contact_id')->all();
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
            $this->otherContacts = $this->umbrellaOrganizationContacts()->where('type', 'OTHER')->pluck('contact_id')->all();
            if (array_key_exists('add_other_contact', $attributes)) {
                array_push($this->otherContacts, $attributes['add_other_contact']);
            }

            if (array_key_exists('delete_other_contact', $attributes)) {
                if(($key = array_search($attributes['delete_other_contact'], $this->otherContacts)) !== false) {
                    unset($this->otherContacts[$key]);
                }
            }
        }
    }



    public function umbrellaOrganizationAddresses() {
        return $this->hasMany('tcCore\UmbrellaOrganizationAddress', 'umbrella_organization_id');
    }

    private function saveMainAdresses() {
        $mainAddresses = $this->umbrellaOrganizationAddresses()->withTrashed()->where('type', '=', 'MAIN')->get();

        $this->syncTcRelation($mainAddresses, $this->mainAddresses, 'address_id', function($umbrellaOrganization, $addressId) {
            UmbrellaOrganizationAddress::create(['address_id' => $addressId, 'umbrella_organization_id' => $umbrellaOrganization->getKey(), 'type' => 'MAIN']);
        });

        $this->mainAddresses = null;
    }

    private function saveInvoiceAdresses() {
        $invoiceAddresses = $this->umbrellaOrganizationAddresses()->withTrashed()->where('type', '=', 'INVOICE')->get();

        $this->syncTcRelation($invoiceAddresses, $this->invoiceAddresses, 'address_id', function($umbrellaOrganization, $addressId) {
            UmbrellaOrganizationAddress::create(['address_id' => $addressId, 'umbrella_organization_id' => $umbrellaOrganization->getKey(), 'type' => 'INVOICE']);
        });

        $this->invoiceAddresses = null;
    }

    private function saveOtherAdresses() {
        $otherAddresses = $this->umbrellaOrganizationAddresses()->withTrashed()->where('type', '=', 'OTHER')->get();

        $this->syncTcRelation($otherAddresses, $this->otherAddresses, 'address_id', function($umbrellaOrganization, $addressId) {
            UmbrellaOrganizationAddress::create(['address_id' => $addressId, 'umbrella_organization_id' => $umbrellaOrganization->getKey(), 'type' => 'OTHER']);
        });

        $this->otherAddresses = null;
    }

    public function umbrellaOrganizationContacts() {
        return $this->hasMany('tcCore\UmbrellaOrganizationContact', 'umbrella_organization_id');
    }

    private function saveFinancialContacts() {
        $financialContacts = $this->umbrellaOrganizationContacts()->withTrashed()->where('type', '=', 'FINANCE')->get();

        $this->syncTcRelation($financialContacts, $this->financialContacts, 'contact_id', function($umbrellaOrganization, $contactId) {
            UmbrellaOrganizationContact::create(['contact_id' => $contactId, 'umbrella_organization_id' => $umbrellaOrganization->getKey(), 'type' => 'FINANCE']);
        });

        $this->financialContacts = null;
    }

    private function saveTechnicalContacts() {
        $technicalContacts = $this->umbrellaOrganizationContacts()->withTrashed()->where('type', '=', 'TECHNICAL')->get();

        $this->syncTcRelation($technicalContacts, $this->technicalContacts, 'contact_id', function($umbrellaOrganization, $contactId) {
            UmbrellaOrganizationContact::create(['contact_id' => $contactId, 'umbrella_organization_id' => $umbrellaOrganization->getKey(), 'type' => 'TECHNICAL']);
        });

        $this->technicalContacts = null;
    }

    private function saveImplementationContacts() {
        $implementationContacts = $this->umbrellaOrganizationContacts()->withTrashed()->where('type', '=', 'IMPLEMENTATION')->get();

        $this->syncTcRelation($implementationContacts, $this->implementationContacts, 'contact_id', function($umbrellaOrganization, $contactId) {
            UmbrellaOrganizationContact::create(['contact_id' => $contactId, 'umbrella_organization_id' => $umbrellaOrganization->getKey(), 'type' => 'IMPLEMENTATION']);
        });

        $this->implementationContacts = null;
    }

    private function saveOtherContacts() {
        $otherContacts = $this->umbrellaOrganizationContacts()->withTrashed()->where('type', '=', 'OTHER')->get();

        $this->syncTcRelation($otherContacts, $this->otherContacts, 'contact_id', function($umbrellaOrganization, $contactId) {
            UmbrellaOrganizationContact::create(['contact_id' => $contactId, 'umbrella_organization_id' => $umbrellaOrganization->getKey(), 'type' => 'OTHER']);
        });

        $this->otherContacts = null;
    }

    public function schools() {
        return $this->hasMany('tcCore\School');
    }

    // Account manager
    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        $user = Auth::user();

        if (!in_array('Administrator', $roles) && in_array('Account manager', $roles)) {
            $userId = $user->getKey();

            $schoolIds = SchoolLocation::where('user_id', $userId)->pluck('school_id')->all();
            $umbrellaOrganizationIds = School::where('user_id', $userId);

            if ($schoolIds) {
                $umbrellaOrganizationIds->orWhereIn('id', $schoolIds);
            }

            $umbrellaOrganizationIds = $umbrellaOrganizationIds->pluck('umbrella_organization_id')->all();

            if ($umbrellaOrganizationIds) {
                $query->where(function ($query) use ($userId, $umbrellaOrganizationIds) {
                    $query->where('user_id', $userId);
                    $query->orWhereIn('id', $umbrellaOrganizationIds);
                });
            } else {
                $query->where('user_id', $userId);
            }
        } elseif(!in_array('Administrator', $roles)) {

            $schoolIds = null;

            if ($user->getAttribute('school_location_id') !== null) {
                $schoolIds = SchoolLocation::where('id', $user->getAttribute('school_location_id'))->values('school_id');
            }

            if ($user->getAttribute('school_id') !== null) {
                if ($schoolIds === null) {
                    $schoolIds = $user->getAttribute('school_id');
                } else {
                    $schoolIds = [$schoolIds, $user->getAttribute('school_id')];
                }
            }

            if (is_array($schoolIds)) {
                $umbrellaOrganizationIds = School::whereIn('id', $schoolIds)->pluck('umbrella_organization_id')->all();
            } else {
                $umbrellaOrganizationIds = School::where('id', $schoolIds)->pluck('umbrella_organization_id')->all();
            }

            $query->whereIn('id', $umbrellaOrganizationIds);
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

            $schoolIds = SchoolLocation::where('user_id', $userId)->pluck('school_id')->all();
            $umbrellaOrganizationIds = School::where('user_id', $userId);

            if ($schoolIds) {
                $umbrellaOrganizationIds->orWhereIn('id', $schoolIds);
            }

            $umbrellaOrganizationIds = $umbrellaOrganizationIds->pluck('umbrella_organization_id')->all();

            return ($this->getAttribute('user_id') == $userId || in_array($this->getKey(), $umbrellaOrganizationIds));
        }

        if (in_array('School manager', $roles)) {
            $user = Auth::user();

            $schoolIds = null;

            if ($user->getAttribute('school_location_id') !== null) {
                $schoolIds = SchoolLocation::where('id', $user->getAttribute('school_location_id'))->values('school_id');
            }

            if ($user->getAttribute('school_id') !== null) {
                if ($schoolIds === null) {
                    $schoolIds = $user->getAttribute('school_id');
                } else {
                    $schoolIds = [$schoolIds, $user->getAttribute('school_id')];
                }
            }

            if (is_array($schoolIds)) {
                $umbrellaOrganizationIds = UmbrellaOrganization::whereIn('id', $schoolIds)->pluck('umbrella_organization_id')->all();
            } else {
                $umbrellaOrganizationIds = UmbrellaOrganization::where('id', $schoolIds)->pluck('umbrella_organization_id')->all();
            }

            return (in_array($this->getKey(), $umbrellaOrganizationIds));
        }
        return false;
    }

    public function canAccessBoundResource($request, Closure $next) {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to umbrella organization denied');
    }

    protected function dispatchJobs($isDeleted = false) {
        $triggerAccountManager = false;
        if ($isDeleted === false) {
            foreach (array('user_id', 'count_active_licenses', 'count_expired_licenses', 'count_licenses', 'count_students', 'count_teachers') as $variable) {
                if ($this->getAttribute($variable) !== $this->getOriginal($variable)) {
                    $triggerAccountManager = true;
                    break;
                }
            }
        }

        if ($isDeleted || $triggerAccountManager) {
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
