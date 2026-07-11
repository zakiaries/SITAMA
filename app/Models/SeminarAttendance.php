<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'seminar_id', 'name', 'nim', 'kelas', 'prodi', 'signature_path',
    ];

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }
}
