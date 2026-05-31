<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicationSchedule extends Model {
    protected $guarded = [];
    protected $casts = ['days_of_week' => 'array'];
}