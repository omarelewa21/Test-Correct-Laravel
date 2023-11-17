<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\BaseSubject;
use tcCore\CompletionQuestion;
use tcCore\Exceptions\QuestionException;
use tcCore\GroupQuestion;
use tcCore\Lib\Repositories\TaxonomyRepository;
use tcCore\MatchingQuestion;
use tcCore\MultipleChoiceQuestion;
use tcCore\OpenQuestion;
use tcCore\Subject;
use tcCore\Tag;
use tcCore\TagRelation;
use tcCore\Test;

trait WithQuestionFilteredHelpers
{
    private function handleFilterParams(&$query, $user, $filters = [])
    {
        $joins = [];
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'base_subject_id':
                    if (isset($filters['source'])) {
                        switch ($filters['source']) {
                            case 'schoolLocation': // only my colleages and me
                                $subjectIdsBuilder = $user->subjects();
                                break;
                            case 'school': //  shared sections
                                $subjectIdsBuilder = $user->subjectsOnlyShared();
                                break;
                            default:
                                $subjectIdsBuilder = $user->subjectsIncludingShared();
                                break;
                        }
                    } else {
                        $subjectIdsBuilder = $user->subjectsIncludingShared();
                    }
                    $subjectIdsBuilder->whereIn('base_subject_id', Arr::wrap($value))->select('subjects.id');
                    $query->whereIn('subject_id', $subjectIdsBuilder->get());
                    break;
                case 'source':
                    if (isset($filters['base_subject_id'])) {
                        // we don't have to do anything, cause here above already caught;
                    } else {
                        switch ($filters['source']) {
                            case 'me': // i need to be the author
                                $query->join('question_authors', 'questions.id', '=', 'question_authors.question_id')
                                    ->where('question_authors.user_id', '=', $user->getKey());
                                break;
                            case 'schoolLocation': // only my colleages and me
                                $query->whereIn('subject_id', $user->subjects()->select('id'));
                                $query->where($this->table . '.owner_id', Auth::user()->school_location_id);
                                break;
                            case 'school': //  shared sections
                                $query->whereIn('subject_id', $user->subjectsOnlyShared()->select('id'));
                                break;
                            default:
                                $query->whereIn('subject_id', $user->subjectsIncludingShared()->select('id'));
                                break;
                        }
                    }
                    break;
                case 'id':
                    if (is_array($value)) {
                        $query->whereIn($this->table . '.id', $value);
                    } else {
                        $query->where($this->table . '.id', '=', $value);
                    }
                    break;
                case 'subject_id':
                    if (is_array($value)) {
                        $query->whereIn('subject_id', $value);
                    } else {
                        $query->where('subject_id', '=', $value);
                    }
                    break;
                case 'education_level_id':
                    if (is_array($value)) {
                        $query->whereIn('education_level_id', $value);
                    } else {
                        $query->where('education_level_id', '=', $value);
                    }
                    break;
                case 'education_level_year':
                    if (is_array($value)) {
                        $query->whereIn('education_level_year', $value);
                    } else {
                        $query->where('education_level_year', '=', $value);
                    }
                    break;
                case 'type':
                    if (is_array($value)) {
                        $filters['type'] = array_map('strtolower', $filters['type']);
                        $query->whereIn('type', $value);
                    } else {
                        $filters['type'] = strtolower($filters['type']);
                        $query->where('type', '=', $value);
                    }
                    break;
                case 'subtype':
                    $joinTable = null;
                    if (is_array($filters['type']) && in_array($filters['type'], array('matchingquestion', 'multiplechoicequestion', 'completionquestion', 'openquestion'))) {
                        break;
                    }

                    switch (strtolower($filters['type'])) {
                        case 'matchingquestion':
                        case 'multiplechoicequestion':
                        case 'completionquestion':
                        case 'openquestion':
                            $joinTable = $filters['type'];
                            break;
                    }

                    if ($joinTable !== null) {
                        $joins[] = $joinTable;
                    } else {
                        break;
                    }

                    if (is_array($value)) {
                        $query->whereIn('subtype', $value);
                    } elseif (strtolower($value) == 'long') {
                        $query->where('subtype', '=', 'long')->orWhere('subtype', '=', 'medium');
                    } else {
                        $query->where('subtype', '=', $value);
                    }
                    break;
                case 'question':
                    $query->where('question', 'LIKE', '%' . $value . '%');
                    break;
                case 'add_to_database':
                    $query->where('add_to_database', '=', $value);
                    break;
                case 'is_subquestion':
                    $query->where('is_subquestion', '=', $value);
                    break;
                case 'without_groups':
                    $query->where('type', '!=', 'GroupQuestion');
                    break;
                case 'author_id':
                    if (is_array($value)) {
                        $query->join('question_authors', 'questions.id', '=', 'question_authors.question_id')
                            ->whereIn('question_authors.user_id', $value);
                    } else {
                        $query->join('question_authors', 'questions.id', '=', 'question_authors.question_id')
                            ->where('question_authors.user_id', '=', $value);
                    }
                    break;
                case 'draft':
                    $query->where('questions.draft', '=', $value);
                    break;
                case 'taxonomy':
                    $taxonomyColumnsWithSearchValues = TaxonomyRepository::filterValuesPerTaxonomyGroup($value);

                    $query->taxonomies($taxonomyColumnsWithSearchValues);
                    break;
            }
        }
        return $joins;
    }

    private function handleFilteredSorting(&$query, $sorting = []): void
    {
        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'type':
                case 'question':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }
            switch (strtolower($key)) {
                case 'id':
                case 'type':
                case 'question':
                    $query->orderBy($key, $value);
                    break;
            }
        }
    }

    private function handleQueryJoins(&$query, array $joins): void
    {
        foreach ($joins as $target) {
            switch (strtolower($target)) {
                case 'tests':
                    $test = new Test();
                    $query->join($test->getTable(), $test->getTable() . '.' . $test->getKeyName(), '=', $this->getTable() . '.test_id');
                    break;
                case 'matchingquestion':
                    $matchingQuestion = new MatchingQuestion();
                    $query->join($matchingQuestion->getTable(), $matchingQuestion->getTable() . '.' . $matchingQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
                case 'multiplechoicequestion':
                    $multipleChoiceQuestion = new MultipleChoiceQuestion();
                    $query->join($multipleChoiceQuestion->getTable(), $multipleChoiceQuestion->getTable() . '.' . $multipleChoiceQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
                case 'completionquestion':
                    $completionQuestion = new CompletionQuestion();
                    $query->join($completionQuestion->getTable(), $completionQuestion->getTable() . '.' . $completionQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
                case 'openquestion':
                    $openQuestion = new OpenQuestion();
                    $query->join($openQuestion->getTable(), $openQuestion->getTable() . '.' . $openQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
                case 'groupquestion':
                    $groupQuestion = new GroupQuestion();
                    $query->leftJoin($groupQuestion->getTable(), $groupQuestion->getTable() . '.' . $groupQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
            }
        }
    }

    /**
     * @param $filters
     * @return bool[]
     */
    private function getOpenQuestionTableSearchVariables($filters): array
    {
        if (array_key_exists('type', $filters)) {
            if (is_array($filters['type'])) {
                $types = array_map('strtolower', $filters['type']);
            } else {
                $types = strtolower($filters['type']);
            }

            if ((is_array($types) && in_array('openquestion', $types) && count($types) === 1) || (!is_array($types) && $types == 'openquestion')) {
                $openQuestionOnly = true;
            } else {
                $openQuestionOnly = false;
            }

            if ((is_array($types) && !in_array('openquestion', $types)) || (!is_array($types) && $types !== 'openquestion')) {
                $openQuestionDisabled = true;
            } else {
                $openQuestionDisabled = false;
            }
        } else {
            $openQuestionOnly = false;
            $openQuestionDisabled = false;
        }
        return array($openQuestionOnly, $openQuestionDisabled);
    }

    private function joinTagsIfExists(&$query, array $value): array
    {
        $tags = Tag::whereIn('name', $value)->pluck('name', 'id')->all();
        if ($tags) {
            $tags = array_map('strtolower', $tags);
            $subQuery = TagRelation::where('tag_relation_type', '=', 'tcCore\Question')
                ->whereIn('tag_id', array_keys($tags))
                ->select([
                    'tag_relation_id',
                    DB::raw('CONCAT(\' \', GROUP_CONCAT(tag_id SEPARATOR \' \'), \' \') as tags')
                ])
                ->groupBy('tag_relation_id');

            $query->leftJoinSub($subQuery, 'tags', function ($join) {
                $join->on('tags.tag_relation_id', '=', $this->getTable() . '.' . $this->getKeyName());
            });
        }
        return $tags;
    }

    /**
     * @param $query
     * @param array $searchValue
     * @param array $tags
     * @param bool $openQuestionDisabled
     * @param OpenQuestion|null $openQuestion
     * @return void
     */
    private function handleSearchWithTextAndTags(&$query, array $searchValue, array $tags, bool $openQuestionDisabled, ?OpenQuestion $openQuestion): void
    {
        foreach ($searchValue as $v) {
            if (!in_array(strtolower($v), $tags)) {
                $query->where(function ($query) use ($v, $openQuestionDisabled, $openQuestion) {
                    $query->where('question', 'LIKE', '%' . $v . '%');
                    if (!$openQuestionDisabled) {
                        $query->orWhere(DB::raw('IFNULL(' . $openQuestion->getTable() . '.answer, \'\')'), 'LIKE', '%' . $v . '%');
                    }
                    $query->orWhere('group_questions.name', 'like', '%' . $v . '%');
                });
            } else {
                $tagId = array_search(strtolower($v), $tags);
                $query->where(function ($query) use ($v, $openQuestionDisabled, $openQuestion, $tagId) {
                    $query->where('question', 'LIKE', '%' . $v . '%');
                    if (!$openQuestionDisabled) {
                        $query->orWhere(DB::raw('IFNULL(' . $openQuestion->getTable() . '.answer, \'\')'), 'LIKE', '%' . $v . '%');
                    }
                    $query->orWhere(DB::raw('IFNULL(tags.tags, \'\')'), 'LIKE', '% ' . $tagId . ' %');
                    $query->orWhere('group_questions.name', 'like', '%' . $v . '%');
                });
            }
        }
    }

    /**
     * @param &$query
     * @param bool $openQuestionOnly
     * @param $filters
     * @param bool $openQuestionDisabled
     * @param OpenQuestion|null $openQuestion
     * @param array $joins
     * @return array
     */
    private function getJoinsFromOpenQuestionSearch(&$query, bool $openQuestionOnly, $filters, bool $openQuestionDisabled, ?OpenQuestion $openQuestion): array
    {
        $joins = [];
        if (!$openQuestionOnly && !array_key_exists('subtype', $filters) && !$openQuestionDisabled) {
            // instead of just set the joins to add openquestion, the join takes place straight away. Probably due to further filters and/ or sortings
            $query->leftJoin($openQuestion->getTable(), $openQuestion->getTable() . '.' . $openQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
        } elseif ($openQuestionOnly) {
            // maybe it could be joined straight away just as above.
            $joins[] = 'openquestion';
        }
        $joins[] = 'groupquestion';
        return $joins;
    }

    /**
     * @param $query
     * @param $filters
     * @return array
     */
    private function handleSearchFilters(&$query, $filters): array
    {
        if (!array_key_exists('search', $filters)) {
            return [];
        }

        $searchValue = $filters['search'];

        // Decide whenever open question table has to be searched/joined
        [$openQuestionOnly, $openQuestionDisabled] = $this->getOpenQuestionTableSearchVariables($filters);

        $openQuestion = $openQuestionDisabled ? null : new OpenQuestion();

        $searchValue = is_array($searchValue) ? $searchValue : [$searchValue];

        // Join tags
        $tags = $this->joinTagsIfExists($query, $searchValue);

        // Search terms + tags
        $this->handleSearchWithTextAndTags($query, $searchValue, $tags, $openQuestionDisabled, $openQuestion);

        $joins = $this->getJoinsFromOpenQuestionSearch($query, $openQuestionOnly, $filters, $openQuestionDisabled, $openQuestion);

        return $joins;
    }

    private function handlePublishedFilterParams(&$query, $filters = [])
    {
        $filters = collect($filters);
        $query->where('scope', 'NOT LIKE', 'not_%'); //Get all questions with a published scope

        if ($filters->has('source') || $filters->has('base_subject_id')) {
            $this->addSourceFilterToPublishedQuery(
                $query,
                $filters->get('source', ''),
                $filters->get('base_subject_id', [])
            );
            $filters = $filters->except(['source', 'base_subject_id']);
        }

        $this->handleFilterParams($query, Auth::user(), $filters->toArray());

        return $query;
    }

    private function addSourceFilterToPublishedQuery(&$query, $source, $baseSubjectIds = [])
    {
        $subjectsQuery = match ($source) {
            'national'          => Subject::nationalItemBankFiltered(),
            'creathlon'         => Subject::creathlonFiltered(),
            'thieme_meulenhoff' => Subject::thiemeMeulenhoffFiltered(),
            'olympiade'         => Subject::olympiadeFiltered(),
            'olympiade_archive' => Subject::olympiadeArchiveFiltered(),
            'formidable'         => Subject::formidableFiltered(),
            default             => null,
        };

        return $query->when($subjectsQuery, function ($query) use ($subjectsQuery, $baseSubjectIds) {
            $query->whereIn(
                'subject_id',
                $subjectsQuery->whereIn(
                    'base_subject_id',
                    $this->getBaseSubjectsToFilterWith($baseSubjectIds)
                )->select('id')
            );
        });
    }

    private function getBaseSubjectsToFilterWith($baseSubjectIds = []): array
    {
        $useBaseSubjects = !empty($baseSubjectIds);
        $allowedBaseSubjectsForUser = BaseSubject::currentForAuthUser()->pluck('id');
        $baseSubjectsToGetSubjectsWith = $allowedBaseSubjectsForUser;

        if ($useBaseSubjects) {
            $baseSubjectsToGetSubjectsWith = collect($baseSubjectIds)->filter(function ($baseSubjectId) use ($allowedBaseSubjectsForUser) {
                return $allowedBaseSubjectsForUser->contains($baseSubjectId);
            });
            if ($baseSubjectsToGetSubjectsWith->isEmpty()) {
                throw new QuestionException('Cannot filter on base subjects not being given in the current period.');
            }
        }

        return $baseSubjectsToGetSubjectsWith->toArray();
    }
}