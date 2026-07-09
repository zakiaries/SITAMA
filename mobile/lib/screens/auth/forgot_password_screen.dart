import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});
  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _id = TextEditingController();
  @override
  void dispose() { _id.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Lupa Sandi')),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const SizedBox(height: 8),
          Center(
            child: Container(
              width: 84, height: 84,
              decoration: const BoxDecoration(color: AppColors.blueTint, shape: BoxShape.circle),
              child: const Icon(Icons.lock_reset_rounded, color: AppColors.primary, size: 40),
            ),
          ),
          const SizedBox(height: 18),
          const Text('Reset Kata Sandi',
              textAlign: TextAlign.center, style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
          const SizedBox(height: 8),
          const Text('Masukkan NIM / NIP atau email akunmu untuk meminta reset kata sandi.',
              textAlign: TextAlign.center, style: TextStyle(color: AppColors.textSecondary, fontSize: 13, height: 1.5)),
          const SizedBox(height: 24),
          TextField(
            controller: _id,
            decoration: const InputDecoration(
              hintText: 'NIM / NIP atau Email',
              prefixIcon: Icon(Icons.person_outline),
            ),
          ),
          const SizedBox(height: 18),
          ElevatedButton(
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Permintaan reset dikirim. Hubungi admin / Kaprodi bila belum diproses.')),
              );
              Navigator.pop(context);
            },
            child: const Text('Kirim Permintaan Reset'),
          ),
          const SizedBox(height: 12),
          const Text('Permintaan diproses oleh admin / Kaprodi.',
              textAlign: TextAlign.center, style: TextStyle(color: AppColors.textMuted, fontSize: 11.5)),
        ],
      ),
    );
  }
}
