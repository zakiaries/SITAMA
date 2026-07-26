import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Tema SIMAMA — biru Dropbox #0061FF, off-white hangat, font Archivo.
class AppColors {
  static const primary = Color(0xFF0061FF);
  static const primaryDark = Color(0xFF0048BD);
  static const text = Color(0xFF1E1919);
  static const textSecondary = Color(0xFF637282);
  static const textMuted = Color(0xFF9EA9B2);
  static const bg = Color(0xFFFFFFFF);
  static const warm = Color(0xFFF7F5F2);
  static const border = Color(0xFFD8D6D3);
  static const borderSubtle = Color(0xFFEDECEA);
  static const blueTint = Color(0xFFEAF1FF);
  static const success = Color(0xFF0AC27D);
  static const successBg = Color(0xFFE0F7EF);
  static const error = Color(0xFFC0392B);
  static const errorBg = Color(0xFFFBEAE8);
  static const warnText = Color(0xFFB9791A);
  static const warnBg = Color(0xFFFDF1DD);
}

class AppTheme {
  static ThemeData get light {
    final base = ThemeData(useMaterial3: true, colorSchemeSeed: AppColors.primary);
    return base.copyWith(
      scaffoldBackgroundColor: AppColors.warm,
      textTheme: GoogleFonts.archivoTextTheme(base.textTheme),
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.bg,
        foregroundColor: AppColors.text,
        elevation: 0,
        scrolledUnderElevation: 0.5,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          minimumSize: const Size.fromHeight(50),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          textStyle: GoogleFonts.archivo(fontWeight: FontWeight.w700, fontSize: 15),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.warm,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.primary, width: 1.6),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: AppColors.bg,
        indicatorColor: AppColors.blueTint,
        labelTextStyle: WidgetStateProperty.resolveWith((s) => TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: s.contains(WidgetState.selected) ? AppColors.primary : AppColors.textMuted,
            )),
        iconTheme: WidgetStateProperty.resolveWith((s) => IconThemeData(
              color: s.contains(WidgetState.selected) ? AppColors.primary : AppColors.textMuted,
            )),
      ),
    );
  }
}
