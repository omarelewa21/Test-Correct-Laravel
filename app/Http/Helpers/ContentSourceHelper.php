<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use tcCore\Subject;
use tcCore\Test;
use tcCore\User;

class ContentSourceHelper
{
    const PUBLISHABLE_ABBREVIATIONS = ['EXAM', 'LDT', 'PUBLS'];
    const PUBLISHABLE_SCOPES = ['exam', 'ldt', 'published_creathlon'];

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
                    self::testsAvailable('creathlon', $user);
        }

        return false;
    }

    public static function testsAvailable(string $contentSourceName, User $user)
    {
        if (in_array($contentSourceName, ['exam', 'cito'])) {
            $publishedTestScope = $contentSourceName;
        } elseif (in_array($contentSourceName, ['national', 'tbni'])) {
            $publishedTestScope = 'ldt';
        } else {
            $publishedTestScope = 'published_' . $contentSourceName;
        }

        return Test::select('s.base_subject_id')
            ->distinct()
            ->join('subjects as s', 'tests.subject_id', '=', 's.id')
            ->where('tests.scope', '=', $publishedTestScope)
            ->whereIn('s.base_subject_id', Subject::filtered(['user_current' => $user->getKey()], [])->pluck('base_subject_id'))
            ->exists('s.base_subject_id');
    }
}