import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/config/assets/app_images.dart';
import 'package:sitama/core/config/themes/app_color.dart';
import 'package:sitama/core/shared/widgets/alert/custom_snackbar.dart';
import 'package:sitama/features/auth/data/models/register_req_params.dart';
import 'package:sitama/features/auth/domain/usecases/register.dart';
import 'package:sitama/features/student/ui/home/pages/home.dart';
import 'package:sitama/service_locator.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final _scaffoldKey = GlobalKey<ScaffoldMessengerState>();
  final _nameController = TextEditingController();
  final _usernameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirm = true;
  bool _isLoading = false;

  @override
  void dispose() {
    _nameController.dispose();
    _usernameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  void _showError(String message) {
    _scaffoldKey.currentState?.showSnackBar(
      CustomSnackBar(
        message: message,
        icon: Icons.error_outline,
        backgroundColor: Colors.red.shade800,
        duration: const Duration(seconds: 3),
        action: SnackBarAction(
          label: 'OK',
          textColor: Colors.white,
          onPressed: () => _scaffoldKey.currentState?.hideCurrentSnackBar(),
        ),
      ),
    );
  }

  Future<void> _register() async {
    final name     = _nameController.text.trim();
    final username = _usernameController.text.trim();
    final email    = _emailController.text.trim();
    final password = _passwordController.text;
    final confirm  = _confirmPasswordController.text;

    if (name.isEmpty || username.isEmpty || email.isEmpty || password.isEmpty || confirm.isEmpty) {
      _showError('Semua field harus diisi');
      return;
    }
    if (password != confirm) {
      _showError('Konfirmasi password tidak cocok');
      return;
    }
    if (password.length < 8) {
      _showError('Password minimal 8 karakter');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final result = await sl<RegisterUseCase>().call(
        param: RegisterReqParams(
          name: name,
          username: username,
          email: email,
          password: password,
          passwordConfirmation: confirm,
        ),
      );

      result.fold(
        (error) => _showError(error.toString()),
        (_) async {
          final prefs = await SharedPreferences.getInstance();
          final role  = prefs.getString('role') ?? '';

          if (!mounted) return;

          if (role == 'Student') {
            Navigator.pushAndRemoveUntil(
              context,
              MaterialPageRoute(builder: (_) => HomePage()),
              (_) => false,
            );
          } else {
            _showError('Registrasi berhasil, silakan login.');
          }
        },
      );
    } catch (e) {
      _showError('Terjadi kesalahan: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ScaffoldMessenger(
      key: _scaffoldKey,
      child: Scaffold(
        body: SingleChildScrollView(
          child: Column(
            children: [
              Container(
                width: double.infinity,
                height: 200,
                color: AppColors.lightPrimary500,
                child: Center(child: Image.asset(AppImages.loginvektor)),
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.arrow_back_ios_rounded, size: 16),
                  ),
                  const Text(
                    'Daftar Akun',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _field('Nama Lengkap', _nameController),
                    const SizedBox(height: 16),
                    _field('NIM', _usernameController,
                        hint: 'Nomor Induk Mahasiswa'),
                    const SizedBox(height: 16),
                    _field('Email', _emailController,
                        keyboardType: TextInputType.emailAddress),
                    const SizedBox(height: 16),
                    _passwordField('Password', _passwordController,
                        _obscurePassword, () {
                      setState(() => _obscurePassword = !_obscurePassword);
                    }),
                    const SizedBox(height: 16),
                    _passwordField(
                        'Konfirmasi Password', _confirmPasswordController,
                        _obscureConfirm, () {
                      setState(() => _obscureConfirm = !_obscureConfirm);
                    }, onSubmit: (_) => _register()),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _register,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.lightPrimary,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: _isLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                      Colors.white),
                                ),
                              )
                            : const Text(
                                'Daftar',
                                style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold),
                              ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Center(
                      child: TextButton(
                        onPressed: () => Navigator.pop(context),
                        child: Text(
                          'Sudah punya akun? Login',
                          style: TextStyle(
                              color: AppColors.lightInfo, fontSize: 12),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _field(String label, TextEditingController controller,
      {String? hint, TextInputType? keyboardType}) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        border: const OutlineInputBorder(
          borderRadius: BorderRadius.all(Radius.circular(12)),
        ),
      ),
    );
  }

  Widget _passwordField(String label, TextEditingController controller,
      bool obscure, VoidCallback onToggle,
      {void Function(String)? onSubmit}) {
    return TextField(
      controller: controller,
      obscureText: obscure,
      onSubmitted: onSubmit,
      decoration: InputDecoration(
        labelText: label,
        border: const OutlineInputBorder(
          borderRadius: BorderRadius.all(Radius.circular(12)),
        ),
        suffixIcon: IconButton(
          icon: Icon(obscure ? Icons.visibility_off : Icons.visibility),
          onPressed: onToggle,
        ),
      ),
    );
  }
}
