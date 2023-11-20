<?php

namespace tcCore\Services;

use tcCore\Services\ContentSource\ContentSourceService;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\NationalItemBankService;
use tcCore\Services\ContentSource\OlympiadeArchiveService;
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\Services\ContentSource\PersonalService;
use tcCore\Services\ContentSource\SchoolLocationService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\Services\ContentSource\UmbrellaOrganizationService;
use tcCore\Test;
use tcCore\User;

class ContentSourceFactory
{
    public static function makeWithTestBasedOnScope(Test $test): ContentSourceService|null
    {
        $service = match ($test->scope) {
            'ltd'                         => new NationalItemBankService(),
            'published_formidable'        => new FormidableService(),
            'published_thieme_meulenhoff' => new ThiemeMeulenhoffService(),
            'published_olympiade'         => new OlympiadeService(),
            'published_olympiade_archive' => new OlympiadeArchiveService(),
            'published_creathlon'         => new CreathlonService(),
            default                       => null,
        };

        return $service;
    }

    public static function getPublishableAuthorByCustomerCode($customerCode): User|null
    {
        $service = static::makeExternalWithCustomerCode($customerCode);

        if (!$service) {
            return null;
        }

        return $service->getSchoolAuthor();
    }



    public static function makeExternalWithCustomerCode($customer_code): ContentSourceService|null
    {
        return match ($customer_code) {
//            config('custom.examschool_customercode')                => new NationalItemBankService(),
            config('custom.national_item_bank_school_customercode') => new NationalItemBankService(),
            config('custom.creathlon_school_customercode')          => new CreathlonService(),
            config('custom.olympiade_school_customercode')          => new OlympiadeService(),
            config('custom.olympiade_archive_school_customercode')  => new OlympiadeArchiveService(),
            config('custom.formidable_school_customercode')         => new FormidableService(),
            config('custom.thieme_meulenhoff_school_customercode')  => new ThiemeMeulenhoffService(),
            default                                                 => null,
        };
    }

    public static function makeWithTab(string $tab): ContentSourceService
    {
        return match ($tab) {
            'school_location'   => new SchoolLocationService,
            'national'          => new NationalItemBankService,
            'umbrella'          => new UmbrellaOrganizationService,
            'formidable'        => new FormidableService,
            'creathlon'         => new CreathlonService,
            'olympiade'         => new OlympiadeService,
            'olympiade_archive' => new OlympiadeArchiveService,
            'thieme_meulenhoff' => new ThiemeMeulenhoffService,
            'personal'          => new PersonalService,
            default             => new PersonalService,
        };
    }

    public static function makeWithTabExternalOnly(string $tab): ContentSourceService|null
    {
        return match ($tab) {
            'umbrella'          => new UmbrellaOrganizationService,
            'formidable'        => new FormidableService,
            'creathlon'         => new CreathlonService,
            'olympiade'         => new OlympiadeService,
            'olympiade_archive' => new OlympiadeArchiveService,
            'thieme_meulenhoff' => new ThiemeMeulenhoffService,
            default             => null,
        };
    }


}
