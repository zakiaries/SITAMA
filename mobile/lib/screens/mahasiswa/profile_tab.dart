import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../shared/profile_edit_sheet.dart';
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

  void _reload() => setState(() { _future = _load(); });

  Future<void> _editProfile(Map<String, dynamic> user) async {
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.bg,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(22))),
      builder: (_) => ProfileEditSheet(basePath: '/mahasiswa', token: _token, user: user),
    );
    if (saved == true) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Profil Saya', subtitle: 'Data akun & magang'),
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
            final d = snap.data!;
            final user = Map<String, dynamic>.from(d['user'] ?? {});
            final student = Map<String, dynamic>.from(d['student'] ?? {});
            final internship = d['internship'] as Map<String, dynamic>?;
            final name = user['name'] ?? '';

            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.borderSubtle),
                    boxShadow: kSoftShadow,
                  ),
                  child: Row(children: [
                    ProfileAvatar(name: name, photoUrl: '${user['photo_url'] ?? ''}', radius: 32, fontSize: 20),
                    const SizedBox(width: 14),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 5),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                        decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(999)),
                        child: const Text('Mahasiswa', style: TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w700)),
                      ),
                      const SizedBox(height: 5),
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
                ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(48)),
                  onPressed: () => _editProfile(user),
                  icon: const Icon(Icons.edit_outlined, size: 18),
                  label: const Text('Edit Profil'),
                ),
                const SizedBox(height: 10),
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
        ),
      ]),
    );
  }
}
