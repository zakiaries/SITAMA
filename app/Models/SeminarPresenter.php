<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Mahasiswa penyaji dalam satu sesi seminar, sekaligus menyimpan ketersediaan
 * tanggal yang ia isi saat dosen membuka penjadwalan.
 */
class SeminarPresenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'seminar_id', 'student_id', 'available_dates', 'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
