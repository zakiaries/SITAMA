import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

/// Kartu ringkas mahasiswa (dipakai dashboard dosen & pembimbing industri).
class StudentTile extends StatelessWidget {
  final String name, subtitle, status, trailing;
  final VoidCallback? onTap;
  const StudentTile({
    super.key,
    required this.name,
    required this.subtitle,
    required this.status,
    required this.trailing,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    Color bg = AppColors.blueTint, fg = AppColors.primary;
    if (status == 'selesai' || status == 'dinilai') { bg = AppColors.successBg; fg = AppColors.success; }

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor: AppColors.blueTint,
            child: Text(_initials(name),
                style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700, fontSize: 13)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: const TextStyle(fontWeight: FontWeight.w700)),
                const SizedBox(height: 2),
                Text(subtitle, style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                const SizedBox(height: 4),
                Text(trailing, style: const TextStyle(color: AppColors.textSecondary, fontSize: 11)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
            decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(999)),
            child: Text(status, style: TextStyle(color: fg, fontSize: 11, fontWeight: FontWeight.w700)),
          ),
        ],
      ),
      ),
    );
  }

  String _initials(String n) {
    final parts = n.trim().split(RegExp(r'\s+'));
    return parts.take(2).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
  }
}
