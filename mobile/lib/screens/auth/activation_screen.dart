import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';
import 'auth_ui.dart';

class ActivationScreen extends StatefulWidget {
  const ActivationScreen({super.key});
  @override
  State<ActivationScreen> createState() => _ActivationScreenState();
}

class _ActivationScreenState extends State<ActivationScreen> {
  final _tokenInput = TextEditingController();
  final _username = TextEditingController();
  final _password = TextEditingController();
  final _passwordConfirm = TextEditingController();

  String? _token;
  String? _userName;
  bool _checking = false;
  bool _saving = false;
  String? _error;

  @override
  void dispose() {
    _tokenInput.dispose();
    _username.dispose();
    _password.dispose();
    _passwordConfirm.dispose();
    super.dispose();
  }

  String _extractToken(String raw) {
    final t = raw.trim();
    if (t.contains('/aktivasi/')) return t.split('/aktivasi/').last.split(RegExp(r'[?#]')).first;
    return t;
  }

  Future<void> _check() async {
    final token = _extractToken(_tokenInput.text);
    if (token.isEmpty) { setState(() => _error = 'Masukkan token / link aktivasi.'); return; }
    setState(() { _checking = true; _error = null; });
    try {
      final data = await ApiClient.get('/aktivasi/$token');
      setState(() {
        _token = token;
        _userName = data['user']?['name'];
      });
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } catch (_) {
      setState(() => _error = 'Tidak dapat terhubung ke server.');
    } finally {
      if (mounted) setState(() => _checking = false);
    }
  }

  Future<void> _activate() async {
    setState(() { _saving = true; _error = null; });
    try {
      final data = await ApiClient.post('/aktivasi/$_token', body: {
        'username': _username.text.trim(),
        'password': _password.text,
        'password_confirmation': _passwordConfirm.text,
      });
      if (!mounted) return;
      showMessage(context, data['message'] ?? 'Akun berhasil diaktivasi.');
      Navigator.pop(context);
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final ready = _token != null;
    return AuthShell(
      showBack: true,
      badge: const AuthBadge(icon: Icons.verified_user_outlined),
      children: [
        authTitle('Aktivasi Akun',
            subtitle: 'Untuk pembimbing industri. Tempel token atau link aktivasi dari email.'),
        if (_error != null) ...[const SizedBox(height: 16), authErrorBox(_error!)],
        const SizedBox(height: 16),
        TextField(
          controller: _tokenInput,
          enabled: !ready,
          decoration: authField('Token / Link Aktivasi', Icons.key_outlined),
        ),
        const SizedBox(height: 12),
        if (!ready)
          authPrimaryButton('Cek Token', _checking ? null : _check, loading: _checking)
        else ...[
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.successBg,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(children: [
              const Icon(Icons.verified_user_outlined, color: AppColors.success, size: 20),
              const SizedBox(width: 10),
              Expanded(child: Text('Akun: ${_userName ?? '-'}',
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13))),
            ]),
          ),
          const SizedBox(height: 14),
          TextField(controller: _username, decoration: authField('Buat Username', Icons.person_outline)),
          const SizedBox(height: 10),
          TextField(controller: _password, obscureText: true, decoration: authField('Buat Password (min. 6)', Icons.lock_outline)),
          const SizedBox(height: 10),
          TextField(controller: _passwordConfirm, obscureText: true, decoration: authField('Konfirmasi Password', Icons.lock_outline)),
          const SizedBox(height: 14),
          authPrimaryButton('Aktivasi Akun', _saving ? null : _activate, loading: _saving),
        ],
      ],
    );
  }
}
