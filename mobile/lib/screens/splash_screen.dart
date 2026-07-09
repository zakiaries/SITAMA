import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 150, height: 150,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(44),
                boxShadow: [BoxShadow(color: Colors.black.withAlpha(22), blurRadius: 44, offset: const Offset(0, 20))],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(44),
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Image.asset('assets/images/logo.png', fit: BoxFit.contain,
                    errorBuilder: (_, _, _) => Container(
                      decoration: const BoxDecoration(color: AppColors.primary, shape: BoxShape.circle),
                      child: const Icon(Icons.school_rounded, color: Colors.white, size: 60))),
                ),
              ),
            ),
            const SizedBox(height: 26),
            const Text('SITAMA', style: TextStyle(fontSize: 30, fontWeight: FontWeight.w800, letterSpacing: 2)),
            const SizedBox(height: 6),
            const Text('Sistem Informasi Magang', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
            const SizedBox(height: 30),
            const SizedBox(width: 26, height: 26, child: CircularProgressIndicator(strokeWidth: 3)),
          ],
        ),
      ),
    );
  }
}
