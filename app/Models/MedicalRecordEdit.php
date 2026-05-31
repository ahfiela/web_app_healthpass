<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicalRecordEdit extends Model {
    protected $guarded = [];
    protected $casts = ['proposed_changes' => 'array'];
    public function medicalRecord() { return $this->belongsTo(MedicalRecord::class); }
}