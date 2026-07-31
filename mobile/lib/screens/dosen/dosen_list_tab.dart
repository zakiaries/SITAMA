import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';
import '../shared/notifikasi_screen.dart';
import 'mahasiswa_detail_screen.dart';

class DosenListTab extends StatefulWidget {
  const DosenListTab({super.key});
  @override
  State<DosenListTab> createState() => _DosenListTabState();
}

class _DosenListTabState extends State<DosenListTab> {
  late Future<Map<String, dynamic>> _future;
  String _search = '';
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/dosen/dashboard', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() { _future = _load(); });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        DetailHeader(
          title: 'Mahasiswa Bimbingan',
          subtitle: 'Daftar mahasiswa bimbingan Anda',
          showBack: false,
          trailing: IconButton(
            icon: const Icon(Icons.notifications_outlined, color: Colors.white),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NotifikasiScreen(basePath: '/dosen'))),
          ),
        ),
        Expanded(
          child: RefreshIndicator(
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
                final counts = Map<String, dynamic>.from(snap.data!['counts'] ?? {});
                final menunggu = Map<String, dynamic>.from(snap.data!['menunggu_tanggapan'] ?? {});
                final totalMenunggu = (menunggu['total'] ?? 0) as int;
                var students = List<Map<String, dynamic>>.from(snap.data!['students'] ?? []);
                if (_search.isNotEmpty) {
                  final q = _search.toLowerCase();
                  students = students.where((s) =>
                      '${s['name'] ?? ''}'.toLowerCase().contains(q) ||
                      '${s['username'] ?? ''}'.toLowerCase().contains(q)).toList();
                }

                return ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    StatStrip([
                      StatItem('${counts['semua'] ?? 0}', 'Semua', accent: true),
                      StatItem('${counts['belum'] ?? 0}', 'Belum Dinilai'),
                      StatItem('${counts['dinilai'] ?? 0}', 'Sudah Dinilai'),
                    ]),
                    SearchFilterBar(hint: 'Cari nama / NIM...', onChanged: (v) => setState(() => _search = v)),
                    if (totalMenunggu > 0)
                      Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        decoration: BoxDecoration(
                          color: AppColors.errorBg,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(children: [
                          CountBadge(totalMenunggu),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'menunggu tanggapanmu — '
                              '${menunggu['logbook'] ?? 0} logbook, '
                              '${menunggu['bimbingan'] ?? 0} bimbingan, '
                              '${menunggu['laporan'] ?? 0} laporan',
                              style: const TextStyle(color: AppColors.error, fontSize: 12, fontWeight: FontWeight.w600),
                            ),
                          ),
                        ]),
                      ),
                    if (students.isEmpty)
                      const EmptyState('Belum ada mahasiswa bimbingan',
                          icon: Icons.people_outline,
                          hint: 'Mahasiswa yang Anda bimbing akan muncul di sini.')
                    else
                      ...students.map((s) {
                        final perlu = (s['perlu_tanggapan'] ?? 0) as int;
                        return AppListTile(
                            leading: UserAvatar(
                              name: '${s['name'] ?? '-'}',
                              photoUrl: s['photo_url'] as String?,
                            ),
                            title: s['name'] ?? '-',
                            subtitle: '${s['username'] ?? ''} · ${s['company'] ?? '-'}\n'
                                '${s['guidances_count'] ?? 0} bimbingan · ${s['logbooks_count'] ?? 0} log'
                                '${perlu > 0 ? '\n$perlu menunggu tanggapanmu' : ''}',
                            trailing: perlu > 0
                                ? Row(mainAxisSize: MainAxisSize.min, children: [
                                    CountBadge(perlu),
                                    const SizedBox(width: 6),
                                    StatusChip(s['status'] ?? ''),
                                  ])
                                : StatusChip(s['status'] ?? ''),
                            onTap: () async {
                              await Navigator.push(context, MaterialPageRoute(builder: (_) => DosenMahasiswaDetail(studentId: s['id'])));
                              if (context.mounted) _reload();
                            },
                          );
                      }),
                    const SizedBox(height: 24),
                  ],
                );
              },
            ),
          ),
        ),
      ]),
    );
  }
}
