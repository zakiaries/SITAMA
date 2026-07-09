import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

/// Kartu putih dengan border subtle.
class AppCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final VoidCallback? onTap;
  const AppCard({super.key, required this.child, this.padding, this.onTap});
  @override
  Widget build(BuildContext context) {
    final card = Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: padding ?? const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderSubtle),
      ),
      child: child,
    );
    if (onTap == null) return card;
    return InkWell(borderRadius: BorderRadius.circular(12), onTap: onTap, child: card);
  }
}

class SectionTitle extends StatelessWidget {
  final String text;
  const SectionTitle(this.text, {super.key});
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(top: 8, bottom: 10),
        child: Text(text, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
      );
}

class InfoRow extends StatelessWidget {
  final String k, v;
  const InfoRow(this.k, this.v, {super.key});
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 5),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          SizedBox(width: 130, child: Text(k, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))),
          Expanded(child: Text(v, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13))),
        ]),
      );
}

class StatusChip extends StatelessWidget {
  final String status;
  const StatusChip(this.status, {super.key});
  @override
  Widget build(BuildContext context) {
    Color bg = AppColors.warnBg, fg = AppColors.warnText;
    final s = status.toLowerCase();
    if (s == 'approved' || s == 'selesai' || s == 'dinilai') { bg = AppColors.successBg; fg = AppColors.success; }
    if (s == 'rejected') { bg = AppColors.errorBg; fg = AppColors.error; }
    if (s == 'aktif' || s == 'scheduled' || s == 'terdaftar') { bg = AppColors.blueTint; fg = AppColors.primary; }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(999)),
      child: Text(status, style: TextStyle(color: fg, fontSize: 11, fontWeight: FontWeight.w700)),
    );
  }
}

class ErrorRetry extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const ErrorRetry({super.key, required this.message, required this.onRetry});
  @override
  Widget build(BuildContext context) => ListView(
        children: [
          const SizedBox(height: 80),
          const Icon(Icons.cloud_off, size: 40, color: AppColors.textMuted),
          const SizedBox(height: 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Text(message, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.textSecondary)),
          ),
          const SizedBox(height: 16),
          Center(child: OutlinedButton(onPressed: onRetry, child: const Text('Coba lagi'))),
        ],
      );
}

class EmptyState extends StatelessWidget {
  final String message;
  final IconData icon;
  const EmptyState(this.message, {super.key, this.icon = Icons.inbox_outlined});
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(top: 60),
        child: Column(children: [
          Icon(icon, size: 40, color: AppColors.textMuted),
          const SizedBox(height: 10),
          Text(message, style: const TextStyle(color: AppColors.textMuted)),
        ]),
      );
}

void showMessage(BuildContext context, String message, {bool error = false}) {
  ScaffoldMessenger.of(context).showSnackBar(SnackBar(
    content: Text(message),
    backgroundColor: error ? AppColors.error : AppColors.success,
  ));
}

/// Dialog input catatan. Mengembalikan teks saat OK, atau null saat batal.
/// Jika [requiredNote] true, tombol OK nonaktif sampai teks diisi.
Future<String?> showNoteDialog(
  BuildContext context, {
  required String title,
  String initial = '',
  bool requiredNote = false,
  String okLabel = 'Simpan',
  String hint = 'Tulis catatan...',
}) {
  return showDialog<String>(
    context: context,
    builder: (_) => _NoteDialog(title: title, initial: initial, requiredNote: requiredNote, okLabel: okLabel, hint: hint),
  );
}

class _NoteDialog extends StatefulWidget {
  final String title, initial, okLabel, hint;
  final bool requiredNote;
  const _NoteDialog({required this.title, required this.initial, required this.requiredNote, required this.okLabel, required this.hint});
  @override
  State<_NoteDialog> createState() => _NoteDialogState();
}

class _NoteDialogState extends State<_NoteDialog> {
  late final TextEditingController _c = TextEditingController(text: widget.initial);
  @override
  void dispose() { _c.dispose(); super.dispose(); }
  @override
  Widget build(BuildContext context) {
    final canOk = !widget.requiredNote || _c.text.trim().isNotEmpty;
    return AlertDialog(
      title: Text(widget.title),
      content: TextField(
        controller: _c,
        maxLines: 4,
        autofocus: true,
        onChanged: (_) => setState(() {}),
        decoration: InputDecoration(hintText: widget.hint),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
        TextButton(onPressed: canOk ? () => Navigator.pop(context, _c.text.trim()) : null, child: Text(widget.okLabel)),
      ],
    );
  }
}

// ── Statistik ringkas (satu baris) ──
class StatItem {
  final String value;
  final String label;
  final bool accent;
  const StatItem(this.value, this.label, {this.accent = false});
}

class StatStrip extends StatelessWidget {
  final List<StatItem> items;
  const StatStrip(this.items, {super.key});
  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 14),
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: AppColors.bg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.borderSubtle),
        ),
        child: Row(
          children: [
            for (int i = 0; i < items.length; i++) ...[
              if (i > 0)
                const SizedBox(
                    width: 1,
                    height: 34,
                    child: ColoredBox(color: AppColors.borderSubtle)),
              Expanded(
                child: Column(
                  children: [
                    Text(items[i].value,
                        style: TextStyle(
                            fontSize: 19,
                            fontWeight: FontWeight.w800,
                            color: items[i].accent
                                ? AppColors.primary
                                : AppColors.text)),
                    const SizedBox(height: 2),
                    Text(items[i].label,
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                            fontSize: 10, color: AppColors.textSecondary)),
                  ],
                ),
              ),
            ]
          ],
        ),
      );
}

// ── Lingkaran status (leading item) ──
class StatusDot extends StatelessWidget {
  final String kind; // done | pending | rejected | blue
  const StatusDot(this.kind, {super.key});
  @override
  Widget build(BuildContext context) {
    Color bg = const Color(0xFFEFEDE0);
    Color fg = AppColors.textMuted;
    IconData ic = Icons.remove;
    switch (kind) {
      case 'done':
        bg = AppColors.successBg; fg = AppColors.success; ic = Icons.check; break;
      case 'rejected':
        bg = AppColors.errorBg; fg = AppColors.error; ic = Icons.close; break;
      case 'blue':
        bg = AppColors.blueTint; fg = AppColors.primary; ic = Icons.article_outlined; break;
    }
    return Container(
      width: 32, height: 32,
      decoration: BoxDecoration(color: bg, shape: BoxShape.circle),
      child: Icon(ic, size: 16, color: fg),
    );
  }
}

// ── Baris item (kartu list yang bisa diklik) ──
class AppListTile extends StatelessWidget {
  final Widget leading;
  final String title;
  final String subtitle;
  final Widget? trailing;
  final VoidCallback? onTap;
  const AppListTile({
    super.key,
    required this.leading,
    required this.title,
    this.subtitle = '',
    this.trailing,
    this.onTap,
  });
  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 9),
        decoration: BoxDecoration(
          color: AppColors.bg,
          borderRadius: BorderRadius.circular(13),
          border: Border.all(color: AppColors.borderSubtle),
        ),
        child: ListTile(
          onTap: onTap,
          leading: leading,
          title: Text(title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5)),
          subtitle: subtitle.isEmpty
              ? null
              : Text(subtitle,
                  style: const TextStyle(
                      fontSize: 11.5, color: AppColors.textMuted)),
          trailing: trailing,
        ),
      );
}

// ── Kotak menu (grid) — dengan ripple biru & isi tile penuh (tidak geser) ──
class MenuTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback? onTap;
  final int badge;
  const MenuTile(this.icon, this.label, {super.key, this.onTap, this.badge = 0});
  @override
  Widget build(BuildContext context) => Material(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          splashColor: AppColors.primary.withAlpha(30),
          highlightColor: AppColors.blueTint,
          onTap: onTap,
          child: Ink(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.borderSubtle),
            ),
            child: Stack(children: [
              Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 12),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 42, height: 42,
                        decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(12)),
                        child: Icon(icon, color: AppColors.primary, size: 22),
                      ),
                      const SizedBox(height: 8),
                      Text(label, textAlign: TextAlign.center, maxLines: 2,
                          style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, height: 1.15)),
                    ],
                  ),
                ),
              ),
              if (badge > 0)
                Positioned(right: 10, top: 8, child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                  decoration: const BoxDecoration(color: AppColors.error, borderRadius: BorderRadius.all(Radius.circular(9999))),
                  child: Text('$badge', style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w700)))),
            ]),
          ),
        ),
      );
}

// ── Field detail (label kecil + isi) ──
class DetailField extends StatelessWidget {
  final String label;
  final String value;
  const DetailField(this.label, this.value, {super.key});
  @override
  Widget build(BuildContext context) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label.toUpperCase(),
              style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textSecondary,
                  letterSpacing: .3)),
          const SizedBox(height: 3),
          Text(value, style: const TextStyle(fontSize: 14, height: 1.5)),
        ],
      );
}

// ── Blok catatan (garis kiri biru/hijau) ──
class NoteBlock extends StatelessWidget {
  final String label;
  final String value;
  final bool green;
  const NoteBlock(
      {super.key, required this.label, required this.value, this.green = false});
  @override
  Widget build(BuildContext context) {
    final c = green ? AppColors.success : AppColors.primary;
    return Container(
      margin: const EdgeInsets.only(top: 14),
      padding: const EdgeInsets.only(left: 12),
      decoration: BoxDecoration(
          border: Border(left: BorderSide(color: c, width: 3))),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label.toUpperCase(),
              style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: c,
                  letterSpacing: .3)),
          const SizedBox(height: 3),
          Text(value, style: const TextStyle(fontSize: 14, height: 1.5)),
        ],
      ),
    );
  }
}

// ── Kartu nilai besar ──
class NilaiHero extends StatelessWidget {
  final String value;
  const NilaiHero(this.value, {super.key});
  @override
  Widget build(BuildContext context) => Container(
        width: double.infinity,
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.all(26),
        decoration: BoxDecoration(
            color: AppColors.primary, borderRadius: BorderRadius.circular(16)),
        child: Column(
          children: [
            const Text('RATA-RATA NILAI AKHIR',
                style: TextStyle(
                    color: Colors.white70, fontSize: 11, letterSpacing: .5)),
            const SizedBox(height: 6),
            Text(value,
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 46,
                    fontWeight: FontWeight.w800,
                    height: 1)),
          ],
        ),
      );
}

// ── Baris search + tombol filter ──
class SearchFilterBar extends StatelessWidget {
  final ValueChanged<String>? onChanged;
  final VoidCallback? onFilter;
  final String hint;
  const SearchFilterBar(
      {super.key, this.onChanged, this.onFilter, this.hint = 'Pencarian..'});
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: Row(
          children: [
            Expanded(
              child: TextField(
                onChanged: onChanged,
                decoration: InputDecoration(
                  hintText: hint,
                  prefixIcon: const Icon(Icons.search, size: 20),
                  isDense: true,
                ),
              ),
            ),
            const SizedBox(width: 10),
            InkWell(
              onTap: onFilter,
              borderRadius: BorderRadius.circular(11),
              child: Container(
                width: 52, height: 52,
                decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(11),
                    border: Border.all(color: AppColors.border)),
                child: const Icon(Icons.tune, size: 20),
              ),
            ),
          ],
        ),
      );
}

/// Segmented tab bar — pilihan seragam (Bimbingan/LogBook/Laporan, filter, dll).
class SegTabs extends StatelessWidget {
  final List<String> labels;
  final int index;
  final ValueChanged<int> onChanged;
  final bool small;
  const SegTabs({
    super.key,
    required this.labels,
    required this.index,
    required this.onChanged,
    this.small = false,
  });

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(4),
        decoration: BoxDecoration(
          color: AppColors.bg,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.borderSubtle),
        ),
        child: Row(
          children: [
            for (int i = 0; i < labels.length; i++)
              Expanded(
                child: GestureDetector(
                  onTap: () => onChanged(i),
                  child: Container(
                    margin: EdgeInsets.only(left: i == 0 ? 0 : 4),
                    padding: EdgeInsets.symmetric(vertical: small ? 9 : 10, horizontal: 2),
                    decoration: BoxDecoration(
                      color: i == index ? AppColors.primary : Colors.transparent,
                      borderRadius: BorderRadius.circular(9),
                    ),
                    child: Text(
                      labels[i],
                      textAlign: TextAlign.center,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: small ? 11 : 12,
                        fontWeight: FontWeight.w700,
                        color: i == index ? Colors.white : AppColors.textSecondary,
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
      );
}
