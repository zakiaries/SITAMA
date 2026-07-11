import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../widgets/ui.dart';
import 'auth_ui.dart';

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
    return AuthShell(
      showBack: true,
      badge: const AuthBadge(),
      children: [
        authTitle('Daftar', subtitle: 'Akun diverifikasi Kaprodi sebelum aktif.'),
        if (_error != null) ...[const SizedBox(height: 16), authErrorBox(_error!)],
        authSectionLabel('Data Akun'),
        TextField(controller: _name, decoration: authField('Nama Lengkap', Icons.person_outline)),
        const SizedBox(height: 10),
        TextField(controller: _username, decoration: authField('NIM', Icons.badge_outlined)),
        const SizedBox(height: 10),
        TextField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: authField('Email', Icons.mail_outline)),
        const SizedBox(height: 10),
        TextField(controller: _password, obscureText: true, decoration: authField('Password (min. 8)', Icons.lock_outline)),
        const SizedBox(height: 10),
        TextField(controller: _passwordConfirm, obscureText: true, decoration: authField('Konfirmasi Password', Icons.lock_outline)),
        authSectionLabel('Data Akademik'),
        TextField(controller: _studyProgram, decoration: authField('Program Studi', Icons.school_outlined)),
        const SizedBox(height: 10),
        TextField(controller: _major, decoration: authField('Jurusan', Icons.account_balance_outlined)),
        const SizedBox(height: 10),
        TextField(controller: _theClass, decoration: authField('Kelas', Icons.class_outlined)),
        const SizedBox(height: 10),
        TextField(controller: _academicYear, decoration: authField('Tahun Akademik (2024/2025)', Icons.calendar_today_outlined)),
        const SizedBox(height: 18),
        authPrimaryButton('Daftar Sekarang', _saving ? null : _submit, loading: _saving),
      ],
    );
  }
}
