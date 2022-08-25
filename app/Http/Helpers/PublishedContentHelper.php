<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use tcCore\Subject;
use tcCore\Test;
use tcCore\User;

class PublishedContentHelper
{
    public static function canViewPublishers(User $user)
    {
        return collect([])
            ->when(self::canViewContent($user, 'umbrella'),
                fn($collection) => $collection->push('umbrella')
            )->when(self::canViewContent($user, 'national'),
                fn($collection) => $collection->push('national')
            )->when(self::canViewContent($user, 'creathlon'),
                fn($collection) => $collection->push('creathlon')
            );
    }

    public static function canViewContent(User $user, string $publisherName): bool
    {
        switch($publisherName)
        {
            case 'umbrella':
                return $user->hasSharedSections();
            case 'national':
                return $user->schoolLocation->show_national_item_bank;
            case 'creathlon':
                return $user->schoolLocation->allow_creathlon &&
                    self::testsAvailable('creathlon', $user);
        }

        return false;
    }

    public static function testsAvailable(string $publisherName, User $user)
    {
        if(!in_array($publisherName, ['exam', 'cito', 'national'])){
            $publishedTestScope = 'published_' . $publisherName;
        }
        if(in_array($publisherName, ['national', 'tbni'])){
            $publishedTestScope = 'ldt';
        }

        return Test::select('s.base_subject_id')
            ->distinct()
            ->join('subjects as s', 'tests.subject_id', '=', 's.id')
            ->where('tests.scope', '=', $publishedTestScope)
            ->whereIn('s.base_subject_id', Subject::filtered(['user_current' => $user->getKey()], [])->pluck('base_subject_id'))
            ->exists('s.base_subject_id');
    }

}