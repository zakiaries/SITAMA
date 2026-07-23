import 'package:flutter/material.dart';
import 'package:flutter/services.dart' show Clipboard, ClipboardData;
import 'package:provider/provider.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

/// Seminar model sesi-grup (menyamai web).
///
/// Dosen pembimbing membuat sesi dan menetapkan mahasiswa sebagai penyaji.
/// Mahasiswa: isi ketersediaan tanggal (draft) → lihat jadwal final + QR
/// daftar hadir (scheduled) → sesi disahkan dosen (completed).
class SeminarScreen extends StatefulWidget {
  const SeminarScreen({super.key});
  @override
  State<SeminarScreen> createState() => _SeminarScreenState();
}

class _SeminarScreenState extends State<SeminarScreen> {
  List<Map<String, dynamic>> _sessions = [];
  bool _loading = true;
  String? _error;

  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final data = await ApiClient.get('/mahasiswa/seminar', token: _token);
      if (!mounted) return;
      setState(() {
        _sessions = List<Map<String, dynamic>>.from(data['sessions'] ?? []);
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() { _error = '$e'; _loading = false; });
    }
  }

  Future<void> _submitAvailability(int id, String dates) async {
    try {
      await ApiClient.post('/mahasiswa/seminar/$id/availability',
          token: _token, body: {'available_dates': dates});
      if (mounted) showMessage(context, 'Ketersediaan tanggalmu tersimpan.');
      _load();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    }
  }

  /// Buka PDF berita acara di browser HP (URL sudah bertanda-tangan/signed).
  Future<void> _openBeritaAcara(String url) async {
    final uri = Uri.parse(url);
    final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!ok && mounted) showMessage(context, 'Tidak dapat membuka berita acara.', error: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Seminar', subtitle: 'Sesi seminar hasil magang'),
        Expanded(child: _body()),
      ]),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) return ErrorRetry(message: _error!, onRetry: _load);

    return RefreshIndicator(
      onRefresh: _load,
      child: _sessions.isEmpty
          ? ListView(children: const [
              SizedBox(height: 40),
              EmptyState(
                'Belum ada sesi seminar untukmu',
                icon: Icons.event_outlined,
                hint: 'Dosen pembimbing akan membuat sesi setelah magangmu ditandai '
                    'selesai — kamu akan dapat notifikasi untuk mengisi ketersediaan tanggal.',
              ),
            ])
          : ListView(
              padding: const EdgeInsets.all(16),
              children: _sessions.map(_card).toList(),
            ),
    );
  }

  Widget _card(Map<String, dynamic> s) {
    final status = '${s['status'] ?? ''}';
    return AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('${s['title'] ?? '-'}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14.5)),
          const SizedBox(height: 2),
          Text('Dosen: ${s['lecturer_name'] ?? '-'}',
              style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
        ])),
        const SizedBox(width: 8),
        _statusBadge(status),
      ]),
      const SizedBox(height: 12),
      if (status == 'draft') _draftSection(s)
      else if (status == 'scheduled') _scheduledSection(s)
      else if (status == 'completed') _completedSection(s)
      else if (status == 'cancelled')
        const Text('Sesi ini dibatalkan oleh dosen.',
            style: TextStyle(fontSize: 12.5, color: AppColors.error)),
    ]));
  }

  Widget _statusBadge(String status) {
    String label; Color bg, fg;
    switch (status) {
      case 'draft':     label = 'Menunggu Jadwal'; bg = AppColors.warnBg; fg = AppColors.warnText;
      case 'scheduled': label = 'Terjadwal'; bg = AppColors.blueTint; fg = AppColors.primary;
      case 'completed': label = 'Selesai'; bg = AppColors.successBg; fg = AppColors.success;
      case 'cancelled': label = 'Dibatalkan'; bg = AppColors.errorBg; fg = AppColors.error;
      default:          label = status; bg = AppColors.warm; fg = AppColors.textSecondary;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(999)),
      child: Text(label, style: TextStyle(color: fg, fontSize: 11, fontWeight: FontWeight.w700)),
    );
  }

  /// Draft: isi/perbarui ketersediaan tanggal.
  Widget _draftSection(Map<String, dynamic> s) {
    final responded = (s['responded_at'] ?? '').toString().isNotEmpty;
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const Divider(height: 1),
      const SizedBox(height: 10),
      const Text('Tanggal yang kamu bisa',
          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
      const SizedBox(height: 6),
      _AvailabilityField(
        initial: '${s['available_dates'] ?? ''}',
        buttonLabel: responded ? 'Perbarui' : 'Kirim',
        onSubmit: (v) => _submitAvailability(s['id'] as int, v),
      ),
      const SizedBox(height: 6),
      Text(
        responded
            ? '✓ Terkirim — dosen akan menetapkan tanggal final.'
            : 'Isi tanggal yang kamu bisa; dosen akan memilih tanggal final dari ketersediaan semua penyaji.',
        style: TextStyle(fontSize: 11.5, color: responded ? AppColors.success : AppColors.textMuted),
      ),
    ]);
  }

  /// Scheduled: jadwal final + QR daftar hadir + berita acara.
  Widget _scheduledSection(Map<String, dynamic> s) {
    final guest = s['guest_count'] ?? 0;
    final minGuest = s['min_guests'] ?? 0;
    final met = guest is num && minGuest is num && guest >= minGuest;
    final hadirUrl = (s['hadir_url'] ?? '').toString();
    final beritaAcaraUrl = (s['berita_acara_url'] ?? '').toString();
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const Divider(height: 1),
      const SizedBox(height: 10),
      Wrap(spacing: 18, runSpacing: 8, children: [
        _meta('Tanggal', '${s['date'] ?? '-'}'),
        _meta('Waktu', '${s['time'] ?? '-'}'),
        _meta('Ruang', '${s['location'] ?? '-'}'),
        _meta('Audiens', '$guest/$minGuest', color: met ? AppColors.success : AppColors.warnText),
      ]),
      if (hadirUrl.isNotEmpty) ...[
        const SizedBox(height: 12),
        SizedBox(width: double.infinity, child: ElevatedButton.icon(
          onPressed: () => _showQr(s),
          style: ElevatedButton.styleFrom(minimumSize: const Size(0, 44)),
          icon: const Icon(Icons.qr_code_2, size: 20),
          label: const Text('QR Daftar Hadir'),
        )),
      ],
      if (beritaAcaraUrl.isNotEmpty) ...[
        const SizedBox(height: 8),
        SizedBox(width: double.infinity, child: OutlinedButton.icon(
          onPressed: () => _openBeritaAcara(beritaAcaraUrl),
          style: OutlinedButton.styleFrom(minimumSize: const Size(0, 44)),
          icon: const Icon(Icons.download_rounded, size: 18),
          label: const Text('Unduh Berita Acara (PDF)'),
        )),
      ],
    ]);
  }

  /// Completed: info pengesahan + berita acara.
  Widget _completedSection(Map<String, dynamic> s) {
    final beritaAcaraUrl = (s['berita_acara_url'] ?? '').toString();
    final witnessed = (s['witnessed_at'] ?? '-').toString();
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const Divider(height: 1),
      const SizedBox(height: 10),
      Text('✓ Seminar telah disahkan dosen pada $witnessed. Audiens hadir: ${s['guest_count'] ?? 0}.',
          style: const TextStyle(fontSize: 12.5, color: AppColors.success)),
      if (beritaAcaraUrl.isNotEmpty) ...[
        const SizedBox(height: 10),
        SizedBox(width: double.infinity, child: OutlinedButton.icon(
          onPressed: () => _openBeritaAcara(beritaAcaraUrl),
          style: OutlinedButton.styleFrom(minimumSize: const Size(0, 44)),
          icon: const Icon(Icons.download_rounded, size: 18),
          label: const Text('Unduh Berita Acara (PDF)'),
        )),
      ],
    ]);
  }

  Widget _meta(String k, String v, {Color? color}) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(k, style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
          Text(v, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: color)),
        ],
      );

  /// Dialog QR daftar hadir: audiens memindai lalu LOGIN untuk absen
  /// (1 akun = 1 kehadiran, anti-manipulasi — sama seperti web).
  void _showQr(Map<String, dynamic> s) {
    final url = (s['hadir_url'] ?? '').toString();
    final guest = s['guest_count'] ?? 0;
    final minGuest = s['min_guests'] ?? 0;
    showDialog(context: context, builder: (c) => AlertDialog(
      title: const Text('QR Daftar Hadir'),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        const Text('Audiens memindai QR ini, lalu login SITAMA untuk mengisi daftar hadir (1 akun = 1 kehadiran).',
            style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
        const SizedBox(height: 14),
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppColors.border)),
          child: QrImageView(data: url, size: 220, version: QrVersions.auto),
        ),
        const SizedBox(height: 10),
        Text('Audiens hadir: $guest/$minGuest',
            style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
      ]),
      actions: [
        TextButton(
          onPressed: () async {
            await Clipboard.setData(ClipboardData(text: url));
            if (c.mounted) Navigator.pop(c);
          },
          child: const Text('Salin Tautan'),
        ),
        TextButton(onPressed: () => Navigator.pop(c), child: const Text('Tutup')),
      ],
    ));
  }
}

/// Input ketersediaan dengan tombol kirim di sisi kanan.
class _AvailabilityField extends StatefulWidget {
  final String initial;
  final String buttonLabel;
  final ValueChanged<String> onSubmit;
  const _AvailabilityField({required this.initial, required this.buttonLabel, required this.onSubmit});
  @override
  State<_AvailabilityField> createState() => _AvailabilityFieldState();
}

class _AvailabilityFieldState extends State<_AvailabilityField> {
  late final TextEditingController _c = TextEditingController(text: widget.initial);

  @override
  void dispose() { _c.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      Expanded(child: TextField(
        controller: _c,
        decoration: const InputDecoration(
          hintText: 'Contoh: 12, 15, atau 18 Agustus 2026',
          isDense: true,
        ),
      )),
      const SizedBox(width: 8),
      ElevatedButton(
        onPressed: () {
          final v = _c.text.trim();
          if (v.isEmpty) { showMessage(context, 'Isi tanggal yang kamu bisa.', error: true); return; }
          widget.onSubmit(v);
        },
        child: Text(widget.buttonLabel),
      ),
    ]);
  }
}
