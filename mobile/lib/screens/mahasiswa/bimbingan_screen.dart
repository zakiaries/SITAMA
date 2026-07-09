import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class BimbinganScreen extends StatefulWidget {
  const BimbinganScreen({super.key});
  @override
  State<BimbinganScreen> createState() => _BimbinganScreenState();
}

class _BimbinganScreenState extends State<BimbinganScreen> {
  late Future<Map<String, dynamic>> _future;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/mahasiswa/bimbingan', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() => _future = _load());

  Future<void> _openForm({Map<String, dynamic>? revisi}) async {
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _BimbinganForm(token: _token, revisi: revisi),
    );
    if (saved == true) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Bimbingan')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openForm(),
        icon: const Icon(Icons.add),
        label: const Text('Ajukan'),
      ),
      body: RefreshIndicator(
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
            final items = List<Map<String, dynamic>>.from(d['guidances'] ?? []);
            return ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 90),
              children: [
                if (d['lecturer'] != null)
                  AppCard(child: Row(children: [
                    const Icon(Icons.person_outline, color: AppColors.primary),
                    const SizedBox(width: 10),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Text('Dosen Pembimbing', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                      Text('${d['lecturer']}', style: const TextStyle(fontWeight: FontWeight.w700)),
                    ])),
                  ])),
                if (items.isEmpty)
                  const EmptyState('Belum ada data bimbingan.', icon: Icons.menu_book_outlined)
                else
                  ...items.map((g) {
                    final status = (g['status'] ?? '').toString();
                    return AppCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(children: [
                            Expanded(child: Text(g['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700))),
                            StatusChip(status),
                          ]),
                          const SizedBox(height: 4),
                          Text(g['date'] ?? '', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                          const SizedBox(height: 8),
                          Text(g['activity'] ?? '', style: const TextStyle(fontSize: 13, height: 1.5)),
                          if ((g['lecturer_note'] ?? '').toString().isNotEmpty)
                            Container(
                              margin: const EdgeInsets.only(top: 10),
                              padding: const EdgeInsets.only(left: 10),
                              decoration: const BoxDecoration(border: Border(left: BorderSide(color: AppColors.primary, width: 3))),
                              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                const Text('Catatan Dosen', style: TextStyle(color: AppColors.primary, fontSize: 12, fontWeight: FontWeight.w700)),
                                Text('${g['lecturer_note']}', style: const TextStyle(fontSize: 13)),
                              ]),
                            ),
                          if (status == 'rejected')
                            Align(
                              alignment: Alignment.centerLeft,
                              child: Padding(
                                padding: const EdgeInsets.only(top: 8),
                                child: ElevatedButton.icon(
                                  style: ElevatedButton.styleFrom(minimumSize: const Size(0, 40)),
                                  onPressed: () => _openForm(revisi: g),
                                  icon: const Icon(Icons.refresh, size: 18),
                                  label: const Text('Revisi & Kirim Ulang'),
                                ),
                              ),
                            ),
                        ],
                      ),
                    );
                  }),
              ],
            );
          },
        ),
      ),
    );
  }
}

class _BimbinganForm extends StatefulWidget {
  final String token;
  final Map<String, dynamic>? revisi;
  const _BimbinganForm({required this.token, this.revisi});
  @override
  State<_BimbinganForm> createState() => _BimbinganFormState();
}

class _BimbinganFormState extends State<_BimbinganForm> {
  late final TextEditingController _title;
  late final TextEditingController _activity;
  late DateTime _date;
  bool _saving = false;
  String? _error;

  bool get _isRevisi => widget.revisi != null;

  @override
  void initState() {
    super.initState();
    _title = TextEditingController(text: widget.revisi?['title'] ?? '');
    _activity = TextEditingController(text: widget.revisi?['activity'] ?? '');
    _date = DateTime.tryParse(widget.revisi?['date'] ?? '') ?? DateTime.now();
  }

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
    final body = {'title': _title.text.trim(), 'activity': _activity.text.trim(), 'date': _dateStr};
    try {
      if (_isRevisi) {
        await ApiClient.put('/mahasiswa/bimbingan/${widget.revisi!['id']}', token: widget.token, body: body);
      } else {
        await ApiClient.post('/mahasiswa/bimbingan', token: widget.token, body: body);
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
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(_isRevisi ? 'Revisi Bimbingan' : 'Ajukan Bimbingan', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
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
            child: InputDecorator(decoration: const InputDecoration(labelText: 'Tanggal'), child: Text(_dateStr)),
          ),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                : Text(_isRevisi ? 'Kirim Ulang' : 'Simpan'),
          ),
        ],
      ),
    );
  }
}
