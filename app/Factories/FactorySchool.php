<?php

namespace tcCore\Factories;

use tcCore\Factories\Traits\RandomCharactersGeneratable;
use tcCore\School;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class FactorySchool
{
    use RandomCharactersGeneratable;

    public $school;
    public $accountManager;
    public $umbrellaOrganization;
    protected $schoolName = '';

    public static function create(string $schoolName = null, User $accountManager = null, array $schoolProperties = [], UmbrellaOrganization $umbrellaOrganization = null): FactorySchool
    {
        $factory = new static;
        $factory->umbrellaOrganization = $umbrellaOrganization;

        if ($schoolName === null) {
            $factory->schoolName = 'S-' . $factory->randomCharacters(5);
        } else {
            $factory->schoolName = $schoolName;
        }

        if ($accountManager === null) {
            $factory->accountManager = FactoryUser::createAccountManager($factory->schoolName)->user;
        } else {
            $factory->accountManager = $accountManager;
        }

        $schoolProperties = array_merge($factory->definition(), $schoolProperties, [
            'user_id' => $factory->accountManager->getKey(),
        ]);

        $factory->school = School::create($schoolProperties);

        return $factory;
    }

    protected function definition(): array
    {
        return [
            'customer_code'            => strtoupper($this->schoolName),
            'name'                     => $this->schoolName,
            'main_address'             => 'Agrobusinespark 10',
            'main_postal'              => '6708PV',
            'main_city'                => 'Wageningen',
            'main_country'             => 'Netherlands',
            'invoice_address'          => 'factory adress',
            'umbrella_organization_id' => $this->umbrellaOrganization?->getKey() ?? null,
        ];
    }


}
