import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

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
    return Scaffold(
      appBar: AppBar(title: const Text('Aktivasi Akun')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Center(
            child: Container(
              width: 76, height: 76,
              decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle,
                boxShadow: [BoxShadow(color: Color(0x22000000), blurRadius: 16, offset: Offset(0, 8))]),
              child: ClipOval(child: Image.asset('assets/images/logo.png', fit: BoxFit.contain,
                errorBuilder: (_, _, _) => Container(color: AppColors.primary,
                  child: const Icon(Icons.school_rounded, color: Colors.white, size: 34)))),
            ),
          ),
          const SizedBox(height: 16),
          const Text('Untuk pembimbing industri. Tempel token atau link aktivasi dari email.', style: TextStyle(color: AppColors.textSecondary)),
          const SizedBox(height: 16),
          if (_error != null) Padding(padding: const EdgeInsets.only(bottom: 12), child: Text(_error!, style: const TextStyle(color: AppColors.error))),
          TextField(controller: _tokenInput, enabled: !ready, decoration: const InputDecoration(labelText: 'Token / Link Aktivasi')),
          const SizedBox(height: 12),
          if (!ready)
            ElevatedButton(
              onPressed: _checking ? null : _check,
              child: _checking
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                  : const Text('Cek Token'),
            )
          else ...[
            AppCard(child: Row(children: [
              const Icon(Icons.verified_user_outlined, color: AppColors.success),
              const SizedBox(width: 10),
              Expanded(child: Text('Akun: ${_userName ?? '-'}', style: const TextStyle(fontWeight: FontWeight.w700))),
            ])),
            const SizedBox(height: 8),
            TextField(controller: _username, decoration: const InputDecoration(labelText: 'Buat Username')),
            const SizedBox(height: 12),
            TextField(controller: _password, obscureText: true, decoration: const InputDecoration(labelText: 'Buat Password (min. 6)')),
            const SizedBox(height: 12),
            TextField(controller: _passwordConfirm, obscureText: true, decoration: const InputDecoration(labelText: 'Konfirmasi Password')),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: _saving ? null : _activate,
              child: _saving
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                  : const Text('Aktivasi Akun'),
            ),
          ],
        ],
      ),
    );
  }
}
