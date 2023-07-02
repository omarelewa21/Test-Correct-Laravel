<?php

namespace tcCore;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use tcCore\Exports\TestTakesExport;
use tcCore\Exports\UwlrExport;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\SomTodayHelper;
use function Livewire\str;
use betterapp\LaravelDbEncrypter\Traits\EncryptableDbAttribute;

class UwlrSoapResult extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source', 'client_code', 'client_name', 'school_year', 'brin_code', 'dependance_code', 'username_who_imported', 'log',
    ];


//    protected $with = ['entries'];

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

    public function setLogAttribute($data)
    {
        $this->attributes['log'] = json_encode($data);
    }

    public function addToLog($key, $value, $save = false)
    {
        $log = $this->log;
        if($value instanceof Carbon){
            $value = $value->format('Y-m-d H:i:s');
        }
        $log->$key = $value;

        $this->log = $log;

        if($save){
            $this->save();
        }
        return $this;
    }

    public function addQueueDataToLog($key, $save = false)
    {
        $jobs = (object) [];
        collect(DB::select(DB::raw('Select queue, count(*) as amount from jobs group by queue')))->each(function($q) use ($jobs){
            $jobs->{$q->queue} = $q->amount;
        });
        $this->addToLog($key,$jobs, $save);
        return $this;
    }

    public function getLogAttribute()
    {
        if(!array_key_exists('log',$this->attributes) || null === $this->attributes['log']){
            $this->attributes['log'] = json_encode((object) []);
        }
        return json_decode($this->attributes['log']);
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

    private function shouldSkipGroup()
    {
        return $this->source == SomTodayHelper::SOURCE;
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
            'leeStamNummer',
            'leeAchternaam',
            'leeTussenvoegsels',
            'leeVoornaam',
            'leeEmail',
            'leeEckid',
            'lesNaam',
            'vakNaam',
            'docStamNummer',
            'docAchternaam',
            'docTussenvoegsels',
            'docVoornaam',
            'docEmail',
            'docEckid',
            'IsMentor',
        ];

        $students->each(function ($leerling) use ($school, $repo) {
            if (!$this->shouldSkipGroup()) {
                $this->transformGroep($leerling, $school, $repo);
            }
            $this->transformSamenGesteldeGroep($leerling, $school, $repo);
        });

        $teachers = $repo->get('leerkracht');
        $teachers->each(function ($leerkracht) use ($school, $repo) {
            if (!$this->shouldSkipGroup()) {
                $this->transformGroepForTeacher($leerkracht, $school, $repo);
            }
            $this->transformSamengesteldeGroepForTeacher($leerkracht, $school, $repo);
        });
        if (!$this->shouldSkipGroup()) {
            $this->checkGroepenForWithLabel($repo, 'leerkracht');
            $this->checkGroepenForWithLabel($repo, 'leerling');
        }
        $this->checkSamengesteldeGroepenForWithLabel($repo, 'leerkracht');
        $this->checkSamengesteldeGroepenForWithLabel($repo, 'leerling');

//        if(!BaseHelper::notOnLocal()) { // only if on local
//            $export = new UwlrExport($this->csvArray);
//            $fileName = sprintf('uwlr-export-%s-%s.xlsx',$this->getKey(),date('Ymd'));
//            $file = storage_path($fileName);
//            if (file_exists($file)) {
//                unlink($file);
//            }
//            Excel::store($export,$fileName);
//            exit;
//        }

        unset($repo);
        unset($students);
        unset($teachers);
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
        $leerkracht,
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
            $this->getStamnummerIfAppropriate($leerling),
            // external_id gets harvested from de entree attributes on account matching; //$leerling['key'], //leeStamNummer,
            $leerling['achternaam'], //leeAchternaam,
            array_key_exists('tussenvoegsel', $leerling) ? $leerling['tussenvoegsel'] : (array_key_exists('voorvoegsel', $leerling) ? $leerling['voorvoegsel'] : ''), //leeTussenvoegsels,
            $leerling['roepnaam'],//leeVoornaam,
            array_key_exists('email', $leerling) ? $leerling['email'] : '', //email student,
            array_key_exists('eckid', $leerling) ? $leerling['eckid'] : (array_key_exists('key',
                $leerling) ? $leerling['key'] : ''),
            $klasNaam,//lesNaam,
            '', //vakNaam,
            $this->getStamnummerIfAppropriate($leerkracht),
            array_key_exists('achternaam', $leerkracht) ? $leerkracht['achternaam'] : '', //docAchternaam,
            array_key_exists('tussenvoegsel', $leerkracht) ? $leerkracht['tussenvoegsel'] : (array_key_exists('voorvoegsel', $leerkracht) ? $leerkracht['voorvoegsel'] : ''),//docTussenvoegsels,
            array_key_exists('roepnaam', $leerkracht) ? $leerkracht['roepnaam'] : '', //docVoornaam,
            array_key_exists('email', $leerkracht) ? $leerkracht['email'] : '', //docEmail,
            array_key_exists('eckid', $leerkracht) ? $leerkracht['eckid'] : (array_key_exists('key',
                $leerkracht) ? $leerkracht['key'] : ''),
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
        $leerling = (array) $leerling;
        if (array_key_exists('groep', $leerling)) {
            collect($leerling['groep'])->each(function ($groep) use ($leerling, $school, $repo) {
                if (!$groep) {
                    return;
                }
                $groepKey = $groep;

                $klas = (array) $repo->get('groep')->first(function ($groep) use ($groepKey) {
                    $groep = (array) $groep;
                    return $groepKey === $groep['key'];
                });

                if (empty($klas)) {
                    return;
                }

                $leerkracht = (array) $repo->get('leerkracht')->first(function ($teacher) use ($groepKey) {
                    $teacher = (array) $teacher;
                    return array_key_exists('groepen', $teacher) && collect($teacher['groepen'])->contains($groepKey);
                });


                if (!$leerkracht) {
                    $this->errors[] = sprintf('%s klas bevat geen leerkracht', $klas['naam']);
                } else {
                    $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 1);
                }

            });
        }
    }

    /**
     * @param $leerling
     * @param $school
     * @param $repo
     */
    private function transformSamengesteldeGroep($leerling, $school, $repo): void
    {
        $leerling = (array) $leerling;

        if (array_key_exists('samengestelde_groepen', $leerling)) {
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
    }

    private function handleSamengesteldeGroep($repo, $school, $leerling, $groepKey)
    {
        $klas = (array) $repo->get('samengestelde_groep')->first(function ($groep) use ($groepKey) {
            $groep = (array) $groep;
            return $groepKey === $groep['key'];
        });

        $leerkracht = (array) $repo->get('leerkracht')->first(function ($teacher) use ($groepKey) {
            $teacher = (array) $teacher;
//            dd(['teacher' => $teacher, 'groepKey' => $groepKey]);
            if (array_key_exists('groepen', $teacher)) {
                $groepen = (array) $teacher['groepen'];

                if (array_key_exists('samengestelde_groep', $groepen)) {
                    $teacherSamengesteldeGroepKeys = collect($groepen['samengestelde_groep'])->map(function ($item) {
                        $item = (array) $item;
                        if (array_key_exists('key', $item)) {
                            return $item['key'];
                        }
                        return array_pop($item);
                    });

                    return $teacherSamengesteldeGroepKeys->contains($groepKey);
                }
            }

            return array_key_exists('samengestelde_groepen',
                    $teacher) && collect($teacher['samengestelde_groepen'])->contains($groepKey);
        });


        if ($leerkracht) {
            $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 0);
        } else {
            $this->errors[] = sprintf('%s klas bevat geen leerkracht', $klas['naam']);
        }


    }

    /**
     * @param $leerkracht
     * @param $school
     * @param $repo
     */
    private function transformGroepForTeacher($leerkracht, $school, $repo): void
    {
        $leerkracht = (array) $leerkracht;
        if (array_key_exists('groepen', $leerkracht)) {
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
    }

    private function handleGroepForTeacher($repo, $school, $leerkracht, $groepKey)
    {
        $klas = (array) $repo->get('groep')->first(function ($groep) use ($groepKey) {
            $groep = (array) $groep;
            return $groepKey === $groep['key'];
        });


        $leerling = (array) $repo->get('leerling')->first(function ($l) use ($groepKey) {
            $l = (array) $l;
            return array_key_exists('groep', $l) && collect($l['groep'])->contains($groepKey);
        });
        if ($leerling) {
            $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 1);
        } else {
            $this->errors[] = sprintf('%s klas bevat geen leerling', $klas['naam']);
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
                    return array_key_exists('samengestelde_groepen',
                            $leerling) && collect($leerling['samengestelde_groepen'])->contains($groepKey);
                });

                if ($leerling) {
                    $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 0);
                } else {
                    $this->errors[] = sprintf('%s klas bevat geen leerling', $klas['naam']);
                }
            });
        } else {
            if (array_key_exists('groepen', $leerkracht)) {
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
    }

    private function handleSamengesteldeGroepForTeacher($repo, $school, $leerkracht, $groepKey)
    {


        $klas = (array) $repo->get('samengestelde_groep')->first(function ($groep) use ($groepKey) {
            $groep = (array) $groep;
            return $groepKey === $groep['key'];
        });

        $leerling = (array) $repo->get('leerling')->first(function ($l) use ($groepKey) {
            $l = (array) $l;
            if (array_key_exists('samengestelde_groepen', $l)) {
                $samengesteldeGroepen = (array) $l['samengestelde_groepen'];
                foreach ($samengesteldeGroepen as $samengesteldeGroep) {
                    if (is_string($samengesteldeGroep)) {
                        if ($samengesteldeGroep == $groepKey) {
                            return true;
                        }
                    } else {
                        $samengesteldeGroep = (array) $samengesteldeGroep;
                        foreach ($samengesteldeGroep as $value) {
                            $value = (array) $value;
                            $key = '';
                            if (array_key_exists('key', $value)) {
                                $key = $value['key'];
                            } else {
                                $key = array_pop($value);
                            }
                            if ($groepKey == $key) {
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        });

        if ($leerling) {
            $this->addCsvRow($school, $leerling, $klas['naam'], $leerkracht, 0);
        } else {
            $this->errors[] = $this->errors[] = sprintf('%s klas bevat geen leerling', $klas['naam']);
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
            $keyNames = collect($repo->get('groep'))->filter(function ($groep) use ($notInLabel) {
                $groep = (array) $groep;
                return $notInLabel->contains($groep['key']);
            })->map(function ($k) {
                $k = (array) $k;
                return $k['naam'];
            })->each(function ($keyName) use ($label) {
                $this->errors[] = sprintf('%s klas bevat geen %s', $keyName, $label);
            });
        }

        $notInGroups = $labelKeys->filter(function ($teacherKey) use ($keys) {
            return !$keys->contains($teacherKey);
        });

        if ($notInGroups->isNotEmpty()) {
            $notInGroups->each(function ($group) use ($label) {
                $this->errors[] = sprintf('%s klas bevat geen %s', $group, $label);
            });
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

        $labelKeys = collect($repo->get($label))->map(function ($value) use ($label) {
            $value = (array) $value;
            if (!array_key_exists('samengestelde_groepen', $value)) {
                // voor somToday staan de samengestelde_groepen onder groepen=>samengestelde_groepen.

                if ($label == 'leerkracht') {
                    $resultKeys = [];
                    if (array_key_exists('groepen', $value)) {
                        collect($value['groepen'])->each(function ($groep, $type) use (&$resultKeys) {
                            if ($type === 'samengestelde_groep') {
                                $groepKey = $groep;
                                if (is_array($groepKey) || is_object($groepKey)) {
                                    foreach ((array) $groepKey as $sGroep) {
                                        $sGroep = (array) $sGroep;
                                        $resultKeys[] = array_key_exists('key',
                                            $sGroep) ? $sGroep['key'] : array_pop($sGroep);
                                    }
                                }
                            }
                        });
                    }
                    return $resultKeys;
                }
                // Roan en martin denken dat hier nog een implementatie hoort voor leerling en dat dit voor SomToday zorgt voor
                // logging van alle klassen bij de leerling.
                return [];
            }
            if (array_key_exists('samengestelde_groepen', $value)) {
                $value['samengestelde_groepen'] = (array) $value['samengestelde_groepen'];
                if (array_key_exists('samengestelde_groep', $value['samengestelde_groepen'])) {
                    foreach ((array) $value['samengestelde_groepen']['samengestelde_groep'] as $sGroep) {
                        $sGroep = (array) $sGroep;
                        $resultKeys[] = array_key_exists('key',
                            $sGroep) ? $sGroep['key'] : array_pop($sGroep);
                    }
                    return $resultKeys;
                }
                return $value['samengestelde_groepen'];
            }
        })->flatten();

        $notInLabel = $keys->filter(function ($key) use ($labelKeys) {
            return !$labelKeys->contains($key);
        });

        if ($notInLabel->isNotEmpty()) {
            $keyNames = collect($repo->get('samengestelde_groep'))->filter(function ($groep) use ($notInLabel) {
                $groep = (array) $groep;
                return $notInLabel->contains($groep['key']);
            })->map(function ($k) {
                $k = (array) $k;
                return $k['naam'];
            })->each(function ($keyName) use ($label) {
                $this->errors[] = sprintf('%s klas bevat geen %s', $keyName, $label);
            });
        }

        $notInGroups = $labelKeys->filter(function ($teacherKey) use ($keys) {
            return !$keys->contains($teacherKey);
        });

        if ($notInGroups->isNotEmpty()) {
            $notInGroups->each(function ($group) use ($label) {
                $this->errors[] = sprintf('%s klas bevat geen %s', $group, $label);
            });
        };
    }

    public function __destruct()
    {
        if ($this->errors) {
            $this->error_messages .= collect($this->errors)
                ->unique()
                ->map(function ($error) {
                    return sprintf('%s: %s<BR>', now(), $error);
                })->sort()->join('');
            $this->save();

        }
        // parent::__destruct();
    }

    public static function schoolLocationHasRunImport(SchoolLocation $schoolLocation): bool
    {
        return UwlrSoapResult::where('brin_code', $schoolLocation->external_main_code)->where('dependance_code',
                $schoolLocation->external_sub_code)->count() > 0;
    }

    private function getStamnummerIfAppropriate($arr)
    {
        if (!is_array($arr)) {
            return '';
        }

        if (!array_key_exists('key', $arr)) {
            return '';
        }

        // remove medewerker from leftside if appropriate;
        $stamnummer = Str::of($arr['key'])->lower()->ltrim('medewerker')->__toString();

        if (strlen($stamnummer) > 30) {
            return '';
        }

        return $stamnummer;
    }

    public function updateProgress($activeLine, $totalLines)
    {
        $this->update([
            'import_progress' => sprintf('%d %%', round(($activeLine / $totalLines) * 100))
        ]);
    }

}
