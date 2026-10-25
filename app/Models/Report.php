<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded = ['id'];

    public function licenses()
    {
        return $this->hasMany(ReportLicense::class);
    }

    public function metadata()
    {
        return $this->hasOne(ReportMeta::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }
}
