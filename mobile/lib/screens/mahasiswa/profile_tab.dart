import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class ProfileTab extends StatefulWidget {
  const ProfileTab({super.key});
  @override
  State<ProfileTab> createState() => _ProfileTabState();
}

class _ProfileTabState extends State<ProfileTab> {
  late Future<Map<String, dynamic>> _future;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/mahasiswa/profile', token: _token);
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
      appBar: AppBar(title: const Text('Profil')),
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
            final user = Map<String, dynamic>.from(d['user'] ?? {});
            final student = Map<String, dynamic>.from(d['student'] ?? {});
            final internship = d['internship'] as Map<String, dynamic>?;
            final name = user['name'] ?? '';

            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(color: AppColors.warm, borderRadius: BorderRadius.circular(14)),
                  child: Row(children: [
                    CircleAvatar(radius: 34, backgroundColor: AppColors.primary, child: Text(_initials(name), style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w800))),
                    const SizedBox(width: 16),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(name, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                        decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(999)),
                        child: const Text('Mahasiswa', style: TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w700)),
                      ),
                      const SizedBox(height: 4),
                      Text(user['email'] ?? '', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                    ])),
                  ]),
                ),
                const SizedBox(height: 16),
                if (internship != null) ...[
                  const SectionTitle('Informasi Magang'),
                  AppCard(child: Column(children: [
                    InfoRow('Perusahaan', internship['company'] ?? '-'),
                    InfoRow('Posisi', internship['position'] ?? '-'),
                    InfoRow('Mulai', internship['start_date'] ?? '-'),
                    InfoRow('Selesai', internship['end_date'] ?? 'Belum selesai'),
                  ])),
                ],
                const SectionTitle('Data Mahasiswa'),
                AppCard(child: Column(children: [
                  InfoRow('Kelas', student['the_class'] ?? '-'),
                  InfoRow('Program Studi', student['study_program'] ?? '-'),
                  InfoRow('Jurusan', student['major'] ?? '-'),
                  InfoRow('Tahun Akademik', student['academic_year'] ?? '-'),
                ])),
                const SizedBox(height: 8),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.error,
                    minimumSize: const Size.fromHeight(48),
                    side: const BorderSide(color: AppColors.error),
                  ),
                  onPressed: () async {
                    final ok = await showDialog<bool>(
                      context: context,
                      builder: (c) => AlertDialog(
                        title: const Text('Keluar?'),
                        content: const Text('Anda yakin ingin keluar?'),
                        actions: [
                          TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                          TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Keluar')),
                        ],
                      ),
                    );
                    if (ok == true && context.mounted) context.read<AuthProvider>().logout();
                  },
                  icon: const Icon(Icons.logout),
                  label: const Text('Log Out'),
                ),
                const SizedBox(height: 24),
              ],
            );
          },
        ),
      ),
    );
  }
}
