import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/config/assets/app_images.dart';
import 'package:sitama/core/config/themes/app_color.dart';
import 'package:sitama/core/shared/widgets/alert/custom_snackbar.dart';
import 'package:sitama/features/lecturer/ui/home/pages/lecturer_home.dart';
import 'package:sitama/features/lecturer_industry/ui/lecturer_industry_shell.dart';
import 'package:sitama/features/kaprodi/ui/kaprodi_shell.dart';
import 'package:sitama/features/student/ui/home/pages/home.dart';
import 'package:sitama/features/industri/ui/industri_shell.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final GlobalKey<ScaffoldMessengerState> _scaffoldKey =
      GlobalKey<ScaffoldMessengerState>();
  late final TextEditingController _usernameController =
      TextEditingController();
  late final TextEditingController _passwordController =
      TextEditingController();
  bool _obscureText = true;
  String _selectedRole = 'Student'; // Default role
  bool _isLoading = false;

  void _showSuccessSnackbar(String message) {
    final snackBar = CustomSnackBar(
      message: message,
      icon: Icons.check_circle_outline,
      backgroundColor: Colors.green.shade800,
      duration: const Duration(seconds: 2),
    );

    _scaffoldKey.currentState?.showSnackBar(snackBar);
  }

  void _showErrorSnackbar(String message) {
    final snackBar = CustomSnackBar(
      message: message,
      icon: Icons.error_outline,
      backgroundColor: Colors.red.shade800,
      duration: const Duration(seconds: 3),
      action: SnackBarAction(
        label: 'OK',
        textColor: Colors.white,
        onPressed: () {
          _scaffoldKey.currentState?.hideCurrentSnackBar();
        },
      ),
    );

    _scaffoldKey.currentState?.showSnackBar(snackBar);
  }

  bool _validateInputs() {
    // Untuk mock login, buat lebih permissive - minimal ada 1 karakter
    if (_usernameController.text.trim().isEmpty) {
      _usernameController.text = 'student'; // Auto-fill
    }
    if (_passwordController.text.trim().isEmpty) {
      _passwordController.text = '1234'; // Auto-fill
    }
    return true;
  }

  Future<void> _mockLogin() async {
    if (!_validateInputs()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      // Simulasi delay login
      await Future.delayed(const Duration(seconds: 1));

      // Save role to SharedPreferences
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('role', _selectedRole);
      await prefs.setString('username', _usernameController.text);

      _showSuccessSnackbar('Login berhasil!');

      // Navigate berdasarkan role
      if (!mounted) return;

      if (_selectedRole == 'Student') {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => HomePage(),
          ),
        );
      } else if (_selectedRole == 'Lecturer Industry') {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => LecturerIndustryShell(),
          ),
        );
      } else if (_selectedRole == 'Kaprodi') {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => const KaprodiShell(),
          ),
        );
      } else if (_selectedRole == 'Industri') {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => const IndustriShell(),
          ),
        );
      } else {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => LecturerHomePage(),
          ),
        );
      }
    } catch (e) {
      _showErrorSnackbar('Terjadi kesalahan: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
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
              SizedBox(height: 20),
              Row(
                children: [
                  IconButton(
                    onPressed: () {
                      Navigator.pop(context);
                    },
                    icon: Icon(
                      Icons.arrow_back_ios_rounded,
                      size: 16,
                    ),
                  ),
                  Text(
                    'Login',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 24, vertical: 10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Username/NIM/NIP TextField
                    TextField(
                      controller: _usernameController,
                      decoration: InputDecoration(
                        labelText: 'NIM / NIP',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(12)),
                        ),
                      ),
                    ),
                    SizedBox(height: 16),

                    // Password TextField
                    TextField(
                      controller: _passwordController,
                      obscureText: _obscureText,
                      decoration: InputDecoration(
                        labelText: 'Kata sandi',
                        border: const OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(12)),
                        ),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscureText
                                ? Icons.visibility_off
                                : Icons.visibility,
                          ),
                          onPressed: () {
                            setState(() {
                              _obscureText = !_obscureText;
                            });
                          },
                        ),
                      ),
                    ),
                    SizedBox(height: 16),

                    // Role Selection Dropdown
                    DropdownButtonFormField<String>(
                      initialValue: _selectedRole,
                      decoration: InputDecoration(
                        labelText: 'Pilih Role',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(12)),
                        ),
                      ),
                      items: [
                        'Student',
                        'Lecturer',
                        'Lecturer Industry',
                        'Kaprodi',
                        'Industri'
                      ]
                          .map((role) => DropdownMenuItem(
                                value: role,
                                child: Text(role),
                              ))
                          .toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedRole = value ?? 'Student';
                        });
                      },
                    ),
                    SizedBox(height: 16),

                    TextButton(
                      onPressed: () {},
                      child: Text(
                        'Lupa Kata Sandi ?',
                        style: TextStyle(
                          color: AppColors.lightInfo,
                          fontSize: 12,
                        ),
                      ),
                    ),
                    SizedBox(height: 24),

                    // Login Button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _mockLogin,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.lightPrimary,
                          foregroundColor: Colors.white,
                          padding: EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: _isLoading
                            ? SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                      Colors.white),
                                ),
                              )
                            : Text(
                                'Login',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                      ),
                    ),
                  ],
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}
