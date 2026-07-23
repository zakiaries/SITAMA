import 'package:flutter/material.dart';
import 'package:flutter/services.dart' show Clipboard, ClipboardData;
import 'package:provider/provider.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

/// "Seminar Bimbingan" — sesi seminar dari sisi dosen pembimbing (menyamai web).
///
/// Alur: buat sesi (draft) berisi mahasiswa bimbingan yang sudah selesai magang
/// → mahasiswa isi ketersediaan → tetapkan jadwal final (scheduled) → audiens
/// absen via login (min. sesuai ketentuan) → sahkan sesi (completed).
class DosenSeminarTab extends StatefulWidget {
  const DosenSeminarTab({super.key});
  @override
  State<DosenSeminarTab> createState() => _DosenSeminarTabState();
}

class _DosenSeminarTabState extends State<DosenSeminarTab> {
  List<Map<String, dynamic>> _sessions = [];
  List<Map<String, dynamic>> _eligible = [];
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
      final data = await ApiClient.get('/dosen/seminar', token: _token);
      if (!mounted) return;
      setState(() {
        _sessions = List<Map<String, dynamic>>.from(data['sessions'] ?? []);
        _eligible = List<Map<String, dynamic>>.from(data['eligible_students'] ?? []);
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() { _error = '$e'; _loading = false; });
    }
  }

  Future<void> _act(Future<dynamic> Function() action) async {
    try {
      await action();
      _load();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const AppHeader(title: 'Seminar Bimbingan', subtitle: 'Sesi seminar mahasiswa bimbingan'),
        Expanded(child: _body()),
      ]),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) return ErrorRetry(message: _error!, onRetry: _load);

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          ElevatedButton.icon(
            onPressed: _eligible.isEmpty ? null : _openCreate,
            icon: const Icon(Icons.add),
            label: Text(_eligible.isEmpty
                ? 'Belum ada mahasiswa siap seminar'
                : 'Buat Sesi Seminar (${_eligible.length} mahasiswa siap)'),
          ),
          if (_eligible.isEmpty)
            const Padding(
              padding: EdgeInsets.only(top: 6),
              child: Text(
                'Sesi bisa dibuat untuk mahasiswa bimbingan yang magangnya sudah selesai dan belum masuk sesi aktif.',
                style: TextStyle(fontSize: 11.5, color: AppColors.textMuted),
              ),
            ),
          const SectionTitle('Sesi Seminar'),
          if (_sessions.isEmpty)
            const AppCard(child: Text('Belum ada sesi seminar.', style: TextStyle(color: AppColors.textMuted)))
          else
            ..._sessions.map(_card),
        ],
      ),
    );
  }

  // ── Buat sesi ──────────────────────────────────────────────────────────────

  void _openCreate() {
    showDialog(context: context, builder: (_) => _CreateSessionDialog(
      eligible: _eligible,
      onSubmit: (title, ids) => _act(() async {
        await ApiClient.post('/dosen/seminar', token: _token, body: {
          'title': title,
          'student_ids': ids, // body JSON → Laravel menerimanya sebagai array
        });
        if (mounted) showMessage(context, 'Sesi dibuat. Mahasiswa diminta mengisi ketersediaan.');
      }),
    ));
  }

  // ── Kartu sesi ─────────────────────────────────────────────────────────────

  Widget _card(Map<String, dynamic> s) {
    final status = '${s['status'] ?? ''}';
    final presenters = List<Map<String, dynamic>>.from(s['presenters'] ?? []);
    return AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Expanded(child: Text('${s['title'] ?? '-'}',
            style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14.5))),
        const SizedBox(width: 8),
        _statusBadge(status),
      ]),
      const SizedBox(height: 8),
      _presenterList(presenters, showAvailability: status == 'draft'),
      const SizedBox(height: 10),
      if (status == 'draft') _draftActions(s)
      else if (status == 'scheduled') _scheduledSection(s)
      else if (status == 'completed')
        Text('✓ Disahkan pada ${s['witnessed_at'] ?? '-'} · Audiens: ${s['guest_count'] ?? 0}',
            style: const TextStyle(fontSize: 12.5, color: AppColors.success)),
    ]));
  }

  Widget _statusBadge(String status) {
    String label; Color bg, fg;
    switch (status) {
      case 'draft':     label = 'Draft'; bg = AppColors.warnBg; fg = AppColors.warnText;
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

  Widget _presenterList(List<Map<String, dynamic>> presenters, {required bool showAvailability}) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const Text('PENYAJI', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.textMuted, letterSpacing: .5)),
      const SizedBox(height: 4),
      ...presenters.map((p) {
        final avail = '${p['available_dates'] ?? ''}';
        final responded = (p['responded_at'] ?? '').toString().isNotEmpty;
        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 2),
          child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('• ', style: TextStyle(fontSize: 12.5)),
            Expanded(child: Text.rich(TextSpan(
              text: '${p['name'] ?? '-'}',
              style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600),
              children: [
                if (showAvailability)
                  TextSpan(
                    text: responded ? '  —  bisa: $avail' : '  —  belum mengisi ketersediaan',
                    style: TextStyle(
                      fontWeight: FontWeight.w400,
                      color: responded ? AppColors.textSecondary : AppColors.warnText,
                    ),
                  ),
              ],
            ))),
          ]),
        );
      }),
    ]);
  }

  // ── Aksi per status ────────────────────────────────────────────────────────

  Widget _draftActions(Map<String, dynamic> s) {
    return Row(children: [
      Expanded(child: ElevatedButton(
        onPressed: () => _openFinalize(s),
        child: const Text('Tetapkan Jadwal'),
      )),
      const SizedBox(width: 8),
      Expanded(child: OutlinedButton(
        style: OutlinedButton.styleFrom(foregroundColor: AppColors.error, side: const BorderSide(color: AppColors.error)),
        onPressed: () => _confirmCancel(s),
        child: const Text('Batalkan'),
      )),
    ]);
  }

  Widget _scheduledSection(Map<String, dynamic> s) {
    final guest = s['guest_count'] ?? 0;
    final minGuest = s['min_guests'] ?? 0;
    final met = guest is num && minGuest is num && guest >= minGuest;
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Wrap(spacing: 18, runSpacing: 8, children: [
        _meta('Tanggal', '${s['date'] ?? '-'}'),
        _meta('Waktu', '${s['time'] ?? '-'}'),
        _meta('Ruang', '${s['location'] ?? '-'}'),
        _meta('Audiens', '$guest/$minGuest', color: met ? AppColors.success : AppColors.warnText),
      ]),
      const SizedBox(height: 10),
      Row(children: [
        Expanded(child: OutlinedButton.icon(
          onPressed: () => _showQr(s),
          icon: const Icon(Icons.qr_code_2, size: 18),
          label: const Text('QR Hadir'),
        )),
        const SizedBox(width: 8),
        Expanded(child: ElevatedButton(
          onPressed: met ? () => _confirmSahkan(s) : null,
          child: const Text('Sahkan'),
        )),
      ]),
      const SizedBox(height: 6),
      Row(children: [
        Expanded(child: OutlinedButton(
          onPressed: () => _openFinalize(s),
          child: const Text('Ubah Jadwal'),
        )),
        const SizedBox(width: 8),
        Expanded(child: OutlinedButton(
          style: OutlinedButton.styleFrom(foregroundColor: AppColors.error, side: const BorderSide(color: AppColors.error)),
          onPressed: () => _confirmCancel(s),
          child: const Text('Batalkan'),
        )),
      ]),
      if (!met)
        Padding(
          padding: const EdgeInsets.only(top: 6),
          child: Text('Butuh minimal $minGuest audiens sebelum bisa disahkan.',
              style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
        ),
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

  void _openFinalize(Map<String, dynamic> s) {
    showDialog(context: context, builder: (_) => _FinalizeDialog(
      initialDate: '${s['date'] ?? ''}',
      initialTime: '${s['time'] ?? ''}',
      initialLocation: '${s['location'] ?? ''}',
      onSubmit: (date, time, location) => _act(() async {
        await ApiClient.post('/dosen/seminar/${s['id']}/finalize', token: _token, body: {
          'date': date,
          if (time.isNotEmpty) 'time': time,
          'location': location,
        });
        if (mounted) showMessage(context, 'Jadwal ditetapkan. Penyaji diberi tahu.');
      }),
    ));
  }

  Future<void> _confirmSahkan(Map<String, dynamic> s) async {
    final ok = await showDialog<bool>(context: context, builder: (c) => AlertDialog(
      title: const Text('Sahkan seminar?'),
      content: const Text('Anda mengesahkan sebagai saksi bahwa sesi ini telah berlangsung dan selesai.'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
        TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Ya, sahkan')),
      ],
    ));
    if (ok == true) {
      await _act(() async {
        await ApiClient.post('/dosen/seminar/${s['id']}/sahkan', token: _token);
        if (mounted) showMessage(context, 'Seminar disahkan selesai.');
      });
    }
  }

  Future<void> _confirmCancel(Map<String, dynamic> s) async {
    final ok = await showDialog<bool>(context: context, builder: (c) => AlertDialog(
      title: const Text('Batalkan sesi seminar?'),
      content: const Text('Sesi beserta data penyaji & daftar hadirnya akan dihapus.'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Tidak')),
        TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Ya, batalkan')),
      ],
    ));
    if (ok == true) {
      await _act(() async {
        await ApiClient.delete('/dosen/seminar/${s['id']}', token: _token);
        if (mounted) showMessage(context, 'Sesi seminar dibatalkan.');
      });
    }
  }

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

// ── Dialog buat sesi ─────────────────────────────────────────────────────────

class _CreateSessionDialog extends StatefulWidget {
  final List<Map<String, dynamic>> eligible;
  final void Function(String title, List<int> studentIds) onSubmit;
  const _CreateSessionDialog({required this.eligible, required this.onSubmit});
  @override
  State<_CreateSessionDialog> createState() => _CreateSessionDialogState();
}

class _CreateSessionDialogState extends State<_CreateSessionDialog> {
  final _title = TextEditingController();
  final Set<int> _selected = {};

  @override
  void dispose() { _title.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Buat Sesi Seminar'),
      content: SizedBox(
        width: double.maxFinite,
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          TextField(
            controller: _title,
            decoration: const InputDecoration(labelText: 'Judul Sesi *', hintText: 'mis. Seminar Hasil Magang Batch 1'),
          ),
          const SizedBox(height: 12),
          const Align(alignment: Alignment.centerLeft,
              child: Text('Pilih mahasiswa penyaji:', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600))),
          const SizedBox(height: 4),
          Flexible(child: SingleChildScrollView(child: Column(
            mainAxisSize: MainAxisSize.min,
            children: widget.eligible.map((st) {
              final id = st['id'] as int;
              return CheckboxListTile(
                dense: true,
                contentPadding: EdgeInsets.zero,
                controlAffinity: ListTileControlAffinity.leading,
                value: _selected.contains(id),
                onChanged: (v) => setState(() => v == true ? _selected.add(id) : _selected.remove(id)),
                title: Text('${st['name'] ?? '-'}', style: const TextStyle(fontSize: 13)),
                subtitle: Text('${st['nim'] ?? ''}', style: const TextStyle(fontSize: 11.5)),
              );
            }).toList(),
          ))),
        ]),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
        ElevatedButton(
          onPressed: () {
            final title = _title.text.trim();
            if (title.isEmpty) { showMessage(context, 'Judul sesi wajib diisi.', error: true); return; }
            if (_selected.isEmpty) { showMessage(context, 'Pilih minimal satu mahasiswa penyaji.', error: true); return; }
            Navigator.pop(context);
            widget.onSubmit(title, _selected.toList());
          },
          child: const Text('Buat Sesi'),
        ),
      ],
    );
  }
}

// ── Dialog tetapkan jadwal ───────────────────────────────────────────────────

class _FinalizeDialog extends StatefulWidget {
  final String initialDate, initialTime, initialLocation;
  final void Function(String date, String time, String location) onSubmit;
  const _FinalizeDialog({
    required this.initialDate,
    required this.initialTime,
    required this.initialLocation,
    required this.onSubmit,
  });
  @override
  State<_FinalizeDialog> createState() => _FinalizeDialogState();
}

class _FinalizeDialogState extends State<_FinalizeDialog> {
  DateTime? _date;
  late final TextEditingController _time = TextEditingController(text: widget.initialTime);
  late final TextEditingController _location = TextEditingController(text: widget.initialLocation);

  @override
  void initState() {
    super.initState();
    _date = DateTime.tryParse(widget.initialDate);
  }

  @override
  void dispose() { _time.dispose(); _location.dispose(); super.dispose(); }

  String? get _dateStr => _date == null ? null
      : '${_date!.year}-${_date!.month.toString().padLeft(2, '0')}-${_date!.day.toString().padLeft(2, '0')}';

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Tetapkan Jadwal Final'),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        InkWell(
          onTap: () async {
            final picked = await showDatePicker(
              context: context,
              initialDate: _date ?? DateTime.now(),
              firstDate: DateTime.now(),
              lastDate: DateTime(2100),
            );
            if (picked != null) setState(() => _date = picked);
          },
          child: InputDecorator(
            decoration: const InputDecoration(labelText: 'Tanggal *'),
            child: Text(_dateStr ?? 'Pilih tanggal'),
          ),
        ),
        const SizedBox(height: 12),
        TextField(controller: _time, decoration: const InputDecoration(labelText: 'Waktu', hintText: 'mis. 09.00–12.00')),
        const SizedBox(height: 12),
        TextField(controller: _location, decoration: const InputDecoration(labelText: 'Ruang/Tempat *', hintText: 'mis. Ruang Sidang Lt. 2')),
      ]),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
        ElevatedButton(
          onPressed: () {
            if (_dateStr == null) { showMessage(context, 'Tanggal wajib diisi.', error: true); return; }
            if (_location.text.trim().isEmpty) { showMessage(context, 'Ruang/tempat wajib diisi.', error: true); return; }
            Navigator.pop(context);
            widget.onSubmit(_dateStr!, _time.text.trim(), _location.text.trim());
          },
          child: const Text('Tetapkan'),
        ),
      ],
    );
  }
}
