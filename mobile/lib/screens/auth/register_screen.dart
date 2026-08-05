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
  final _major = TextEditingController();
  final _theClass = TextEditingController();
  bool _saving = false;
  String? _error;

  /// Prodi yang memakai SIMAMA. Harus sama persis dengan Student::PRODI di sisi
  /// web — server menolak nilai di luar daftar ini. Dulu isian teks bebas, dan
  /// hasilnya satu prodi tercatat dalam dua ejaan.
  static const _daftarProdi = <String>[
    'Teknik Informatika',
    'Teknologi Rekayasa Komputer',
  ];
  String? _studyProgram;

  // Tahun akademik tak lagi dikirim: server menurunkannya dari periode magang
  // yang sedang berjalan, jadi pendaftar tak bisa lagi salah ketik.

  @override
  void dispose() {
    for (final c in [_name, _username, _email, _password, _passwordConfirm, _major, _theClass]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    if (_studyProgram == null) {
      setState(() => _error = 'Pilih program studi lebih dulu.');
      return;
    }

    setState(() { _saving = true; _error = null; });
    try {
      final msg = await context.read<AuthProvider>().register({
        'name': _name.text.trim(),
        'username': _username.text.trim(),
        'email': _email.text.trim(),
        'password': _password.text,
        'password_confirmation': _passwordConfirm.text,
        'study_program': _studyProgram,
        'major': _major.text.trim(),
        'the_class': _theClass.text.trim(),
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
        AuthPasswordField(controller: _password, hint: 'Password (min. 8)'),
        const SizedBox(height: 10),
        AuthPasswordField(controller: _passwordConfirm, hint: 'Konfirmasi Password'),
        authSectionLabel('Data Akademik'),
        DropdownButtonFormField<String>(
          initialValue: _studyProgram,
          decoration: authField('Program Studi', Icons.school_outlined),
          items: [
            for (final prodi in _daftarProdi)
              DropdownMenuItem(value: prodi, child: Text(prodi)),
          ],
          onChanged: (nilai) => setState(() => _studyProgram = nilai),
        ),
        const SizedBox(height: 10),
        TextField(controller: _major, decoration: authField('Jurusan', Icons.account_balance_outlined)),
        const SizedBox(height: 10),
        TextField(controller: _theClass, decoration: authField('Kelas', Icons.class_outlined)),
        const SizedBox(height: 18),
        authPrimaryButton('Daftar Sekarang', _saving ? null : _submit, loading: _saving),
      ],
    );
  }
}
