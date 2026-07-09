import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _name = TextEditingController();
  final _username = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _passwordConfirm = TextEditingController();
  final _studyProgram = TextEditingController();
  final _major = TextEditingController();
  final _theClass = TextEditingController();
  final _academicYear = TextEditingController();
  bool _saving = false;
  String? _error;

  @override
  void dispose() {
    for (final c in [_name, _username, _email, _password, _passwordConfirm, _studyProgram, _major, _theClass, _academicYear]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() { _saving = true; _error = null; });
    try {
      final msg = await context.read<AuthProvider>().register({
        'name': _name.text.trim(),
        'username': _username.text.trim(),
        'email': _email.text.trim(),
        'password': _password.text,
        'password_confirmation': _passwordConfirm.text,
        'study_program': _studyProgram.text.trim(),
        'major': _major.text.trim(),
        'the_class': _theClass.text.trim(),
        'academic_year': _academicYear.text.trim(),
      });
      if (!mounted) return;
      showMessage(context, msg);
      Navigator.pop(context);
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } catch (_) {
      setState(() => _error = 'Tidak dapat terhubung ke server.');
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pendaftaran Mahasiswa')),
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
          const Text('Akun akan diverifikasi Kaprodi sebelum aktif.', style: TextStyle(color: AppColors.textSecondary)),
          const SizedBox(height: 16),
          if (_error != null) Padding(padding: const EdgeInsets.only(bottom: 12), child: Text(_error!, style: const TextStyle(color: AppColors.error))),
          const SectionTitle('Data Akun'),
          TextField(controller: _name, decoration: const InputDecoration(labelText: 'Nama Lengkap')),
          const SizedBox(height: 12),
          TextField(controller: _username, decoration: const InputDecoration(labelText: 'NIM')),
          const SizedBox(height: 12),
          TextField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email')),
          const SizedBox(height: 12),
          TextField(controller: _password, obscureText: true, decoration: const InputDecoration(labelText: 'Password (min. 8)')),
          const SizedBox(height: 12),
          TextField(controller: _passwordConfirm, obscureText: true, decoration: const InputDecoration(labelText: 'Konfirmasi Password')),
          const SectionTitle('Data Akademik'),
          TextField(controller: _studyProgram, decoration: const InputDecoration(labelText: 'Program Studi')),
          const SizedBox(height: 12),
          TextField(controller: _major, decoration: const InputDecoration(labelText: 'Jurusan')),
          const SizedBox(height: 12),
          TextField(controller: _theClass, decoration: const InputDecoration(labelText: 'Kelas')),
          const SizedBox(height: 12),
          TextField(controller: _academicYear, decoration: const InputDecoration(labelText: 'Tahun Akademik', hintText: '2024/2025')),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _saving ? null : _submit,
            child: _saving
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                : const Text('Daftar Sekarang'),
          ),
        ],
      ),
    );
  }
}
