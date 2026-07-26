<?php

namespace Tests\Feature;

use App\Models\{ChatbotKnowledge, Company, JobListing, Lecturer, Notification, Seminar, Student};
use Illuminate\Support\Str;
use Tests\FeatureTestCase;

/** Buka semua halaman web (GET) tiap peran; tandai yang 500. */
class SmokeTest extends FeatureTestCase
{
    public function test_all_pages_render(): void
    {
        // Fixtures tambahan agar route berparameter bisa diisi.
        $dosen   = Lecturer::whereHas('user', fn ($q) => $q->where('username', 'dosen1'))->first();
        $company = Company::first() ?? Company::create(['name' => 'PT Smoke', 'verification_status' => 'verified']);
        $sem     = Seminar::create(['lecturer_id' => $dosen->id, 'title' => 'Smoke', 'program' => 'TI', 'status' => 'scheduled', 'access_token' => Str::random(48)]);
        $job     = JobListing::create(['company_id' => $company->id, 'title' => 'Dev', 'status' => 'active']);
        $kaprodiU = $this->userByUsername('kaprodi');
        $notif   = Notification::create(['user_id' => $kaprodiU->id, 'message' => 'x', 'date' => now()->toDateString(), 'category' => 'general', 'is_read' => false]);
        $kb      = ChatbotKnowledge::create(['pertanyaan' => 'Q?', 'kata_kunci' => 'q', 'jawaban' => 'A', 'kategori' => 'umum', 'is_active' => true]);

        $ids = [
            'student' => Student::whereHas('user', fn ($q) => $q->where('username', '3.34.23.2.01'))->value('id'),
            'lecturer' => $dosen->id,
            'seminar' => $sem->id,
            'jobListing' => $job->id,
            'lowongan' => $job->id,
            'notification' => $notif->id,
            'chatbotKnowledge' => $kb->id,
        ];
        $roleUser = [
            'mahasiswa' => $this->userByUsername('3.34.23.2.01'),
            'dosen-industri' => $this->userByUsername('industri1'),
            'dosen' => $this->userByUsername('dosen1'),
            'kaprodi' => $kaprodiU,
        ];

        $bad = [];
        foreach (app('router')->getRoutes() as $route) {
            if (! in_array('GET', $route->methods())) continue;
            $uri = $route->uri();
            if (preg_match('#^(api|sanctum|_ignition)#', $uri)) continue;
            if (str_contains($uri, '{token}')) continue; // token khusus, diuji terpisah

            $prefix = explode('/', $uri)[0];
            $actor  = $roleUser[$prefix] ?? null;

            $params = [];
            $skip = false;
            if (preg_match_all('/\{(\w+)\??\}/', $uri, $m)) {
                foreach ($m[1] as $p) {
                    if (empty($ids[$p])) { $skip = true; break; }
                    $params[$p] = $ids[$p];
                }
            }
            if ($skip) continue;
            if (in_array($prefix, ['mahasiswa', 'dosen', 'dosen-industri', 'kaprodi']) && ! $actor) continue;

            $name = $route->getName();
            $url  = $name && $params ? route($name, $params) : '/' . ltrim($uri, '/');
            $req  = $actor ? $this->actingAs($actor) : $this;
            $status = $req->get($url)->getStatusCode();
            if ($status >= 500) {
                $bad[] = "[$status] " . ($name ?? $uri);
            }
        }

        $this->assertEmpty($bad, "Halaman 500:\n" . implode("\n", $bad));
    }
}
