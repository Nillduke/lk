<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLicense extends Model
{
    protected $table = 'reports_licenses';

    protected $guarded = ['id'];

    public $timestamps = false;
}
