import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

/// Bayangan lembut ala Dropbox.
const List<BoxShadow> kSoftShadow = [
  BoxShadow(color: Color(0x0F1E1E2D), blurRadius: 16, offset: Offset(0, 6)),
];

/// Petakan status → jenis StatusDot.
String statusDotKind(String status) {
  final s = status.toLowerCase();
  if (s == 'approved' || s == 'selesai' || s == 'dinilai') return 'done';
  if (s == 'rejected') return 'rejected';
  if (s == 'aktif' || s == 'scheduled' || s == 'terdaftar') return 'blue';
  return 'pending';
}

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
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.borderSubtle),
        boxShadow: kSoftShadow,
      ),
      child: child,
    );
    if (onTap == null) return card;
    return InkWell(borderRadius: BorderRadius.circular(14), onTap: onTap, child: card);
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
  final String? hint;
  const EmptyState(this.message, {super.key, this.icon = Icons.inbox_outlined, this.hint});
  @override
  Widget build(BuildContext context) {
    final content = Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 78, height: 78,
          decoration: const BoxDecoration(color: AppColors.blueTint, shape: BoxShape.circle),
          child: Icon(icon, size: 34, color: AppColors.primary),
        ),
        const SizedBox(height: 16),
        Text(message, textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700, color: AppColors.text)),
        if (hint != null) ...[
          const SizedBox(height: 5),
          Text(hint!, textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 12.5, color: AppColors.textMuted, height: 1.4)),
        ],
      ],
    );
    return LayoutBuilder(builder: (ctx, cons) {
      if (cons.maxHeight.isFinite) {
        return Center(child: Padding(padding: const EdgeInsets.symmetric(horizontal: 32), child: content));
      }
      return Padding(padding: const EdgeInsets.fromLTRB(32, 48, 32, 36), child: content);
    });
  }
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

// ── Avatar user: foto profil bila ada, kalau tidak inisial namanya ──
/// Padanan komponen <x-avatar> di web. Dipakai di SEMUA tempat yang
/// menampilkan orang lain (daftar & detail mahasiswa), supaya foto profil
/// tak cuma terlihat di halaman profil sendiri.
class UserAvatar extends StatelessWidget {
  final String? name;
  final String? photoUrl;
  final double radius;
  final Color? background;
  final Color? foreground;

  const UserAvatar({
    super.key,
    this.name,
    this.photoUrl,
    this.radius = 22,
    this.background,
    this.foreground,
  });

  @override
  Widget build(BuildContext context) {
    final bg = background ?? AppColors.blueTint;
    final fg = foreground ?? AppColors.primary;
    final url = photoUrl;

    return CircleAvatar(
      radius: radius,
      backgroundColor: bg,
      // Kalau foto gagal dimuat (offline / file hilang), inisial di bawahnya
      // tetap terlihat — tidak berubah jadi ikon rusak.
      backgroundImage: (url != null && url.isNotEmpty) ? NetworkImage(url) : null,
      child: (url != null && url.isNotEmpty)
          ? null
          : Text(
              _initials(name ?? ''),
              style: TextStyle(color: fg, fontWeight: FontWeight.w700, fontSize: radius * 0.6),
            ),
    );
  }

  static String _initials(String n) {
    final parts = n.trim().split(RegExp(r'\s+')).where((w) => w.isNotEmpty).toList();
    if (parts.isEmpty) return '?';
    return parts.take(2).map((w) => w[0].toUpperCase()).join();
  }
}

// ── Lencana angka merah (penanda "menunggu tanggapan") ──
class CountBadge extends StatelessWidget {
  final int count;
  const CountBadge(this.count, {super.key});

  @override
  Widget build(BuildContext context) {
    if (count <= 0) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: AppColors.error,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        count > 99 ? '99+' : '$count',
        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
      ),
    );
  }
}

// ── Titik merah kecil (penanda ada yang baru) ──
class NewDot extends StatelessWidget {
  const NewDot({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 9,
      height: 9,
      decoration: const BoxDecoration(color: AppColors.error, shape: BoxShape.circle),
    );
  }
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
          boxShadow: kSoftShadow,
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
              color: AppColors.bg,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.borderSubtle),
              boxShadow: kSoftShadow,
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

/// Header band biru (judul + subjudul + aksi kanan + slot bawah untuk search).
class AppHeader extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Widget? trailing;
  final Widget? bottom;
  const AppHeader({super.key, required this.title, this.subtitle, this.trailing, this.bottom});
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(18, MediaQuery.of(context).padding.top + 16, 18, 16),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppColors.primary, Color(0xFF2F78FF)],
          begin: Alignment.topLeft, end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: const TextStyle(color: Colors.white, fontSize: 21, fontWeight: FontWeight.w800)),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(subtitle!, style: const TextStyle(color: Colors.white70, fontSize: 12)),
                    ],
                  ],
                ),
              ),
              ?trailing,
            ],
          ),
          if (bottom != null) ...[const SizedBox(height: 14), bottom!],
        ],
      ),
    );
  }
}

/// Tombol lingkaran putih semi-transparan untuk aksi di header (mis. tambah).
class HeaderAction extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  const HeaderAction(this.icon, this.onTap, {super.key});
  @override
  Widget build(BuildContext context) => Material(
        color: Colors.white.withAlpha(46),
        shape: const CircleBorder(),
        child: InkWell(
          customBorder: const CircleBorder(),
          onTap: onTap,
          child: SizedBox(width: 38, height: 38, child: Icon(icon, color: Colors.white, size: 20)),
        ),
      );
}

/// Kolom pencarian putih untuk slot bawah header biru.
Widget headerSearch({required String hint, ValueChanged<String>? onChanged}) {
  return TextField(
    onChanged: onChanged,
    decoration: InputDecoration(
      hintText: hint,
      prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.textMuted),
      filled: true,
      fillColor: Colors.white,
      isDense: true,
      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
      hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
    ),
  );
}

/// Kartu statistik dengan badge ikon berwarna.
class StatCard extends StatelessWidget {
  final IconData icon;
  final Color bg;
  final Color fg;
  final String value;
  final String label;
  const StatCard({super.key, required this.icon, required this.bg, required this.fg, required this.value, required this.label});
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.bg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.borderSubtle),
          boxShadow: kSoftShadow,
        ),
        child: Row(
          children: [
            Container(
              width: 36, height: 36,
              decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(10)),
              child: Icon(icon, color: fg, size: 18),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, height: 1.1)),
                Text(label, style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary)),
              ],
            ),
          ],
        ),
      );
}

/// Header band biru untuk layar detail (dengan tombol kembali opsional).
class DetailHeader extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Widget? trailing;
  final bool showBack;
  const DetailHeader({super.key, required this.title, this.subtitle, this.trailing, this.showBack = true});
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(showBack ? 6 : 18, MediaQuery.of(context).padding.top + (showBack ? 6 : 16), 14, 20),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppColors.primary, Color(0xFF2F78FF)],
          begin: Alignment.topLeft, end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: Row(
        children: [
          if (showBack)
            IconButton(
              icon: const Icon(Icons.arrow_back, color: Colors.white),
              onPressed: () => Navigator.maybePop(context),
            ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800)),
                if (subtitle != null) ...[
                  const SizedBox(height: 2),
                  Text(subtitle!, style: const TextStyle(color: Colors.white70, fontSize: 11.5)),
                ],
              ],
            ),
          ),
          ?trailing,
          const SizedBox(width: 4),
        ],
      ),
    );
  }
}
