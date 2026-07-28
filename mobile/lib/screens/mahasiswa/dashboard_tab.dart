import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';
import '../shared/notifikasi_screen.dart';
import 'seminar_screen.dart';
import 'nilai_screen.dart';
import 'ajukan_magang_screen.dart';
import 'laporan_screen.dart';

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

  void _reload() => setState(() { _future = _load(); });

  void _push(Widget page) =>
      Navigator.push(context, MaterialPageRoute(builder: (_) => page));

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
      backgroundColor: AppColors.warm,
      body: RefreshIndicator(
        onRefresh: () async => _reload(),
        child: FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snap.hasError) {
              return ListView(children: [
                SizedBox(height: MediaQuery.of(context).size.height * .3),
                ErrorRetry(message: '${snap.error}', onRetry: _reload),
              ]);
            }
            final d = snap.data!;
            final stats = Map<String, dynamic>.from(d['stats'] ?? {});
            final internship = d['internship'] as Map<String, dynamic>?;
            final guidances = List<Map<String, dynamic>>.from(d['latest_guidances'] ?? []);
            final name = d['user']?['name'] ?? '';

            return ListView(
              padding: EdgeInsets.zero,
              children: [
                // ── Header biru (avatar + sambutan + lonceng, TANPA logout) ──
                Container(
                  width: double.infinity,
                  padding: EdgeInsets.fromLTRB(18, MediaQuery.of(context).padding.top + 16, 18, 40),
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [AppColors.primary, Color(0xFF2F78FF)],
                      begin: Alignment.topLeft, end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.vertical(bottom: Radius.circular(26)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            width: 28, height: 28,
                            decoration: BoxDecoration(color: Colors.white.withAlpha(52), borderRadius: BorderRadius.circular(8)),
                            child: const Icon(Icons.laptop_mac, color: Colors.white, size: 16),
                          ),
                          const SizedBox(width: 8),
                          const Text('SIMAMA', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w700)),
                          const Spacer(),
                          HeaderAction(Icons.notifications_outlined, () => Navigator.push(context,
                              MaterialPageRoute(builder: (_) => const NotifikasiScreen(basePath: '/mahasiswa')))),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Container(
                            width: 46, height: 46,
                            decoration: BoxDecoration(
                              color: Colors.white.withAlpha(40),
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white.withAlpha(115), width: 1.5),
                            ),
                            child: const Icon(Icons.person_outline, color: Colors.white, size: 24),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Selamat datang,', style: TextStyle(color: Colors.white70, fontSize: 12)),
                                Text(name, maxLines: 1, overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w700)),
                                const Text('Mahasiswa', style: TextStyle(color: Colors.white70, fontSize: 11)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                // ── Kartu statistik 2×2 (naik menimpa header) ──
                Transform.translate(
                  offset: const Offset(0, -22),
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(14, 0, 14, 0),
                    child: GridView.count(
                      crossAxisCount: 2,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      mainAxisSpacing: 10,
                      crossAxisSpacing: 10,
                      childAspectRatio: 2.5,
                      children: [
                        StatCard(icon: Icons.book_outlined, bg: AppColors.blueTint, fg: AppColors.primary, value: '${stats['logbook'] ?? 0}', label: 'Log Book'),
                        StatCard(icon: Icons.forum_outlined, bg: AppColors.successBg, fg: AppColors.success, value: '${stats['bimbingan'] ?? 0}', label: 'Bimbingan'),
                        StatCard(icon: Icons.co_present_outlined, bg: AppColors.warnBg, fg: AppColors.warnText, value: '${stats['seminar'] ?? 0}', label: 'Seminar'),
                        StatCard(icon: Icons.event_available_outlined, bg: const Color(0xFFF0ECFB), fg: const Color(0xFF6B4DB8), value: '${stats['hari_magang'] ?? 0}', label: 'Hari Magang'),
                      ],
                    ),
                  ),
                ),
                // ── Akses cepat ──
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SectionTitle('Akses Cepat'),
                      GridView.count(
                        crossAxisCount: 4,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        mainAxisSpacing: 10,
                        crossAxisSpacing: 10,
                        childAspectRatio: 0.82,
                        children: [
                          MenuTile(Icons.note_add_outlined, 'Ajukan', onTap: () => _push(const AjukanMagangScreen())),
                          MenuTile(Icons.event_outlined, 'Seminar', onTap: () => _push(const SeminarScreen())),
                          MenuTile(Icons.description_outlined, 'Laporan', onTap: () => _push(const LaporanScreen())),
                          MenuTile(Icons.star_border_rounded, 'Nilai', onTap: () => _push(const NilaiScreen())),
                        ],
                      ),
                      const SectionTitle('Magang Saya'),
                      if (internship != null)
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
                        )
                      else
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: AppColors.borderSubtle),
                            boxShadow: kSoftShadow,
                          ),
                          child: Column(
                            children: [
                              Container(
                                width: 60, height: 60,
                                decoration: const BoxDecoration(color: AppColors.blueTint, shape: BoxShape.circle),
                                child: const Icon(Icons.work_outline, color: AppColors.primary, size: 27),
                              ),
                              const SizedBox(height: 12),
                              const Text('Belum ada magang aktif',
                                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
                              const SizedBox(height: 4),
                              const Text('Ajukan magang untuk mulai bimbingan dan mengisi log book.',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.4)),
                              const SizedBox(height: 14),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton(
                                  style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(46), shape: const StadiumBorder()),
                                  onPressed: () => _push(const AjukanMagangScreen()),
                                  child: const Text('Ajukan Magang Sekarang'),
                                ),
                              ),
                            ],
                          ),
                        ),
                      const SectionTitle('Bimbingan Terbaru'),
                      if (guidances.isEmpty)
                        const EmptyState('Belum ada bimbingan',
                            icon: Icons.menu_book_outlined,
                            hint: 'Catatan bimbingan terbaru akan muncul di sini.')
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
                      const SizedBox(height: 20),
                    ],
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }
}
