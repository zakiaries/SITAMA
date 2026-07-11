import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import 'auth/auth_ui.dart';
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
    return AuthShell(
      badge: const AuthBadge(),
      children: [
        authTitle('Masuk'),
        const SizedBox(height: 18),
        if (_error != null) authErrorBox(_error!),
        TextField(
          controller: _username,
          textInputAction: TextInputAction.next,
          decoration: authField('NIM / NIP', Icons.person_outline),
        ),
        const SizedBox(height: 11),
        TextField(
          controller: _password,
          obscureText: _obscure,
          onSubmitted: (_) => _submit(),
          decoration: authField('Kata sandi', Icons.lock_outline,
              suffix: IconButton(
                icon: Icon(_obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 20),
                onPressed: () => setState(() => _obscure = !_obscure),
              )),
        ),
        Align(
          alignment: Alignment.centerRight,
          child: TextButton(
            onPressed: () => _go(const ForgotPasswordScreen()),
            child: const Text('Lupa kata sandi?'),
          ),
        ),
        const SizedBox(height: 4),
        authPrimaryButton('Masuk', _loading ? null : _submit, loading: _loading),
        const SizedBox(height: 11),
        authSecondaryButton('Daftar', () => _go(const RegisterScreen())),
        const SizedBox(height: 11),
        authSecondaryButton('Aktivasi Pembimbing Industri', () => _go(const ActivationScreen())),
      ],
    );
  }
}
