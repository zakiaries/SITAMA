import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import 'auth_ui.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});
  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _id = TextEditingController();
  @override
  void dispose() { _id.dispose(); super.dispose(); }

  void _submit() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Permintaan reset dikirim. Hubungi admin / Kaprodi bila belum diproses.')),
    );
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    return AuthShell(
      showBack: true,
      badge: const AuthBadge(icon: Icons.lock_open_outlined),
      children: [
        authTitle('Reset Kata Sandi',
            subtitle: 'Masukkan NIM / NIP atau email akunmu untuk meminta reset kata sandi.'),
        const SizedBox(height: 16),
        TextField(controller: _id, decoration: authField('NIM / NIP atau Email', Icons.person_outline)),
        const SizedBox(height: 14),
        authPrimaryButton('Kirim Permintaan Reset', _submit),
        const SizedBox(height: 14),
        const Text('Permintaan diproses oleh admin / Kaprodi.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
      ],
    );
  }
}
