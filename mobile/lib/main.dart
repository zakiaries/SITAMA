import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'theme/app_theme.dart';
import 'screens/login_screen.dart';
import 'screens/splash_screen.dart';
import 'screens/home/mahasiswa_home.dart';
import 'screens/home/dosen_home.dart';
import 'screens/home/industri_home.dart';

void main() {
  runApp(const SitamaApp());
}

class SitamaApp extends StatelessWidget {
  const SitamaApp({super.key});
  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AuthProvider()..bootstrap(),
      child: MaterialApp(
        title: 'SIMAMA',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: const _SplashRoot(),
      ),
    );
  }
}

class _SplashRoot extends StatefulWidget {
  const _SplashRoot();
  @override
  State<_SplashRoot> createState() => _SplashRootState();
}

class _SplashRootState extends State<_SplashRoot> {
  bool _ready = false;
  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 2000), () {
      if (mounted) setState(() => _ready = true);
    });
  }
  @override
  Widget build(BuildContext context) => _ready ? const AuthGate() : const SplashScreen();
}

/// Menentukan layar berdasarkan status login + role.
class AuthGate extends StatelessWidget {
  const AuthGate({super.key});
  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    switch (auth.status) {
      case AuthStatus.unknown:
        return const SplashScreen();
      case AuthStatus.unauthenticated:
        return const LoginScreen();
      case AuthStatus.authenticated:
        switch (auth.role) {
          case 'student':
            return const MahasiswaHome();
          case 'lecturer':
            return const DosenHome();
          case 'lecturer_industry':
            return const IndustriHome();
          default:
            return const _UnsupportedRole();
        }
    }
  }
}

class _UnsupportedRole extends StatelessWidget {
  const _UnsupportedRole();
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(Icons.desktop_windows_outlined, size: 48, color: AppColors.textMuted),
            const SizedBox(height: 14),
            const Text('Peran ini tidak didukung di aplikasi mobile.',
                textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w700)),
            const SizedBox(height: 6),
            const Text('Silakan gunakan versi web.',
                textAlign: TextAlign.center, style: TextStyle(color: AppColors.textSecondary)),
            const SizedBox(height: 18),
            TextButton(
              onPressed: () => context.read<AuthProvider>().logout(),
              child: const Text('Keluar'),
            ),
          ]),
        ),
      ),
    );
  }
}
