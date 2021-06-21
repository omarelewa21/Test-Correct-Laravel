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

    private $errors = [];

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

    public function getSchoolNameAttribute()
    {
        $location = SchoolLocation::firstWhere([
            ['external_main_code', $this->brin_code], ['external_sub_code', $this->dependance_code]
        ]);
        return optional($location)->name;
    }

    public function asData()
    {
        return $this->entries->groupBy('key')->map(function ($group) {
            return $group->map(function ($item) {
                return unserialize($item->object);
            });
        });
    }

    public function toCSV()
    {
        $repo = $this->asData();

        $students = $repo->get('leerling');

        $school = (array) $repo->get('school')->get(0);

        $schoolRecord = SchoolLocation::where('external_main_code', $school['brincode'])->where('external_sub_code',
            $school['dependancecode'])->first();

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
//            'leeStamNummer',
            'leeAchternaam',
            'leeTussenvoegsels',
            'leeVoornaam',
            'leeEmail',
            'leeEckid',
            'lesNaam',
            'vakNaam',
//            'docStamNummer',
            'docAchternaam',
            'docTussenvoegsels',
            'docVoornaam',
            'docEmail',
            'docEckid',
            'IsMentor',
            'isNullTeacher',
        ];

        $students->each(function ($leerling) use ($school, $repo) {
            $this->transformGroep($leerling, $school, $repo);
            $this->transformSamenGesteldeGroep($leerling, $school, $repo);
        });

        $teachers = $repo->get('leerkracht');
        $teachers->each(function ($leerkracht) use ($school, $repo) {
            $this->transformGroepForTeacher($leerkracht, $school, $repo);
            $this->transformSamengesteldeGroepForTeacher($leerkracht, $school, $repo);
        });

        $this->checkGroepenForWithLabel($repo, 'leerkracht');
        $this->checkGroepenForWithLabel($repo, 'leerling');
        $this->checkSamengesteldeGroepenForWithLabel($repo, 'leerkracht');
        $this->checkSamengesteldeGroepenForWithLabel($repo, 'leerling');

        return $this->csvArray;
    }


    private function normalizeJaarGroep($jaargroep)
    {
        if (!$jaargroep) {
            return 0;
        }

        if ($jaargroep > 0 && $jaargroep < 7) {
            return $jaargroep;
        }

        if ($jaargroep > 10 && $jaargroep < 17) {
            //lesJaarlaag wordt gedefineerd als 11-16 is 1-6 vo zie code table uwlr,
            return $jaargroep - 10;
        }

        $this->errors[] = sprintf('unkown jaargroep %s', $jaargroep);
        return 0;

    }

    /**
     * @param $school
     * @param $leerling
     * @param $klasNaam
     * @param $leerkracht
     */
    private function addCsvRow(
        $school,
        $leerling,
        $klasNaam,
        $leerkracht = null,// a null record for teacher is a (temporary) measure because uwlr of somToday does not contain groep info.
        $isMentorGroep = 1,
        $studierichting = 'uwlr_education_level'
    ): void {
        $jaargroep = $this->normalizeJaarGroep($leerling['jaargroep']);
        /** @todo jaargroep uit klas halen als die niet in de leerling zit. */


        $this->csvArray[] = [
            $school['name'], //Schoolnaam,
            $school['brincode'], //Brincode,
            $school['dependancecode'], //Locatiecode,
            $studierichting, //Studierichting,
            $jaargroep, // $klas['jaargroep'], //lesJaarlaag wordt gedefineerd als 11-16 is 1-6 vo zie code table uwlr,

            $school['schooljaar'], // Schooljaar,
            // $leerling['key'], // external_id gets harvested from de entree attributes on account matching; //$leerling['key'], //leeStamNummer,
            $leerling['achternaam'], //leeAchternaam,
            array_key_exists('tussenvoegsel', $leerling) ? $leerling['tussenvoegsel'] : '', //leeTussenvoegsels,
            $leerling['roepnaam'],//leeVoornaam,
            array_key_exists('email', $leerling) ? $leerling['email'] : '', //email student,
            array_key_exists('eckid', $leerling) ? $leerling['eckid'] : '',
            $klasNaam,//lesNaam,
            '', //vakNaam,
//            $leerkracht['key'],//docStamNummer,
            is_null($leerkracht)? '' : array_key_exists('achternaam', $leerkracht) ? $leerkracht['achternaam'] : '', //docAchternaam,
            is_null($leerkracht)? '' :array_key_exists('tussenvoegsel', $leerkracht) ? $leerkracht['tussenvoegsel'] : '',//docTussenvoegsels,
            is_null($leerkracht)? '' :array_key_exists('roepnaam', $leerkracht) ? $leerkracht['roepnaam'] : '', //docVoornaam,
            is_null($leerkracht)? '' :array_key_exists('email', $leerkracht) ? $leerkracht['email'] : '', //docEmail,
            is_null($leerkracht)? '' :array_key_exists('eckid', $leerkracht) ? $leerkracht['eckid'] : '',
            $isMentorGroep,//IsMentor
            is_null($leerkracht)? 1: 0, // isNullTeacher
        ];
    }

    /**
     * @param $leerling
     * @param $school
     * @param $repo
     */
    private function transformGroep($leerling, $school, $repo): void
    {
        $leerling = (array) $leerling;
        collect($leerling['groep'])->each(function ($groep) use ($leerling, $school, $repo) {

            $groepKey = $groep;

            $klas = (array) $repo->get('groep')->first(function ($groep) use ($groepKey) {
                $groep = (array) $groep;
                return $groepKey === $groep['key'];
            });


            $leerkracht = (array) $repo->get('leerkracht')->first(function ($teacher) use ($groepKey) {
                $teacher = (array) $teacher;
                return collect($teacher['groepen'])->contains($groepKey);
            });

            if (!$leerkracht) {
                $leerkracht = null;
                $this->errors[] = sprintf('kan geen leerkracht vinden voor klas %s', $klas['naam']);
            }

            $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 1);

        });
    }

    /**
     * @param $leerling
     * @param $school
     * @param $repo
     */
    private function transformSamengesteldeGroep($leerling, $school, $repo): void
    {
        $leerling = (array) $leerling;

        collect($leerling['samengestelde_groepen'])->each(function ($groep) use ($leerling, $school, $repo) {
            $groepKey = $groep;
            if (is_array($groepKey) || is_object($groepKey)) {

                foreach ((array) $groepKey as $sGroep) {
                    $sGroep = (array) $sGroep;
                    $key = array_key_exists('key', $sGroep) ? $sGroep['key'] : array_pop($sGroep);
                    $this->handleSamengesteldeGroep($repo, $school, $leerling, $key);
                }
            } else {
                $this->handleSamengesteldeGroep($repo, $school, $leerling, $groepKey);
            }


        });
    }

    private function handleSamengesteldeGroep($repo, $school, $leerling, $groepKey)
    {
        $klas = (array) $repo->get('samengestelde_groep')->first(function ($groep) use ($groepKey) {
            $groep = (array) $groep;
            return $groepKey === $groep['key'];
        });

        $leerkracht = (array) $repo->get('leerkracht')->first(function ($teacher) use ($groepKey) {
            $teacher = (array) $teacher;
            if (array_key_exists('samengestelde_groepen', $teacher)) {
                return collect($teacher['samengestelde_groepen'])->contains($groepKey);
            }

            return collect($teacher['groepen'])->contains($groepKey);
        });


        $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 0);


    }

    /**
     * @param $leerkracht
     * @param $school
     * @param $repo
     */
    private function transformGroepForTeacher($leerkracht, $school, $repo): void
    {
        $leerkracht = (array) $leerkracht;
        collect($leerkracht['groepen'])->each(function ($groep, $type) use ($leerkracht, $school, $repo) {
            // someToDay has samengestelde_groep inside groepen;
            if ($type !== 'samengestelde_groep') {
                $groepKey = $groep;
                if (is_array($groepKey) || is_object($groepKey)) {
                    foreach ((array) $groepKey as $sGroep) {
                        $sGroep = (array) $sGroep;
                        $key = array_key_exists('key', $sGroep) ? $sGroep['key'] : array_pop($sGroep);
                        $this->handleGroepForTeacher($repo, $school, $leerkracht, $key);
                    }
                } else {
                    $this->handleGroepForTeacher($repo, $school, $leerkracht, $groepKey);
                }
            }
        });
    }

    private function handleGroepForTeacher($repo, $school, $leerkracht, $groepKey)
    {
        $klas = (array) $repo->get('groep')->first(function ($groep) use ($groepKey) {
            $groep = (array) $groep;
            return $groepKey === $groep['key'];
        });


        $leerling = (array) $repo->get('leerling')->first(function ($l) use ($groepKey) {
            $l = (array) $l;
            return collect($l['groep'])->contains($groepKey);
        });
        if ($leerling) {
            $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 1);
        } else {
            $this->errors[] = sprintf('kan geen leerling niet vinden voor klas %s', $klas['naam']);
        }
    }


    /**
     * @param $samengestelde_groepen
     * @param $school
     * @param $repo
     */
    private function transformSamengesteldeGroepForTeacher($leerkracht, $school, $repo): void
    {
        $leerkracht = (array) $leerkracht;

        //scenario magister;
        if (array_key_exists('samengestelde_groepen', $leerkracht)) {
            collect($leerkracht['samengestelde_groepen'])->each(function ($groep) use (
                $leerkracht,
                $school,
                $repo
            ) {

                $groepKey = $groep;

                $klas = $repo->get('samengestelde_groep')->first(function ($groep) use ($groepKey) {
                    return $groepKey === $groep['key'];
                });

                $leerling = $repo->get('leerling')->first(function ($leerling) use ($groepKey) {
                    return collect($leerling['samengestelde_groepen'])->contains($groepKey);
                });

                if ($leerling) {
                    $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 0);
                } else {
                    $this->errors[] = $this->errors[] = sprintf('kan geen leerling vinden voor klas %s', $klas['naam']);
                }
            });
        } else {
            collect($leerkracht['groepen'])->each(function ($groep, $type) use (
                $leerkracht,
                $school,
                $repo
            ) {
                if ($type === 'samengestelde_groep') {
                    $groepKey = $groep;
                    if (is_array($groepKey) || is_object($groepKey)) {
                        foreach ((array) $groepKey as $sGroep) {
                            $sGroep = (array) $sGroep;
                            $key = array_key_exists('key', $sGroep) ? $sGroep['key'] : array_pop($sGroep);

                            $this->handleSamengesteldeGroepForTeacher($repo, $school, $leerkracht, $key);
                        }
                    }
                }
            });
        }
    }

    private function handleSamengesteldeGroepForTeacher($repo, $school, $leerkracht, $groepKey)
    {
         $klas = (array) $repo->get('samengestelde_groep')->first(function ($groep) use ($groepKey) {
             $groep = (array) $groep;
            return $groepKey === $groep['key'];
        });

        $leerling = (array) $repo->get('leerling')->first(function ($l) use ($groepKey) {
            $l = (array) $l;
            return collect($l['samengestelde_groepen'])->contains($groepKey);
        });

        if ($leerling) {
            $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 0);
        } else {
            $this->errors[] = $this->errors[] = sprintf('kan geen leerling vinden voor klas %s', $klas['naam']);
        }
    }

    /**
     * @param $repo
     * @param  string  $label
     */
    private function checkGroepenForWithLabel($repo, string $label): void
    {
        $keys = collect($repo->get('groep'))->map(function ($groep) {
            $groep = (array) $groep;
            return $groep['key'];
        });

        $labelKeys = collect($repo->get($label))->map(function ($value) {
            $value = (array) $value;
            if (array_key_exists('groepen', $value)) {
                return $value['groepen'];
            };
            return $value['groep'];
        })->flatten();

        $notInLabel = $keys->filter(function ($key) use ($labelKeys) {
            return !$labelKeys->contains($key);
        });

        if ($notInLabel->isNotEmpty()) {
            $this->errors[] = sprintf('no %sen found for group(s) keys (letop deze is dubbel) [%s]', $label, $notInLabel->join(', '));
        }

        $notInGroups = $labelKeys->filter(function ($teacherKey) use ($keys) {
            return !$keys->contains($teacherKey);
        });

        if ($notInGroups->isNotEmpty()) {
            $this->errors[] = sprintf('found groep(s) in %s but not in groep %s', $label, $notInGroups->join(', '));
        };
    }


    /**
     * @param $repo
     * @param  string  $label
     */
    private function checkSamengesteldeGroepenForWithLabel($repo, string $label): void
    {
        $keys = collect($repo->get('samengestelde_groep'))->map(function ($groep) {
            $groep = (array) $groep;
            return $groep['key'];
        });

        $labelKeys =  collect($repo->get($label))->map(function ($value) use($label) {
            $value = (array) $value;
            if (! array_key_exists('samengestelde_groepen', $value)) {
                // voor somToday staan de samengestelde_groepen onder groepen=>samengestelde_groepen.

                if ($label == 'leerkracht') {
                    $resultKeys = [];
                    collect($value['groepen'])->each(function ($groep, $type) use (&$resultKeys) {
                        if ($type === 'samengestelde_groep') {
                            $groepKey = $groep;
                            if (is_array($groepKey) || is_object($groepKey)) {
                                foreach ((array) $groepKey as $sGroep) {
                                    $sGroep = (array) $sGroep;
                                    $resultKeys[] = array_key_exists('key', $sGroep) ? $sGroep['key'] : array_pop($sGroep);
                                }
                            }
                        }
                    });
                    return $resultKeys;
                }


            }
            return $value['samengestelde_groepen'];
        })->flatten();

        $notInLabel = $keys->filter(function ($key) use ($labelKeys) {
            return !$labelKeys->contains($key);
        });

        if ($notInLabel->isNotEmpty()) {
            $this->errors[] = sprintf('no %sen found for samengestelde_group(s) [%s]', $label, $notInLabel->join(', '));
        }

        $notInGroups = $labelKeys->filter(function ($teacherKey) use ($keys) {
            return !$keys->contains($teacherKey);
        });

        if ($notInGroups->isNotEmpty()) {
            $this->errors[] = sprintf('found groep(s) in %s but not in groep %s', $label, $notInGroups->join(' '));
        };
    }

    public function __destruct()
    {
        if ($this->errors) {
            $this->error_messages .= collect($this->errors)
                ->map(function ($error) {
                    return sprintf('%s: %s<BR>', now(), $error);
                })->join(',');
            $this->save();

        }
        // parent::__destruct();
    }

    public static function schoolLocationHasRunImport(SchoolLocation $schoolLocation): bool
    {
        return UwlrSoapResult::where('brin_code', $schoolLocation->external_main_code)->where('dependance_code', $schoolLocation->external_sub_code)->count() > 0;
    }

}
