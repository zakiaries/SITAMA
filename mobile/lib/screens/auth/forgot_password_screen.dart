import 'package:flutter/material.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import 'auth_ui.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});
  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _id = TextEditingController();
  bool _loading = false;

  @override
  void dispose() { _id.dispose(); super.dispose(); }

  Future<void> _submit() async {
    if (_loading) return;
    final username = _id.text.trim();
    if (username.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('NIM / username wajib diisi.')));
      return;
    }
    setState(() => _loading = true);
    try {
      final res = await ApiClient.post('/lupa-password', body: {'username': username});
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('${res['message'] ?? 'Jika terdaftar, link reset dikirim ke email terdaftar.'}')),
      );
      Navigator.pop(context);
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tidak dapat terhubung ke server.')));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return AuthShell(
      showBack: true,
      badge: const AuthBadge(icon: Icons.lock_open_outlined),
      children: [
        authTitle('Reset Kata Sandi',
            subtitle: 'Masukkan NIM / username akunmu. Link untuk membuat kata sandi baru akan dikirim ke email yang kamu daftarkan.'),
        const SizedBox(height: 16),
        TextField(controller: _id, decoration: authField('NIM / Username', Icons.person_outline)),
        const SizedBox(height: 14),
        authPrimaryButton(_loading ? 'Mengirim...' : 'Kirim Link Reset', _submit),
        const SizedBox(height: 14),
        const Text('Cek kotak masuk (dan folder spam) email terdaftarmu.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
      ],
    );
  }
}
