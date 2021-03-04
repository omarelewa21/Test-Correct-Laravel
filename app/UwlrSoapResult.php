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

    public function entries()
    {
        return $this->hasMany(UwlrSoapEntry::class);
    }

    public function report()
    {
        dd($this->entries);
        return $this->entries()->groupBy('key')->count();
    }
}
