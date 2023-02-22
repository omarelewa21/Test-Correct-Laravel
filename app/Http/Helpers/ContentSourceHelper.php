<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use tcCore\Subject;
use tcCore\Test;
use tcCore\User;

class ContentSourceHelper
{
    const PUBLISHABLE_ABBREVIATIONS = ['EXAM', 'LDT', 'PUBLS', 'SBON'];
    const PUBLISHABLE_SCOPES = ['exam', 'ldt', 'published_creathlon', 'published_olympiade'];

    public static function allAllowedForUser(User $user)
    {
        return collect([
            'personal',
            'school_location'
        ])->when(self::canViewContent($user, 'umbrella'),
            fn($collection) => $collection->push('umbrella')
        )->when(self::canViewContent($user, 'national'),
            fn($collection) => $collection->push('national')
        )->when(self::canViewContent($user, 'creathlon'),
            fn($collection) => $collection->push('creathlon')
        )->when(self::canViewContent($user, 'olympiade'),
            fn($collection) => $collection->push('olympiade')
        );
    }

    public static function canViewContent(User $user, string $contentSourceName): bool
    {
        switch ($contentSourceName) {
            case 'personal':
            case 'school_location':
                return true;
            case 'umbrella':
                return $user->hasSharedSections() && !$user->isValidExamCoordinator();
            case 'ldt':
            case 'tbni':
                $contentSourceName = 'national';
            case 'national':
                return $user->schoolLocation->show_national_item_bank;
            case 'creathlon':
                return $user->schoolLocation->allow_creathlon &&
                    self::testsAvailable($user, 'creathlon');
            case 'olympiade':
                return $user->schoolLocation->allow_olympiade &&
                    self::testsAvailable($user, 'olympiade');
        }

        return false;
    }

    public static function testsAvailable(User $user, string $contentSourceName)
    {
        switch($contentSourceName) {

        }

        if ($contentSourceName === 'umbrella') {
            return Test::sharedSectionsFiltered()->exists();
        }
        if ($contentSourceName === 'exam') {
            return Test::ExamFiltered()->exists();
        }
        if ($contentSourceName === 'cito') {
            return Test::CitoFiltered()->exists();
        }
        if (in_array($contentSourceName, ['national', 'tbni'])) {
            return Test::NationalItemBankFiltered()->exists();
        }
        if ($contentSourceName === 'creathlon') {
            return Test::CreathlonItemBankFiltered()->exists();
        }
        if ($contentSourceName === 'olympiade') {
            return Test::OlympiadeItemBankFiltered()->exists();
        }
    }
}