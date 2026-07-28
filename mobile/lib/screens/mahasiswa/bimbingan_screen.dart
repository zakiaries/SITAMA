import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/app_config.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/file_helper.dart';
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
  String _q = '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/mahasiswa/bimbingan', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() { _future = _load(); });

  Future<void> _openForm({Map<String, dynamic>? revisi}) async {
    await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _BimbinganForm(token: _token, revisi: revisi),
    );
    if (mounted) _reload(); // selalu segarkan setelah modal ditutup
  }

  Future<void> _openFile(String url) async {
    final uri = Uri.parse(AppConfig.absoluteFileUrl(url));
    final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!ok && mounted) showMessage(context, 'Tidak bisa membuka file.', error: true);
  }

  Future<void> _delete(dynamic id) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Hapus bimbingan?'),
        content: const Text('Bimbingan yang belum disetujui akan dihapus permanen.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Hapus')),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ApiClient.delete('/mahasiswa/bimbingan/$id', token: _token);
      if (mounted) showMessage(context, 'Bimbingan dihapus.');
      _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(
        children: [
          AppHeader(
            title: 'Bimbingan',
            subtitle: 'Riwayat catatan bimbingan kamu',
            trailing: HeaderAction(Icons.add, () => _openForm()),
            bottom: headerSearch(hint: 'Cari bimbingan', onChanged: (v) => setState(() => _q = v)),
          ),
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
                  final all = List<Map<String, dynamic>>.from(d['guidances'] ?? []);
                  final items = _q.trim().isEmpty
                      ? all
                      : all.where((g) => '${g['title']} ${g['activity']}'.toLowerCase().contains(_q.toLowerCase())).toList();
                  return ListView(
                    padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
                    children: [
                      if (d['lecturer'] != null)
                        AppCard(child: Row(children: [
                          Container(
                            width: 34, height: 34,
                            decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(10)),
                            child: const Icon(Icons.person_outline, color: AppColors.primary, size: 18),
                          ),
                          const SizedBox(width: 11),
                          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            const Text('Dosen Pembimbing', style: TextStyle(color: AppColors.textSecondary, fontSize: 11.5)),
                            Text('${d['lecturer']}', style: const TextStyle(fontWeight: FontWeight.w700)),
                          ])),
                        ])),
                      if (items.isEmpty)
                        const EmptyState('Belum ada bimbingan',
                            icon: Icons.menu_book_outlined,
                            hint: 'Ajukan bimbingan lewat tombol + di kanan atas.')
                      else
                        ...items.map((g) {
                          final status = (g['status'] ?? '').toString();
                          return AppCard(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    StatusDot(statusDotKind(status)),
                                    const SizedBox(width: 11),
                                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                      Text(g['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700)),
                                      const SizedBox(height: 2),
                                      Text(g['date'] ?? '', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                                    ])),
                                    if (status.isNotEmpty) StatusChip(status),
                                  ],
                                ),
                                if ((g['activity'] ?? '').toString().isNotEmpty) ...[
                                  const SizedBox(height: 10),
                                  Text(g['activity'], style: const TextStyle(fontSize: 13, height: 1.5)),
                                ],
                                if ((g['lecturer_note'] ?? '').toString().isNotEmpty)
                                  NoteBlock(label: 'Catatan Dosen', value: '${g['lecturer_note']}'),
                                if ((g['file_url'] ?? '').toString().isNotEmpty)
                                  Align(
                                    alignment: Alignment.centerLeft,
                                    child: TextButton.icon(
                                      onPressed: () => _openFile('${g['file_url']}'),
                                      style: TextButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 4)),
                                      icon: const Icon(Icons.description_outlined, size: 18),
                                      label: const Text('Lihat File Bimbingan'),
                                    ),
                                  ),
                                if (status != 'approved')
                                  Padding(
                                    padding: const EdgeInsets.only(top: 8),
                                    child: Row(
                                      children: [
                                        if (status == 'rejected')
                                          ElevatedButton.icon(
                                            style: ElevatedButton.styleFrom(minimumSize: const Size(0, 40)),
                                            onPressed: () => _openForm(revisi: g),
                                            icon: const Icon(Icons.refresh, size: 18),
                                            label: const Text('Revisi & Kirim Ulang'),
                                          )
                                        else
                                          OutlinedButton.icon(
                                            style: OutlinedButton.styleFrom(minimumSize: const Size(0, 40)),
                                            onPressed: () => _openForm(revisi: g),
                                            icon: const Icon(Icons.edit_outlined, size: 18),
                                            label: const Text('Edit'),
                                          ),
                                        const Spacer(),
                                        TextButton.icon(
                                          style: TextButton.styleFrom(foregroundColor: AppColors.error),
                                          onPressed: () => _delete(g['id']),
                                          icon: const Icon(Icons.delete_outline, size: 18),
                                          label: const Text('Hapus'),
                                        ),
                                      ],
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
          ),
        ],
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
  String? _filePath; // file lampiran (opsional)

  bool get _isEdit => widget.revisi != null; // ada record → update (PUT)
  bool get _isRevisi => widget.revisi?['status']?.toString() == 'rejected'; // untuk kata "Revisi"

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
    final fields = {'title': _title.text.trim(), 'activity': _activity.text.trim(), 'date': _dateStr};
    try {
      if (_filePath != null) {
        // Ada file → kirim multipart. Untuk edit/revisi pakai spoof _method=PUT
        // (PHP tidak mengurai file pada request PUT asli).
        final path = _isEdit ? '/mahasiswa/bimbingan/${widget.revisi!['id']}' : '/mahasiswa/bimbingan';
        await ApiClient.upload(
          path,
          fileField: 'file',
          filePath: _filePath!,
          fields: _isEdit ? {...fields, '_method': 'PUT'} : fields,
          token: widget.token,
        );
      } else if (_isEdit) {
        await ApiClient.put('/mahasiswa/bimbingan/${widget.revisi!['id']}', token: widget.token, body: fields);
      } else {
        await ApiClient.post('/mahasiswa/bimbingan', token: widget.token, body: fields);
      }
      if (mounted) Navigator.pop(context, true);
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } catch (_) {
      setState(() => _error = 'Tidak dapat terhubung ke server. Coba lagi.');
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
          Text(_isEdit ? (_isRevisi ? 'Revisi Bimbingan' : 'Edit Bimbingan') : 'Ajukan Bimbingan', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
          const SizedBox(height: 16),
          if (_error != null) Padding(padding: const EdgeInsets.only(bottom: 10), child: Text(_error!, style: const TextStyle(color: AppColors.error))),
          TextField(controller: _title, decoration: const InputDecoration(labelText: 'Judul')),
          const SizedBox(height: 12),
          TextField(controller: _activity, maxLines: 4, decoration: const InputDecoration(labelText: 'Aktivitas', alignLabelWithHint: true)),
          const SizedBox(height: 12),
          InkWell(
            onTap: () async {
              final picked = await showDatePicker(context: context, initialDate: _date, firstDate: DateTime(2020), lastDate: DateTime.now());
              if (picked != null) setState(() => _date = picked);
            },
            child: InputDecorator(decoration: const InputDecoration(labelText: 'Tanggal'), child: Text(_dateStr)),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: _saving ? null : () async {
              final p = await pickFilePath(extensions: ['pdf', 'doc', 'docx']);
              if (p != null) setState(() => _filePath = p);
            },
            icon: const Icon(Icons.attach_file, size: 18),
            label: Text(_filePath == null ? 'Lampirkan File (PDF/Word, opsional)' : fileName(_filePath!),
                maxLines: 1, overflow: TextOverflow.ellipsis),
          ),
          if (_filePath != null)
            Align(
              alignment: Alignment.centerLeft,
              child: TextButton.icon(
                onPressed: _saving ? null : () => setState(() => _filePath = null),
                icon: const Icon(Icons.close, size: 16, color: AppColors.error),
                label: const Text('Hapus lampiran', style: TextStyle(color: AppColors.error, fontSize: 12)),
              ),
            ),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                : Text(_isEdit ? (_isRevisi ? 'Kirim Ulang' : 'Simpan Perubahan') : 'Simpan'),
          ),
        ],
      ),
    );
  }
}
