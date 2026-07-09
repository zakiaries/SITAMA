import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../theme/app_theme.dart';
import 'auth/register_screen.dart';
import 'auth/activation_screen.dart';
import 'auth/forgot_password_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _username = TextEditingController();
  final _password = TextEditingController();
  bool _obscure = true;
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _username.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() { _loading = true; _error = null; });
    try {
      await context.read<AuthProvider>().login(_username.text.trim(), _password.text);
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } catch (_) {
      setState(() => _error = 'Tidak dapat terhubung ke server. Cek koneksi / base URL.');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _go(Widget page) =>
      Navigator.push(context, MaterialPageRoute(builder: (_) => page));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          Positioned(right: -70, top: 90, child: _blob(220, AppColors.primary)),
          Positioned(left: -56, bottom: 120, child: _blob(160, const Color(0xFF3A82FF))),
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(22, 40, 22, 30),
                child: Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      margin: const EdgeInsets.only(top: 56),
                      padding: const EdgeInsets.fromLTRB(24, 74, 24, 26),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(26),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withAlpha(22), blurRadius: 40, offset: const Offset(0, 18)),
                        ],
                      ),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const Text('Masuk',
                              textAlign: TextAlign.center,
                              style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, letterSpacing: -0.5)),
                          const SizedBox(height: 20),
                          if (_error != null)
                            Container(
                              margin: const EdgeInsets.only(bottom: 14),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: AppColors.errorBg,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFF0C4BE)),
                              ),
                              child: Text(_error!, style: const TextStyle(color: AppColors.error)),
                            ),
                          TextField(
                            controller: _username,
                            textInputAction: TextInputAction.next,
                            decoration: const InputDecoration(
                              hintText: 'NIM / NIP',
                              prefixIcon: Icon(Icons.person_outline),
                            ),
                          ),
                          const SizedBox(height: 13),
                          TextField(
                            controller: _password,
                            obscureText: _obscure,
                            onSubmitted: (_) => _submit(),
                            decoration: InputDecoration(
                              hintText: 'Kata sandi',
                              prefixIcon: const Icon(Icons.lock_outline),
                              suffixIcon: IconButton(
                                icon: Icon(_obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                                onPressed: () => setState(() => _obscure = !_obscure),
                              ),
                            ),
                          ),
                          Align(
                            alignment: Alignment.centerRight,
                            child: TextButton(
                              onPressed: () => _go(const ForgotPasswordScreen()),
                              child: const Text('Lupa kata sandi?'),
                            ),
                          ),
                          const SizedBox(height: 4),
                          ElevatedButton(
                            onPressed: _loading ? null : _submit,
                            style: ElevatedButton.styleFrom(
                              minimumSize: const Size.fromHeight(50),
                              shape: const StadiumBorder(),
                            ),
                            child: _loading
                                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                                : const Text('Masuk'),
                          ),
                          const SizedBox(height: 11),
                          _secondary('Daftar', () => _go(const RegisterScreen())),
                          const SizedBox(height: 11),
                          _secondary('Aktivasi Pembimbing Industri', () => _go(const ActivationScreen())),
                        ],
                      ),
                    ),
                    Positioned(top: 0, left: 0, right: 0, child: Center(child: _logoBadge())),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _secondary(String label, VoidCallback onTap) => OutlinedButton(
        onPressed: onTap,
        style: OutlinedButton.styleFrom(
          minimumSize: const Size.fromHeight(50),
          backgroundColor: Colors.white,
          foregroundColor: AppColors.text,
          side: const BorderSide(color: Color(0xFFC6D3EA), width: 2),
          shape: const StadiumBorder(),
          textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14.5),
        ),
        child: Text(label),
      );

  Widget _blob(double s, Color c) =>
      Container(width: s, height: s, decoration: BoxDecoration(color: c, shape: BoxShape.circle));

  Widget _logoBadge() => Container(
        width: 112,
        height: 112,
        decoration: BoxDecoration(
          color: Colors.white,
          shape: BoxShape.circle,
          border: Border.all(color: Colors.white, width: 6),
          boxShadow: [BoxShadow(color: Colors.black.withAlpha(30), blurRadius: 30, offset: const Offset(0, 14))],
        ),
        child: ClipOval(
          child: Image.asset(
            'assets/images/logo.png',
            fit: BoxFit.contain,
            errorBuilder: (_, _, _) =>
                Container(color: AppColors.primary, child: const Icon(Icons.school_rounded, color: Colors.white, size: 44)),
          ),
        ),
      );
}
