import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class SeminarScreen extends StatefulWidget {
  const SeminarScreen({super.key});
  @override
  State<SeminarScreen> createState() => _SeminarScreenState();
}

class _SeminarScreenState extends State<SeminarScreen> {
  late Future<Map<String, dynamic>> _future;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/mahasiswa/seminar', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() => _future = _load());

  Future<void> _register(int id) async {
    try {
      await ApiClient.post('/mahasiswa/seminar/$id/register', token: _token);
      if (mounted) showMessage(context, 'Berhasil mendaftar seminar.');
      _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    }
  }

  Future<void> _cancel(int id) async {
    final ok = await showDialog<bool>(context: context, builder: (c) => AlertDialog(
      title: const Text('Batalkan pengajuan seminar?'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Tidak')),
        TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Ya, batalkan')),
      ],
    ));
    if (ok != true) return;
    try {
      await ApiClient.delete('/mahasiswa/seminar/$id', token: _token);
      if (mounted) showMessage(context, 'Pengajuan seminar dibatalkan.');
      _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    }
  }

  Future<void> _openForm({Map<String, dynamic>? edit, bool canSubmit = true, String? blockHint}) async {
    if (edit == null && !canSubmit) {
      showMessage(context, blockHint ?? 'Belum memenuhi syarat mengajukan seminar.', error: true);
      return;
    }
    final saved = await showModalBottomSheet<bool>(
      context: context, isScrollControlled: true,
      builder: (_) => _SeminarForm(token: _token, edit: edit),
    );
    if (saved == true) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Seminar', subtitle: 'Jadwal & pendaftaran seminar'),
        Expanded(
          child: RefreshIndicator(
            onRefresh: () async => _reload(),
        child: FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snap.hasError) {
              return ErrorRetry(message: '${snap.error}', onRetry: _reload);
            }
            final d = snap.data!;
            final canSubmit = d['can_submit'] == true;
            final reqs = List<Map<String, dynamic>>.from(d['requirements'] ?? []);
            final mine = List<Map<String, dynamic>>.from(d['my_seminars'] ?? []);
            final avail = [
              ...List<Map<String, dynamic>>.from(d['seminars'] ?? []),
              ...List<Map<String, dynamic>>.from(d['peer_seminars'] ?? []),
            ];
            final blockHint = reqs.isNotEmpty ? reqs.first['hint'] as String? : null;

            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                ElevatedButton.icon(
                  onPressed: () => _openForm(canSubmit: canSubmit, blockHint: blockHint),
                  icon: const Icon(Icons.add),
                  label: const Text('Ajukan Jadwal Seminar'),
                ),
                if (!canSubmit)
                  Padding(padding: const EdgeInsets.only(top: 6), child: Text(blockHint ?? '', style: const TextStyle(color: AppColors.textMuted, fontSize: 12))),

                SectionTitle('Jadwal Seminar Saya (${mine.length})'),
                if (mine.isEmpty)
                  const AppCard(child: Text('Belum ada seminar yang Anda ajukan.', style: TextStyle(color: AppColors.textMuted)))
                else
                  ...mine.map((s) => _card(s, mine: true)),

                SectionTitle('Seminar Tersedia (${avail.length})'),
                if (avail.isEmpty)
                  const AppCard(child: Text('Belum ada seminar tersedia.', style: TextStyle(color: AppColors.textMuted)))
                else
                  ...avail.map((s) => _card(s, mine: false)),
                const SizedBox(height: 24),
              ],
            );
          },
          ),
          ),
        ),
      ]),
    );
  }

  Widget _card(Map<String, dynamic> s, {required bool mine}) {
    final aud = s['audience'] ?? 0;
    final minA = s['min_audience'] ?? 0;
    final registered = s['is_registered'] == true;
    return AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(children: [
        Expanded(child: Text(s['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700))),
        StatusChip(s['status'] ?? ''),
      ]),
      const SizedBox(height: 2),
      Text(s['program'] ?? '', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
      const SizedBox(height: 8),
      Wrap(spacing: 16, runSpacing: 4, children: [
        _meta('Tanggal', s['date'] ?? '-'),
        _meta('Waktu', s['time'] ?? '-'),
        _meta('Ruang', s['location'] ?? '-'),
      ]),
      const SizedBox(height: 8),
      Text('Audiens: $aud/$minA', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
      if (mine)
        Padding(padding: const EdgeInsets.only(top: 10), child: Row(children: [
          Expanded(child: OutlinedButton(onPressed: () => _openForm(edit: s), child: const Text('Edit'))),
          const SizedBox(width: 8),
          Expanded(child: OutlinedButton(
            style: OutlinedButton.styleFrom(foregroundColor: AppColors.error, side: const BorderSide(color: AppColors.error)),
            onPressed: () => _cancel(s['id']), child: const Text('Batalkan'))),
        ]))
      else if (registered)
        const Padding(padding: EdgeInsets.only(top: 10), child: Text('✓ Anda sudah terdaftar', style: TextStyle(color: AppColors.success, fontWeight: FontWeight.w600)))
      else
        Padding(padding: const EdgeInsets.only(top: 10), child: SizedBox(
          width: double.infinity,
          child: ElevatedButton(onPressed: () => _register(s['id']), style: ElevatedButton.styleFrom(minimumSize: const Size(0, 42)), child: const Text('Daftar sebagai Audiens')),
        )),
    ]));
  }

  Widget _meta(String k, String v) => Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(k, style: const TextStyle(color: AppColors.textMuted, fontSize: 11)),
        Text(v, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
      ]);
}

class _SeminarForm extends StatefulWidget {
  final String token;
  final Map<String, dynamic>? edit;
  const _SeminarForm({required this.token, this.edit});
  @override
  State<_SeminarForm> createState() => _SeminarFormState();
}

class _SeminarFormState extends State<_SeminarForm> {
  late final TextEditingController _title;
  late final TextEditingController _time;
  late final TextEditingController _location;
  late final TextEditingController _desc;
  DateTime? _date;
  bool _saving = false;
  String? _error;

  bool get _isEdit => widget.edit != null;

  @override
  void initState() {
    super.initState();
    _title = TextEditingController(text: widget.edit?['title'] ?? '');
    _time = TextEditingController(text: widget.edit?['time'] ?? '');
    _location = TextEditingController(text: widget.edit?['location'] ?? '');
    _desc = TextEditingController(text: widget.edit?['description'] ?? '');
    _date = DateTime.tryParse(widget.edit?['date'] ?? '');
  }

  @override
  void dispose() { _title.dispose(); _time.dispose(); _location.dispose(); _desc.dispose(); super.dispose(); }

  String? get _dateStr => _date == null ? null
      : '${_date!.year}-${_date!.month.toString().padLeft(2, '0')}-${_date!.day.toString().padLeft(2, '0')}';

  Future<void> _save() async {
    if (_title.text.trim().isEmpty || _dateStr == null) { setState(() => _error = 'Judul & tanggal wajib diisi.'); return; }
    setState(() { _saving = true; _error = null; });
    final body = {
      'title': _title.text.trim(),
      'date': _dateStr,
      'time': _time.text.trim(),
      'location': _location.text.trim(),
      'description': _desc.text.trim(),
    };
    try {
      if (_isEdit) {
        await ApiClient.put('/mahasiswa/seminar/${widget.edit!['id']}', token: widget.token, body: body);
      } else {
        await ApiClient.post('/mahasiswa/seminar', token: widget.token, body: body);
      }
      if (mounted) Navigator.pop(context, true);
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(left: 16, right: 16, top: 16, bottom: MediaQuery.of(context).viewInsets.bottom + 16),
      child: SingleChildScrollView(
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Text(_isEdit ? 'Edit Seminar' : 'Ajukan Seminar', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
          const SizedBox(height: 16),
          if (_error != null) Padding(padding: const EdgeInsets.only(bottom: 10), child: Text(_error!, style: const TextStyle(color: AppColors.error))),
          TextField(controller: _title, decoration: const InputDecoration(labelText: 'Judul Seminar')),
          const SizedBox(height: 12),
          InkWell(
            onTap: () async {
              final picked = await showDatePicker(context: context, initialDate: _date ?? DateTime.now(), firstDate: DateTime.now(), lastDate: DateTime(2100));
              if (picked != null) setState(() => _date = picked);
            },
            child: InputDecorator(decoration: const InputDecoration(labelText: 'Tanggal'), child: Text(_dateStr ?? 'Pilih tanggal')),
          ),
          const SizedBox(height: 12),
          TextField(controller: _time, decoration: const InputDecoration(labelText: 'Waktu (opsional)', hintText: '09:00 - 11:00')),
          const SizedBox(height: 12),
          TextField(controller: _location, decoration: const InputDecoration(labelText: 'Tempat / Ruang (opsional)')),
          const SizedBox(height: 12),
          TextField(controller: _desc, maxLines: 3, decoration: const InputDecoration(labelText: 'Deskripsi (opsional)', alignLabelWithHint: true)),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                : Text(_isEdit ? 'Simpan' : 'Ajukan'),
          ),
        ]),
      ),
    );
  }
}
