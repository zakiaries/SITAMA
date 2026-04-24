import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/config/assets/app_images.dart';
import 'package:sitama/core/config/themes/app_color.dart';
import 'package:sitama/core/shared/widgets/alert/custom_snackbar.dart';
import 'package:sitama/features/auth/data/models/signin_req_params.dart';
import 'package:sitama/features/auth/domain/usecases/signin.dart';
import 'package:sitama/features/industri/ui/industri_shell.dart';
import 'package:sitama/features/kaprodi/ui/kaprodi_shell.dart';
import 'package:sitama/features/lecturer/ui/home/pages/lecturer_home.dart';
import 'package:sitama/features/lecturer_industry/ui/lecturer_industry_shell.dart';
import 'package:sitama/features/student/ui/home/pages/home.dart';
import 'package:sitama/service_locator.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final GlobalKey<ScaffoldMessengerState> _scaffoldKey =
      GlobalKey<ScaffoldMessengerState>();
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _obscureText = true;
  bool _isLoading = false;

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _showErrorSnackbar(String message) {
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

  Future<void> _login() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text;

    if (username.isEmpty || password.isEmpty) {
      _showErrorSnackbar('Username dan password tidak boleh kosong');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final result = await sl<SigninUseCase>().call(
        param: SigninReqParams(username: username, password: password),
      );

      result.fold(
        (error) => _showErrorSnackbar(error.toString()),
        (_) async {
          final prefs = await SharedPreferences.getInstance();
          final role = prefs.getString('role') ?? '';

          if (!mounted) return;

          switch (role) {
            case 'Student':
              Navigator.pushReplacement(context,
                  MaterialPageRoute(builder: (_) => HomePage()));
            case 'Lecturer':
              Navigator.pushReplacement(context,
                  MaterialPageRoute(builder: (_) => LecturerHomePage()));
            case 'Lecturer Industry':
              Navigator.pushReplacement(context,
                  MaterialPageRoute(builder: (_) => LecturerIndustryShell()));
            case 'Kaprodi':
              Navigator.pushReplacement(context,
                  MaterialPageRoute(builder: (_) => const KaprodiShell()));
            case 'Industri':
              Navigator.pushReplacement(context,
                  MaterialPageRoute(builder: (_) => const IndustriShell()));
            default:
              _showErrorSnackbar('Role tidak dikenali: $role');
          }
        },
      );
    } catch (e) {
      _showErrorSnackbar('Terjadi kesalahan: $e');
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
                height: 312,
                color: AppColors.lightPrimary500,
                child: Center(
                  child: Image.asset(AppImages.loginvektor),
                ),
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.arrow_back_ios_rounded, size: 16),
                  ),
                  const Text(
                    'Login',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 24, vertical: 10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    TextField(
                      controller: _usernameController,
                      decoration: const InputDecoration(
                        labelText: 'NIM / NIP',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(12)),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _passwordController,
                      obscureText: _obscureText,
                      onSubmitted: (_) => _login(),
                      decoration: InputDecoration(
                        labelText: 'Kata sandi',
                        border: const OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(12)),
                        ),
                        suffixIcon: IconButton(
                          icon: Icon(_obscureText
                              ? Icons.visibility_off
                              : Icons.visibility),
                          onPressed: () =>
                              setState(() => _obscureText = !_obscureText),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextButton(
                      onPressed: () {},
                      child: Text(
                        'Lupa Kata Sandi ?',
                        style: TextStyle(
                            color: AppColors.lightInfo, fontSize: 12),
                      ),
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _login,
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
                                'Login',
                                style: TextStyle(
                                    fontSize: 16, fontWeight: FontWeight.bold),
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
}
