<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $table = 'reports_enquiries';

    protected $guarded = ['id'];
}
