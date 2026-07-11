import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

const Color _fieldFill = Color(0xFFF7F6F3);
const Color _fieldBorder = Color(0xFFE7E5E1);
const Color _secondaryBorder = Color(0xFFC6D3EA);
const Color _authBg = Color(0xFFF4F2EF);

/// Dekorasi input pill lembut untuk semua layar auth.
InputDecoration authField(String hint, IconData icon, {Widget? suffix}) {
  OutlineInputBorder b(Color c, [double w = 1]) => OutlineInputBorder(
        borderRadius: BorderRadius.circular(30),
        borderSide: BorderSide(color: c, width: w),
      );
  return InputDecoration(
    hintText: hint,
    prefixIcon: Icon(icon, size: 20, color: AppColors.textMuted),
    suffixIcon: suffix,
    filled: true,
    fillColor: _fieldFill,
    isDense: true,
    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
    hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13.5),
    enabledBorder: b(_fieldBorder),
    border: b(_fieldBorder),
    focusedBorder: b(AppColors.primary, 1.4),
    errorBorder: b(AppColors.error),
    focusedErrorBorder: b(AppColors.error, 1.4),
  );
}

/// Badge logo bulat. icon == null → pakai logo.png; selain itu badge ikon biru-muda.
class AuthBadge extends StatelessWidget {
  final IconData? icon;
  const AuthBadge({super.key, this.icon});
  @override
  Widget build(BuildContext context) {
    final Widget inner = icon == null
        ? Image.asset(
            'assets/images/logo.png',
            fit: BoxFit.contain,
            errorBuilder: (_, _, _) => Container(
              color: AppColors.primary,
              child: const Icon(Icons.school_rounded, color: Colors.white, size: 40),
            ),
          )
        : Container(
            color: AppColors.blueTint,
            child: Icon(icon, color: AppColors.primary, size: 38),
          );
    return Container(
      width: 92,
      height: 92,
      decoration: BoxDecoration(
        color: Colors.white,
        shape: BoxShape.circle,
        border: Border.all(color: Colors.white, width: 5),
        boxShadow: [
          BoxShadow(color: Colors.black.withAlpha(38), blurRadius: 26, offset: const Offset(0, 12)),
        ],
      ),
      child: ClipOval(child: inner),
    );
  }
}

/// Kerangka layar auth: latar hangat + blob biru + kartu putih dengan badge menimpa atas.
/// Kartu SELALU di tengah vertikal; kalau isi lebih tinggi dari layar, otomatis bisa di-scroll.
/// Responsif untuk semua ukuran HP.
class AuthShell extends StatelessWidget {
  final Widget badge;
  final List<Widget> children;
  final bool showBack;
  const AuthShell({super.key, required this.badge, required this.children, this.showBack = false});

  static Widget _blob(double s, Color c) =>
      Container(width: s, height: s, decoration: BoxDecoration(color: c, shape: BoxShape.circle));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _authBg,
      body: Stack(
        children: [
          Positioned(right: -60, top: 100, child: _blob(180, AppColors.primary)),
          Positioned(left: -50, bottom: 90, child: _blob(130, const Color(0xFF3A82FF))),
          SafeArea(
            child: LayoutBuilder(
              builder: (context, constraints) {
                return SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                  child: ConstrainedBox(
                    // minHeight = tinggi layar (dikurangi padding) → Column bisa center.
                    constraints: BoxConstraints(minHeight: constraints.maxHeight - 48),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Stack(
                          clipBehavior: Clip.none,
                          alignment: Alignment.topCenter,
                          children: [
                            Container(
                              margin: const EdgeInsets.only(top: 46),
                              padding: const EdgeInsets.fromLTRB(20, 60, 20, 22),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(24),
                                boxShadow: [
                                  BoxShadow(color: Colors.black.withAlpha(28), blurRadius: 44, offset: const Offset(0, 20)),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                mainAxisSize: MainAxisSize.min,
                                children: children,
                              ),
                            ),
                            Positioned(top: 0, child: badge),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          // Tombol kembali dipatok di kiri-atas, tidak ikut ter-center.
          if (showBack)
            Positioned(
              top: 0,
              left: 0,
              child: SafeArea(
                child: Padding(
                  padding: const EdgeInsets.only(left: 8, top: 2),
                  child: TextButton.icon(
                    onPressed: () => Navigator.pop(context),
                    style: TextButton.styleFrom(foregroundColor: AppColors.text),
                    icon: const Icon(Icons.arrow_back, size: 20),
                    label: const Text('Kembali', style: TextStyle(fontWeight: FontWeight.w600)),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

/// Judul + subjudul di dalam kartu.
Widget authTitle(String title, {String? subtitle}) => Column(
      children: [
        Text(title,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 23, fontWeight: FontWeight.w800, letterSpacing: -0.5)),
        if (subtitle != null) ...[
          const SizedBox(height: 6),
          Text(subtitle,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 12.5, color: AppColors.textSecondary, height: 1.4)),
        ],
      ],
    );

Widget authSectionLabel(String text) => Padding(
      padding: const EdgeInsets.only(top: 14, bottom: 8, left: 2),
      child: Align(
        alignment: Alignment.centerLeft,
        child: Text(text.toUpperCase(),
            style: const TextStyle(
                fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.textMuted, letterSpacing: .5)),
      ),
    );

Widget authErrorBox(String message) => Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.errorBg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFF0C4BE)),
      ),
      child: Text(message, style: const TextStyle(color: AppColors.error, fontSize: 13)),
    );

/// Tombol utama biru dengan glow lembut.
Widget authPrimaryButton(String label, VoidCallback? onPressed, {bool loading = false}) => Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(30),
        boxShadow: onPressed == null
            ? null
            : [BoxShadow(color: AppColors.primary.withAlpha(72), blurRadius: 18, offset: const Offset(0, 8))],
      ),
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(50), shape: const StadiumBorder()),
        child: loading
            ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
            : Text(label),
      ),
    );

/// Tombol sekunder bergaris.
Widget authSecondaryButton(String label, VoidCallback onPressed) => OutlinedButton(
      onPressed: onPressed,
      style: OutlinedButton.styleFrom(
        minimumSize: const Size.fromHeight(48),
        backgroundColor: Colors.white,
        foregroundColor: AppColors.text,
        side: const BorderSide(color: _secondaryBorder, width: 1.6),
        shape: const StadiumBorder(),
        textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5),
      ),
      child: Text(label),
    );
