import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

/// Profil generik untuk Dosen & Pembimbing Industri.
/// [basePath] = '/dosen' atau '/dosen-industri'; [roleLabel] untuk pill.
class LecturerProfileScreen extends StatefulWidget {
  final String basePath;
  final String roleLabel;
  const LecturerProfileScreen({super.key, required this.basePath, required this.roleLabel});
  @override
  State<LecturerProfileScreen> createState() => _LecturerProfileScreenState();
}

class _LecturerProfileScreenState extends State<LecturerProfileScreen> {
  late Future<Map<String, dynamic>> _future;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('${widget.basePath}/profile', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() { _future = _load(); });

  String _initials(String n) {
    final p = n.trim().split(RegExp(r'\s+'));
    return p.take(2).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
  }

  Future<void> _editProfile(Map<String, dynamic> user) async {
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _EditForm(basePath: widget.basePath, token: _token, user: user),
    );
    if (saved == true) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        DetailHeader(title: 'Profil Saya', subtitle: widget.roleLabel, showBack: false),
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
                final user = Map<String, dynamic>.from(snap.data!['user'] ?? {});
                final stats = Map<String, dynamic>.from(snap.data!['stats'] ?? {});
                final name = user['name'] ?? '';

                return ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.borderSubtle),
                        boxShadow: kSoftShadow,
                      ),
                      child: Row(children: [
                        CircleAvatar(radius: 28, backgroundColor: AppColors.primary,
                            child: Text(_initials(name), style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800))),
                        const SizedBox(width: 14),
                        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text(name, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
                          const SizedBox(height: 5),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                            decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(999)),
                            child: Text(widget.roleLabel, style: const TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w700)),
                          ),
                          const SizedBox(height: 5),
                          Text(user['email'] ?? '', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                        ])),
                      ]),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [BoxShadow(color: AppColors.primary.withAlpha(64), blurRadius: 20, offset: const Offset(0, 10))],
                      ),
                      child: Column(children: [
                        Text('${stats['total_mahasiswa'] ?? 0}',
                            style: const TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.w800, height: 1)),
                        const SizedBox(height: 3),
                        const Text('Mahasiswa Bimbingan', style: TextStyle(color: Colors.white70, fontSize: 11.5)),
                      ]),
                    ),
                    const SectionTitle('Informasi Akun'),
                    AppCard(child: Column(children: [
                      InfoRow('Nama', user['name'] ?? '-'),
                      InfoRow('Username', user['username'] ?? '-'),
                      InfoRow('Email', user['email'] ?? '-'),
                    ])),
                    const SizedBox(height: 10),
                    OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(48), shape: const StadiumBorder()),
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
                        shape: const StadiumBorder(),
                      ),
                      onPressed: () async {
                        final ok = await showDialog<bool>(context: context, builder: (c) => AlertDialog(
                          title: const Text('Keluar?'),
                          content: const Text('Anda yakin ingin keluar?'),
                          actions: [
                            TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                            TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Keluar')),
                          ],
                        ));
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
  final String basePath, token;
  final Map<String, dynamic> user;
  const _EditForm({required this.basePath, required this.token, required this.user});
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
      await ApiClient.put('${widget.basePath}/profile', token: widget.token, body: body);
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
