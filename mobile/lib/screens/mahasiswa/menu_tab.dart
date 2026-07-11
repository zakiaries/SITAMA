import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../widgets/ui.dart';
import '../../theme/app_theme.dart';
import 'seminar_screen.dart';
import 'nilai_screen.dart';
import 'magang_saya_screen.dart';
import 'ajukan_magang_screen.dart';
import 'laporan_screen.dart';
import 'profile_tab.dart';
import '../shared/notifikasi_screen.dart';

class MenuTab extends StatelessWidget {
  const MenuTab({super.key});

  void _open(BuildContext c, Widget page) =>
      Navigator.push(c, MaterialPageRoute(builder: (_) => page));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(
        children: [
          const AppHeader(title: 'Menu', subtitle: 'Semua fitur & pengaturan akun'),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
              children: [
                const Padding(
                  padding: EdgeInsets.only(left: 2, bottom: 10),
                  child: Text('KAMPUS & INDUSTRI',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted, letterSpacing: .6)),
                ),
                GridView.count(
                  crossAxisCount: 3,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  mainAxisSpacing: 12,
                  crossAxisSpacing: 12,
                  childAspectRatio: 0.86,
                  children: [
                    MenuTile(Icons.event_outlined, 'Seminar', onTap: () => _open(context, const SeminarScreen())),
                    MenuTile(Icons.star_border_rounded, 'Nilai', onTap: () => _open(context, const NilaiScreen())),
                    MenuTile(Icons.business_center_outlined, 'Magang Saya', onTap: () => _open(context, const MagangSayaScreen())),
                    MenuTile(Icons.note_add_outlined, 'Ajukan Magang', onTap: () => _open(context, const AjukanMagangScreen())),
                    MenuTile(Icons.description_outlined, 'Laporan Akhir', onTap: () => _open(context, const LaporanScreen())),
                    MenuTile(Icons.notifications_none_rounded, 'Notifikasi', onTap: () => _open(context, const NotifikasiScreen(basePath: '/mahasiswa'))),
                  ],
                ),
                const SizedBox(height: 20),
                const Padding(
                  padding: EdgeInsets.only(left: 2, bottom: 10),
                  child: Text('AKUN',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted, letterSpacing: .6)),
                ),
                _accountTile(context, Icons.person_outline, 'Profil Saya', () => _open(context, const ProfileTab())),
                _accountTile(context, Icons.help_outline_rounded, 'Bantuan & FAQ', () {}),
                const SizedBox(height: 4),
                _LogoutTile(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _accountTile(BuildContext c, IconData icon, String label, VoidCallback onTap) => Container(
        margin: const EdgeInsets.only(bottom: 9),
        decoration: BoxDecoration(
          color: AppColors.bg,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.borderSubtle),
          boxShadow: kSoftShadow,
        ),
        child: ListTile(
          onTap: onTap,
          leading: Container(
            width: 36, height: 36,
            decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, color: AppColors.primary, size: 18),
          ),
          title: Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
          trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
        ),
      );
}

/// Baris merah "Keluar" (memakai AuthProvider.logout dengan konfirmasi).
class _LogoutTile extends StatelessWidget {
  Future<void> _confirm(BuildContext context) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Keluar?'),
        content: const Text('Anda yakin ingin keluar dari akun ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Keluar')),
        ],
      ),
    );
    if (ok == true && context.mounted) {
      await context.read<AuthProvider>().logout();
    }
  }

  @override
  Widget build(BuildContext context) => Container(
        decoration: BoxDecoration(
          color: AppColors.errorBg,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.errorBg),
        ),
        child: ListTile(
          onTap: () => _confirm(context),
          leading: Container(
            width: 36, height: 36,
            decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(10)),
            child: const Icon(Icons.logout, color: AppColors.error, size: 18),
          ),
          title: const Text('Keluar', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.error)),
        ),
      );
}
