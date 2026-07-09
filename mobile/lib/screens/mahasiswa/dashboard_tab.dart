import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/logout_button.dart';
import '../widgets/ui.dart';
import '../shared/notifikasi_screen.dart';

class DashboardTab extends StatefulWidget {
  const DashboardTab({super.key});
  @override
  State<DashboardTab> createState() => _DashboardTabState();
}

class _DashboardTabState extends State<DashboardTab> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final token = context.read<AuthProvider>().token;
    final data = await ApiClient.get('/mahasiswa/dashboard', token: token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() => _future = _load());

  /// Petakan status bimbingan → jenis StatusDot.
  String _dot(String status) {
    final s = status.toLowerCase();
    if (s == 'approved' || s == 'selesai' || s == 'dinilai') return 'done';
    if (s == 'rejected') return 'rejected';
    if (s == 'aktif' || s == 'scheduled' || s == 'terdaftar') return 'blue';
    return 'pending';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('SITAMA'),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            onPressed: () => Navigator.push(context, MaterialPageRoute(
              builder: (_) => const NotifikasiScreen(basePath: '/mahasiswa'),
            )),
          ),
          const LogoutButton(),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => _reload(),
        child: FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snap.hasError) {
              return ErrorRetry(message: '${snap.error}', onRetry: _reload);
            }
            final d = snap.data!;
            final stats = Map<String, dynamic>.from(d['stats'] ?? {});
            final internship = d['internship'] as Map<String, dynamic>?;
            final guidances = List<Map<String, dynamic>>.from(d['latest_guidances'] ?? []);

            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // ── Kartu sambutan ──
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(18),
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: AppColors.blueTint,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Selamat datang,',
                          style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                      const SizedBox(height: 2),
                      Text(d['user']?['name'] ?? '',
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
                    ],
                  ),
                ),
                // ── Statistik ringkas ──
                StatStrip([
                  StatItem('${stats['logbook'] ?? 0}', 'Log Book', accent: true),
                  StatItem('${stats['bimbingan'] ?? 0}', 'Bimbingan'),
                  StatItem('${stats['seminar'] ?? 0}', 'Seminar'),
                  StatItem('${stats['hari_magang'] ?? 0}', 'Hari Magang'),
                ]),
                // ── Info magang aktif ──
                if (internship != null) ...[
                  const SectionTitle('Info Magang Aktif'),
                  AppCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        InfoRow('Perusahaan', internship['company'] ?? '-'),
                        InfoRow('Posisi', internship['position'] ?? '-'),
                        InfoRow('Dosen Pembimbing', internship['lecturer'] ?? '-'),
                        InfoRow('Mulai', internship['start_date'] ?? '-'),
                      ],
                    ),
                  ),
                ],
                // ── Bimbingan terbaru ──
                const SectionTitle('Bimbingan Terbaru'),
                if (guidances.isEmpty)
                  const EmptyState('Belum ada bimbingan.', icon: Icons.menu_book_outlined)
                else
                  ...guidances.map((g) {
                    final status = (g['status'] ?? '').toString();
                    return AppListTile(
                      leading: StatusDot(_dot(status)),
                      title: g['title'] ?? '',
                      subtitle: g['date'] ?? '',
                      trailing: status.isEmpty ? null : StatusChip(status),
                    );
                  }),
                const SizedBox(height: 24),
              ],
            );
          },
        ),
      ),
    );
  }
}
