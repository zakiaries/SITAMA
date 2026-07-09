import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

/// Input nilai oleh dosen kampus untuk seorang mahasiswa.
class DosenNilaiScreen extends StatefulWidget {
  final int studentId;
  final String studentName;
  const DosenNilaiScreen({super.key, required this.studentId, required this.studentName});
  @override
  State<DosenNilaiScreen> createState() => _DosenNilaiScreenState();
}

class _DosenNilaiScreenState extends State<DosenNilaiScreen> {
  late Future<List<Map<String, dynamic>>> _future;
  final Map<int, TextEditingController> _controllers = {};
  bool _saving = false;

  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  @override
  void dispose() {
    for (final c in _controllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  Future<List<Map<String, dynamic>>> _load() async {
    final data = await ApiClient.get('/dosen/mahasiswa/${widget.studentId}/nilai', token: _token);
    final comps = List<Map<String, dynamic>>.from(data['components'] ?? []);
    for (final c in comps) {
      for (final d in List<Map<String, dynamic>>.from(c['details'] ?? [])) {
        final id = d['id'] as int;
        _controllers[id] = TextEditingController(text: d['score']?.toString() ?? '');
      }
    }
    return comps;
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    final scores = <String, dynamic>{};
    _controllers.forEach((id, c) {
      if (c.text.trim().isNotEmpty) scores['$id'] = c.text.trim();
    });
    try {
      await ApiClient.post('/dosen/mahasiswa/${widget.studentId}/nilai', token: _token, body: {'scores': scores});
      if (mounted) {
        showMessage(context, 'Nilai berhasil disimpan.');
        Navigator.pop(context, true);
      }
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Input Nilai')),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return ErrorRetry(message: '${snap.error}', onRetry: () => setState(() => _future = _load()));
          }
          final comps = snap.data!;
          return ListView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
            children: [
              Text(widget.studentName, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
              const SizedBox(height: 4),
              const Text('Isi skor 0–100 per sub-komponen.', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
              const SizedBox(height: 12),
              ...comps.map((c) {
                final details = List<Map<String, dynamic>>.from(c['details'] ?? []);
                return AppCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(c['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w800)),
                      const SizedBox(height: 6),
                      ...details.map((d) => Padding(
                            padding: const EdgeInsets.symmetric(vertical: 6),
                            child: Row(children: [
                              Expanded(child: Text(d['name'] ?? '', style: const TextStyle(fontSize: 13))),
                              SizedBox(
                                width: 84,
                                child: TextField(
                                  controller: _controllers[d['id']],
                                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                  textAlign: TextAlign.center,
                                  decoration: const InputDecoration(hintText: '0-100', isDense: true),
                                ),
                              ),
                            ]),
                          )),
                    ],
                  ),
                );
              }),
            ],
          );
        },
      ),
      bottomSheet: Padding(
        padding: const EdgeInsets.all(16),
        child: ElevatedButton(
          onPressed: _saving ? null : _save,
          child: _saving
              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
              : const Text('Simpan Nilai'),
        ),
      ),
    );
  }
}
