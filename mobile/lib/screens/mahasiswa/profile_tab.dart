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

  void _reload() => setState(() { _future = _load(); });

  Future<void> _editProfile(Map<String, dynamic> user) async {
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _EditForm(token: _token, user: user),
    );
    if (saved == true) _reload();
  }

  String _initials(String n) {
    final p = n.trim().split(RegExp(r'\s+'));
    return p.take(2).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
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
                    CircleAvatar(radius: 32, backgroundColor: AppColors.primary,
                        child: Text(_initials(name), style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800))),
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

class _EditForm extends StatefulWidget {
  final String token;
  final Map<String, dynamic> user;
  const _EditForm({required this.token, required this.user});
  @override
  State<_EditForm> createState() => _EditFormState();
}

class _EditFormState extends State<_EditForm> {
  late final TextEditingController _name = TextEditingController(text: widget.user['name'] ?? '');
  late final TextEditingController _email = TextEditingController(text: widget.user['email'] ?? '');
  final _password = TextEditingController();
  final _passwordConfirm = TextEditingController();
  bool _saving = false;
  String? _error;

  @override
  void dispose() { _name.dispose(); _email.dispose(); _password.dispose(); _passwordConfirm.dispose(); super.dispose(); }

  Future<void> _save() async {
    setState(() { _saving = true; _error = null; });
    final body = {'name': _name.text.trim(), 'email': _email.text.trim()};
    if (_password.text.isNotEmpty) {
      body['password'] = _password.text;
      body['password_confirmation'] = _passwordConfirm.text;
    }
    try {
      await ApiClient.put('/mahasiswa/profile', token: widget.token, body: body);
      if (mounted) { showMessage(context, 'Profil diperbarui.'); Navigator.pop(context, true); }
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(left: 16, right: 16, top: 16, bottom: MediaQuery.of(context).viewInsets.bottom + 16),
      child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        const Text('Edit Profil', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
        const SizedBox(height: 16),
        if (_error != null) Padding(padding: const EdgeInsets.only(bottom: 10), child: Text(_error!, style: const TextStyle(color: AppColors.error))),
        TextField(controller: _name, decoration: const InputDecoration(labelText: 'Nama')),
        const SizedBox(height: 12),
        TextField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email')),
        const SizedBox(height: 12),
        TextField(controller: _password, obscureText: true, decoration: const InputDecoration(labelText: 'Password baru (opsional)')),
        const SizedBox(height: 12),
        TextField(controller: _passwordConfirm, obscureText: true, decoration: const InputDecoration(labelText: 'Konfirmasi password')),
        const SizedBox(height: 20),
        ElevatedButton(
          onPressed: _saving ? null : _save,
          child: _saving
              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
              : const Text('Simpan'),
        ),
      ]),
    );
  }
}
