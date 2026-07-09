import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';
import '../widgets/logout_button.dart';
import '../shared/notifikasi_screen.dart';
import 'mahasiswa_detail_screen.dart';

class IndustriListTab extends StatefulWidget {
  const IndustriListTab({super.key});
  @override
  State<IndustriListTab> createState() => _IndustriListTabState();
}

class _IndustriListTabState extends State<IndustriListTab> {
  late Future<Map<String, dynamic>> _future;
  String _search = '';
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/dosen-industri/dashboard', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() => _future = _load());

  String _initials(String n) {
    final p = n.trim().split(RegExp(r'\s+'));
    return p.take(2).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mahasiswa Magang'), actions: [
        IconButton(
          icon: const Icon(Icons.notifications_outlined),
          onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NotifikasiScreen(basePath: '/dosen-industri'))),
        ),
        const LogoutButton(),
      ]),
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
            final stats = Map<String, dynamic>.from(snap.data!['stats'] ?? {});
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
                  StatItem('${stats['total_mahasiswa'] ?? 0}', 'Total', accent: true),
                  StatItem('${stats['aktif'] ?? 0}', 'Aktif'),
                  StatItem('${stats['belum_dikomen'] ?? 0}', 'Belum Dikomen'),
                ]),
                SearchFilterBar(hint: 'Cari nama / NIM...', onChanged: (v) => setState(() => _search = v)),
                if (students.isEmpty)
                  const EmptyState('Belum ada mahasiswa magang.', icon: Icons.people_outline)
                else
                  ...students.map((s) {
                    final total = (s['logbook_total'] ?? 0) as int;
                    final done = (s['logbook_dikomen'] ?? 0) as int;
                    final pct = total > 0 ? done / total : 0.0;
                    return AppCard(
                      onTap: () async {
                        await Navigator.push(context, MaterialPageRoute(builder: (_) => IndustriMahasiswaDetail(studentId: s['id'])));
                        if (context.mounted) _reload();
                      },
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Row(children: [
                          CircleAvatar(radius: 20, backgroundColor: AppColors.blueTint, child: Text(_initials(s['name'] ?? '-'), style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700, fontSize: 12))),
                          const SizedBox(width: 12),
                          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            Text(s['name'] ?? '-', style: const TextStyle(fontWeight: FontWeight.w700)),
                            Text('${s['position'] ?? '-'} · ${s['company'] ?? '-'}', style: const TextStyle(color: AppColors.textMuted, fontSize: 11)),
                          ])),
                          StatusChip(s['is_finished'] == true ? 'selesai' : 'aktif'),
                        ]),
                        const SizedBox(height: 10),
                        Row(children: [
                          Expanded(child: ClipRRect(
                            borderRadius: BorderRadius.circular(4),
                            child: LinearProgressIndicator(value: pct, minHeight: 6, backgroundColor: AppColors.warm, valueColor: const AlwaysStoppedAnimation(AppColors.primary)),
                          )),
                          const SizedBox(width: 10),
                          Text('$done/$total', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
                        ]),
                      ]),
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
