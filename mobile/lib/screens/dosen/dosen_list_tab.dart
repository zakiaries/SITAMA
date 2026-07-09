import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';
import '../widgets/logout_button.dart';
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

  void _reload() => setState(() => _future = _load());

  String _initials(String n) {
    final p = n.trim().split(RegExp(r'\s+'));
    return p.take(2).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mahasiswa Bimbingan'), actions: [
        IconButton(
          icon: const Icon(Icons.notifications_outlined),
          onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NotifikasiScreen(basePath: '/dosen'))),
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
            final counts = Map<String, dynamic>.from(snap.data!['counts'] ?? {});
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
                if (students.isEmpty)
                  const EmptyState('Belum ada mahasiswa bimbingan.', icon: Icons.people_outline)
                else
                  ...students.map((s) => AppListTile(
                        leading: CircleAvatar(
                          radius: 22, backgroundColor: AppColors.blueTint,
                          child: Text(_initials(s['name'] ?? '-'), style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700, fontSize: 13)),
                        ),
                        title: s['name'] ?? '-',
                        subtitle: '${s['username'] ?? ''} · ${s['company'] ?? '-'}\n${s['guidances_count'] ?? 0} bimbingan · ${s['logbooks_count'] ?? 0} log',
                        trailing: StatusChip(s['status'] ?? ''),
                        onTap: () async {
                          await Navigator.push(context, MaterialPageRoute(builder: (_) => DosenMahasiswaDetail(studentId: s['id'])));
                          if (context.mounted) _reload();
                        },
                      )),
                const SizedBox(height: 24),
              ],
            );
          },
        ),
      ),
    );
  }
}
