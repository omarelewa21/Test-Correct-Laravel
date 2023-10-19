<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use tcCore\Lib\Models\BaseModel;
use tcCore\Traits\UuidTrait;

abstract class Versionable extends BaseModel
{
    use SoftDeletes;
    use UuidTrait;

    protected ?User $editingAuthor = null;
    protected ?Versionable $updatedVersion = null;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function versions()
    {
        return $this->morphMany(Version::class, 'versionable')
            ->orWhere('original_id', $this->getKey())
            ->where('versionable_type', static::class);
    }

    protected function associateAuthor(User $author): Versionable
    {
        $this->user()->associate($author);
        return $this;
    }

    protected function associateVersion(int $name = 1, ?Versionable $original = null): Versionable
    {
        $this->versions()
            ->make([
                'name'        => $name,
                'original_id' => $original?->getKey() ?? $this->getKey(),
            ])
            ->user()
            ->associate($this->user)
            ->save();
        return $this;
    }

    public function replicateWithVersion(User $newAuthor): Versionable
    {
        $versionable = $this->replicate();
        $versionable->associateAuthor($newAuthor);
        $versionable->uuid = Uuid::uuid4();
        $versionable->save();

        return $versionable->associateVersion($this->versions()->count() + 1, $this);
    }

    public function scopeFiltered($query, array|Collection $filters = [], array|Collection $sorting = [])
    {
        $query->whereIn('id', function ($query) {
            $query->selectRaw('MAX(versionable_id)')
                ->from('versions')
                ->where('versionable_type', static::class)
                ->groupBy(['original_id', 'user_id']);
        });

        collect($filters)->each(fn($value, $filter) => $query->whereIn($filter, Arr::wrap($value)));
        collect($sorting)->each(fn($direction, $key) => $query->orderBy($key, $direction));

        return $query;
    }

    public static function forUser(User $user)
    {
//         alle woordenlijsten waarvoor ik een versie heb of de laatste versie van alle anderen;
        $prioQuery = static::prioQuery(0);
        $prioQuery2 = static::prioQuery(1)
            ->where('versions.user_id', $user->getKey());

        $subQuery = DB::query()
            ->selectRaw(
                'CASE WHEN MAX(prio = 1) = 1 THEN MAX(CASE WHEN prio = 1 THEN max_id END) ELSE MAX(max_id) END AS max_id'
            )
            ->fromSub(DB::unionRaw($prioQuery, $prioQuery2), 'prioritized_ids')
            ->groupBy('origin');

        return static::whereIn('id', $subQuery);


//        $query->whereIn('id', function ($query) {
//            $query->selectRaw('MAX(versionable_id)')
//                ->from('versions')
//                ->where('versionable_type', static::class)
//                ->groupBy('original_id');
//        });
//
//        $query->whereIn('id', function ($query) {
//            $query->select('versionable_id')
//                ->from('versions')
//                ->where('versionable_type', static::class)
//                ->whereRaw('versionable_id = original_id');
//        });
//
//        return $query;
    }

    private static function prioQuery($prio)
    {
        $table = static::getTableName();
        return static::selectRaw(
            "{$prio} AS prio, MAX({$table}.id) AS max_id, COALESCE(original_id, {$table}.id) AS origin",
        )
            ->leftJoin('versions', function ($join) use ($table) {
                $join->on("{$table}.id", '=', 'versions.versionable_id')
                    ->where('versions.versionable_type', '=', static::class);
            })
            ->groupBy('origin');
    }

    abstract public function syncRelationsFrom(Versionable $original);

    abstract public function handleDuplication();

    public function getEditingAuthor(): User
    {
        return $this->editingAuthor ?? $this->user;
    }

    public function setEditingAuthor(User $editingAuthor): void
    {
        $this->editingAuthor = $editingAuthor;
    }

    public function needsDuplication(): bool
    {
        if ($this->user->isNot($this->getEditingAuthor())) {
            return true;
        }

        return $this->isUsed();
    }

    protected static function resolveVersionableInstance(Versionable $versionable)
    {
        return $versionable->needsDuplication() ? $versionable->handleDuplication() : $versionable;
    }

    public function isOriginal(): bool
    {
        return Version::where('versionable_id', $this->getKey())
            ->where('original_id', $this->getKey())
            ->where('versionable_type', $this::class)
            ->exists();
    }

    public function setUpdatedVersion(?Versionable $updatedVersion): void
    {
        $this->updatedVersion = $updatedVersion;
    }

    public function edit(array $properties)
    {
        $this->fill($properties);
        $this->save();

        if ($this->updatedVersion) {
            $update = $this->updatedVersion;
            $this->updatedVersion = null;
            return $update;
        }

        return $this;
    }
}