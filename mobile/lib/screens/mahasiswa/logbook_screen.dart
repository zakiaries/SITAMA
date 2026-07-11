import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class LogbookScreen extends StatefulWidget {
  const LogbookScreen({super.key});
  @override
  State<LogbookScreen> createState() => _LogbookScreenState();
}

class _LogbookScreenState extends State<LogbookScreen> {
  late Future<List<Map<String, dynamic>>> _future;
  String _q = '';

  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<Map<String, dynamic>>> _load() async {
    final data = await ApiClient.get('/mahasiswa/logbook', token: _token);
    return List<Map<String, dynamic>>.from(data['logbooks'] ?? []);
  }

  void _reload() => setState(() => _future = _load());

  Future<void> _delete(int id) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Hapus log book?'),
        content: const Text('Data ini akan dihapus permanen.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Hapus')),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ApiClient.delete('/mahasiswa/logbook/$id', token: _token);
      if (mounted) showMessage(context, 'Log book dihapus.');
      _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    }
  }

  Future<void> _openAdd() async {
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _LogbookForm(token: _token),
    );
    if (saved == true) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(
        children: [
          AppHeader(
            title: 'Log Book',
            subtitle: 'Catatan kegiatan harian magang',
            trailing: HeaderAction(Icons.add, _openAdd),
            bottom: headerSearch(hint: 'Cari kegiatan', onChanged: (v) => setState(() => _q = v)),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async => _reload(),
              child: FutureBuilder<List<Map<String, dynamic>>>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (snap.hasError) {
                    return ErrorRetry(message: '${snap.error}', onRetry: _reload);
                  }
                  final all = snap.data!;
                  final items = _q.trim().isEmpty
                      ? all
                      : all.where((l) => '${l['title']} ${l['activity']}'.toLowerCase().contains(_q.toLowerCase())).toList();
                  if (items.isEmpty) {
                    return ListView(children: const [
                      EmptyState('Belum ada log book',
                          icon: Icons.book_outlined,
                          hint: 'Tambah kegiatan lewat tombol + di kanan atas.'),
                    ]);
                  }
                  return ListView(
                    padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
                    children: items.map((l) {
                      return AppCard(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                                  decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(999)),
                                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                                    const Icon(Icons.calendar_today_outlined, size: 12, color: AppColors.primary),
                                    const SizedBox(width: 5),
                                    Text(l['date'] ?? '', style: const TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w700)),
                                  ]),
                                ),
                                const Spacer(),
                                InkWell(
                                  onTap: () => _delete(l['id']),
                                  borderRadius: BorderRadius.circular(8),
                                  child: const Padding(
                                    padding: EdgeInsets.all(4),
                                    child: Icon(Icons.delete_outline, size: 19, color: AppColors.error),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(l['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700)),
                            const SizedBox(height: 3),
                            Text(l['activity'] ?? '', style: const TextStyle(fontSize: 13, height: 1.5)),
                            if ((l['lecturer_note'] ?? '').toString().isNotEmpty)
                              _Note('Catatan Dosen', l['lecturer_note'], AppColors.primary),
                            if ((l['industry_note'] ?? '').toString().isNotEmpty)
                              _Note('Catatan Pembimbing Industri', l['industry_note'], AppColors.success),
                          ],
                        ),
                      );
                    }).toList(),
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _Note extends StatelessWidget {
  final String label;
  final dynamic value;
  final Color color;
  const _Note(this.label, this.value, this.color);
  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(top: 10),
        padding: const EdgeInsets.only(left: 10),
        decoration: BoxDecoration(border: Border(left: BorderSide(color: color, width: 3))),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label, style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w700)),
          const SizedBox(height: 2),
          Text('$value', style: const TextStyle(fontSize: 13)),
        ]),
      );
}

class _LogbookForm extends StatefulWidget {
  final String token;
  const _LogbookForm({required this.token});
  @override
  State<_LogbookForm> createState() => _LogbookFormState();
}

class _LogbookFormState extends State<_LogbookForm> {
  final _title = TextEditingController();
  final _activity = TextEditingController();
  DateTime _date = DateTime.now();
  bool _saving = false;
  String? _error;

  @override
  void dispose() {
    _title.dispose();
    _activity.dispose();
    super.dispose();
  }

  String get _dateStr => '${_date.year}-${_date.month.toString().padLeft(2, '0')}-${_date.day.toString().padLeft(2, '0')}';

  Future<void> _save() async {
    if (_title.text.trim().isEmpty || _activity.text.trim().isEmpty) {
      setState(() => _error = 'Judul & aktivitas wajib diisi.');
      return;
    }
    setState(() { _saving = true; _error = null; });
    try {
      await ApiClient.post('/mahasiswa/logbook', token: widget.token, body: {
        'title': _title.text.trim(),
        'activity': _activity.text.trim(),
        'date': _dateStr,
      });
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
      padding: EdgeInsets.only(
        left: 16, right: 16, top: 16,
        bottom: MediaQuery.of(context).viewInsets.bottom + 16,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text('Tambah Log Book', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
          const SizedBox(height: 16),
          if (_error != null) Padding(padding: const EdgeInsets.only(bottom: 10), child: Text(_error!, style: const TextStyle(color: AppColors.error))),
          TextField(controller: _title, decoration: const InputDecoration(labelText: 'Judul')),
          const SizedBox(height: 12),
          TextField(controller: _activity, maxLines: 4, decoration: const InputDecoration(labelText: 'Aktivitas', alignLabelWithHint: true)),
          const SizedBox(height: 12),
          InkWell(
            onTap: () async {
              final picked = await showDatePicker(context: context, initialDate: _date, firstDate: DateTime(2020), lastDate: DateTime(2100));
              if (picked != null) setState(() => _date = picked);
            },
            child: InputDecorator(
              decoration: const InputDecoration(labelText: 'Tanggal'),
              child: Text(_dateStr),
            ),
          ),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                : const Text('Simpan'),
          ),
        ],
      ),
    );
  }
}
