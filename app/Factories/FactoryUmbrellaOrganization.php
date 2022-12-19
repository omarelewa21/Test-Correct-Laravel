<?php

namespace tcCore\Factories;

use tcCore\Factories\Traits\RandomCharactersGeneratable;
use tcCore\School;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class FactoryUmbrellaOrganization
{
    use RandomCharactersGeneratable;

    public $umbrellaOrganization;
    public $accountManager;
    protected $umbrellaName = '';

    public static function create(string $umbrellaName = null, User $accountManager = null, array $umbrellaProperties = []): FactoryUmbrellaOrganization
    {
        $factory = new static;

        if ($umbrellaName === null) {
            $factory->umbrellaName = 'U-' . $factory->randomCharacters(5);
        } else {
            $factory->umbrellaName = $umbrellaName;
        }

        if ($accountManager === null) {
            $factory->accountManager = FactoryUser::createAccountManager($factory->umbrellaName)->user;
        } else {
            $factory->accountManager = $accountManager;
        }

        $umbrellaProperties = array_merge($factory->definition(), $umbrellaProperties, [
            'user_id' => $factory->accountManager->getKey(),
        ]);

        $factory->umbrellaOrganization = UmbrellaOrganization::create($umbrellaProperties);

        return $factory;
    }

    protected function definition(): array
    {
        return [
            'customer_code'   => strtoupper($this->umbrellaName),
            'name'            => $this->umbrellaName,
            'main_address'    => 'Agrobusinespark 10',
            'main_postal'     => '6708PV',
            'main_city'       => 'Wageningen',
            'main_country'    => 'Netherlands',
            'invoice_address' => 'Agrobusinespark 10',
            'invoice_postal'  => '6708PV',
            'invoice_city'    => 'Wageningen',
            'invoice_country' => 'Netherlands',
        ];
    }


}
