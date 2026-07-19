<?php

namespace App\Services\Chatbot;

use App\Models\JobListing;
use Illuminate\Support\Facades\Schema;

/**
 * Sumber korpus "lowongan/tempat magang" untuk rekomendasi chatbot.
 *
 * Dokumen yang divektorkan = gabungan nama perusahaan + judul/posisi + bidang +
 * divisi + skill + lokasi + deskripsi. Set data sama dengan yang tampil di tab
 * Lowongan mahasiswa (lowongan aktif dari perusahaan berafiliasi/verified).
 */
class LowonganKnowledge
{
    /**
     * @return array<int, array{
     *   id:int, company:string, title:string, bidang:?string, location:?string,
     *   contact:?string, text:string
     * }>
     */
    public static function entries(): array
    {
        try {
            if (! Schema::hasTable('job_listings')) {
                return [];
            }

            $rows = JobListing::query()
                ->where('status', 'active')
                ->whereHas('company', fn ($c) => $c->where('verification_status', 'verified'))
                ->with('company')
                ->latest()
                ->get();

            return $rows->map(function (JobListing $r) {
                $skills = is_array($r->skills) ? implode(' ', $r->skills) : (string) $r->skills;
                $company = $r->company_display_name;

                $contact = $r->pic_name
                    ? trim($r->pic_name . ' ' . ($r->pic_phone ?? ''))
                    : ($r->pic_phone ?? $r->pic_email ?? null);

                // Dokumen untuk TF-IDF (bidang & skill diberi bobot lebih dengan diulang).
                $text = implode(' ', array_filter([
                    $company, $r->title,
                    $r->bidang, $r->bidang,
                    $r->division, $skills, $skills,
                    $r->location, $r->description,
                ]));

                return [
                    'id'       => $r->id,
                    'company'  => $company,
                    'title'    => $r->title,
                    'bidang'   => $r->bidang,
                    'location' => $r->location,
                    'contact'  => $contact,
                    'text'     => $text,
                ];
            })->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
