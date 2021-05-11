<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class UwlrSoapResult extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source', 'client_code', 'client_name', 'school_year', 'brin_code', 'dependance_code',
    ];

    protected $csvArray = [];

    public function entries()
    {
        return $this->hasMany(UwlrSoapEntry::class);
    }

    public function report()
    {
        return $this->entries->groupBy('key')->map(function ($group) {
            return $group->count();
        });
    }

    public function asData()
    {
        return $this->entries->groupBy('key')->map(function ($group) {

            return $group->map(function ($item) {
                return unserialize($item->object);
            });
        });
    }

    public function toCVS()
    {
        $repo = $this->asData();

        $students = $repo->get('leerling');

        $school = $repo->get('school')->get(0);

        $schoolRecord = SchoolLocation::where('external_main_code', $school['brincode'])->where('external_sub_code', $school['dependancecode'])->first();

        if ($schoolRecord) {
            $school['name'] = $schoolRecord->name;
        } else {
            /** @todo foutmelding school niet gevonden met gegevens hoe aan te maken (brin en brinneven) */
        }


        $this->csvArray[] = [
            'Schoolnaam',
            'Brincode',
            'Locatiecode',
            'Studierichting',
            'lesJaarlaag',
            'Schooljaar',
            'leeStamNummer',
            'leeAchternaam',
            'leeTussenvoegsels',
            'leeVoornaam',
            'lesNaam',
            'vakNaam',
            'docStamNummer',
            'docAchternaam',
            'docTussenvoegsels',
            'docVoornaam',
            'IsMentor',
        ];

        $students->each(function ($leerling) use ($school, $repo) {
            $this->transformGroep($leerling, $school, $repo);
            $this->transformSamenGesteldeGroep($leerling, $school, $repo);
        });
        return $this->csvArray;
    }

    /**
     * @param $school
     * @param $leerling
     * @param $klasNaam
     * @param $leerkracht
     */
    private function addCvsRow($school, $leerling, $klasNaam, $leerkracht, $isMentorGroep = 1, $studierichting='vwo'): void
    {
        /** @todo jaargroep uit klas halen als die niet in de leerling zit. */

        $this->csvArray[] = [
            $school['name'], //Schoolnaam,
            $school['brincode'], //Brincode,
            $school['dependancecode'], //Locatiecode,
            $studierichting, //Studierichting,
            $leerling['jaargroep']? $leerling['jaargroep']-10: $klas['jaargroep'], //lesJaarlaag wordt gedefineerd als 11-16 is 1-6 vo zie code table uwlr,


            $school['schooljaar'], // Schooljaar,
            $leerling['key'], //leeStamNummer,
            $leerling['achternaam'], //leeAchternaam,
            array_key_exists('tussenvoegsel', $leerling) ? $leerling['tussenvoegsel'] : '', //leeTussenvoegsels,
            $leerling['roepnaam'],//leeVoornaam,
            $klasNaam,//lesNaam,
            '', //vakNaam,
            $leerkracht['key'],//docStamNummer,
            array_key_exists('achternaam', $leerkracht) ? $leerkracht['achternaam'] : '', //docAchternaam,
            array_key_exists('tussenvoegsel', $leerkracht) ? $leerkracht['tussenvoegsel'] : '',//docTussenvoegsels,
            array_key_exists('roepnaam', $leerkracht) ? $leerkracht['roepnaam'] : '', //docVoornaam,
            $isMentorGroep,//IsMentor
        ];
    }

    /**
     * @param $leerling
     * @param $school
     * @param $repo
     */
    private function transformGroep($leerling, $school, $repo): void
    {
        collect($leerling['groep'])->each(function ($groep) use ($leerling, $school, $repo) {
            $groepKey = $groep;

            $klas = $repo->get('groep')->first(function ($groep) use ($groepKey) {
                return $groepKey === $groep['key'];
            });

            $leerkracht = $repo->get('leerkracht')->first(function ($teacher) use ($groepKey) {
                return collect($teacher['groepen'])->contains($groepKey);
            });

            $this->addCvsRow($school, $leerling, $klas['naam'], $leerkracht, 1);
        });
    }

    /**
     * @param $leerling
     * @param $school
     * @param $repo
     */
    private function transformSamengesteldeGroep($leerling, $school, $repo): void
    {
        collect($leerling['samengestelde_groepen'])->each(function ($groep) use ($leerling, $school, $repo) {
            $groepKey = $groep;

            $klas = $repo->get('samengestelde_groep')->first(function ($groep) use ($groepKey) {
                return $groepKey === $groep['key'];
            });

            $leerkracht = $repo->get('leerkracht')->first(function ($teacher) use ($groepKey) {
                return collect($teacher['samengestelde_groepen'])->contains($groepKey);
            });


            $this->addCvsRow($school, $leerling, $klas['naam'], $leerkracht, 0);
        });
    }



}
