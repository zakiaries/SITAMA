import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../widgets/ui.dart';

/// Penilaian akhir oleh pembimbing industri (skor + catatan kinerja).
class IndustriPenilaianScreen extends StatefulWidget {
  final int studentId;
  final String studentName;
  const IndustriPenilaianScreen({super.key, required this.studentId, required this.studentName});
  @override
  State<IndustriPenilaianScreen> createState() => _IndustriPenilaianScreenState();
}

class _IndustriPenilaianScreenState extends State<IndustriPenilaianScreen> {
  late Future<Map<String, dynamic>> _future;
  final Map<int, TextEditingController> _scores = {};
  final _notes = TextEditingController();
  bool _saving = false;

  // Sama seperti portal dosen: penilaian baru terbuka setelah mahasiswa
  // merampungkan magangnya. Dibaca di muka lewat can_score/score_locked supaya
  // pembimbing tak mengetik seluruh skor lalu ditolak 422 di ujung.
  bool _bolehMenilai = true;
  String? _alasanTerkunci;

  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  @override
  void dispose() {
    for (final c in _scores.values) {
      c.dispose();
    }
    _notes.dispose();
    super.dispose();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/dosen-industri/mahasiswa/${widget.studentId}/penilaian', token: _token);
    final m = Map<String, dynamic>.from(data);
    // Server lama tak mengirim can_score — anggap boleh, biar formulir tak
    // terkunci hanya karena aplikasinya lebih baru dari servernya.
    _bolehMenilai = m['can_score'] as bool? ?? true;
    _alasanTerkunci = m['score_locked'] as String?;
    _notes.text = m['performance_notes'] ?? '';
    for (final c in List<Map<String, dynamic>>.from(m['components'] ?? [])) {
      for (final d in List<Map<String, dynamic>>.from(c['details'] ?? [])) {
        _scores[d['id'] as int] = TextEditingController(text: d['score']?.toString() ?? '');
      }
    }
    return m;
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    final scores = <String, dynamic>{};
    _scores.forEach((id, c) {
      if (c.text.trim().isNotEmpty) scores['$id'] = c.text.trim();
    });
    try {
      await ApiClient.post('/dosen-industri/mahasiswa/${widget.studentId}/penilaian', token: _token, body: {
        'scores': scores,
        'performance_notes': _notes.text.trim(),
      });
      if (mounted) {
        showMessage(context, 'Penilaian berhasil disimpan.');
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
      appBar: AppBar(title: const Text('Penilaian Akhir')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return ErrorRetry(message: '${snap.error}', onRetry: () => setState(() { _future = _load(); }));
          }
          final comps = List<Map<String, dynamic>>.from(snap.data!['components'] ?? []);
          return ListView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
            children: [
              Text(widget.studentName, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
              const SizedBox(height: 4),
              const Text('Isi skor 1–10 per komponen. Nilai industri = rata-rata seluruh komponen.',
                  style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
              const SizedBox(height: 12),
              if (!_bolehMenilai) ...[
                AppCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Belum bisa dinilai',
                          style: TextStyle(fontWeight: FontWeight.w800, color: AppColors.warnText)),
                      const SizedBox(height: 4),
                      Text(
                        _alasanTerkunci ??
                            'Mahasiswa ini belum menyelesaikan magangnya. Formulir akan terbuka sendiri setelah dilengkapi.',
                        style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 4),
              ],
              ...comps.map((c) {
                final details = List<Map<String, dynamic>>.from(c['details'] ?? []);
                return AppCard(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(c['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w800)),
                    const SizedBox(height: 6),
                    ...details.map((d) => Padding(
                          padding: const EdgeInsets.symmetric(vertical: 6),
                          child: Row(children: [
                            Expanded(child: Text(d['name'] ?? '', style: const TextStyle(fontSize: 13))),
                            SizedBox(
                              width: 84,
                              child: TextField(
                                controller: _scores[d['id']],
                                enabled: _bolehMenilai,
                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                textAlign: TextAlign.center,
                                decoration: const InputDecoration(hintText: '1-10', isDense: true),
                              ),
                            ),
                          ]),
                        )),
                  ]),
                );
              }),
              const SectionTitle('Catatan Kinerja'),
              AppCard(child: TextField(
                controller: _notes,
                enabled: _bolehMenilai,
                maxLines: 4,
                decoration: const InputDecoration(hintText: 'Catatan kinerja mahasiswa (opsional)...', border: InputBorder.none, filled: false),
              )),
            ],
          );
        },
      ),
      bottomSheet: Padding(
        padding: const EdgeInsets.all(16),
        child: ElevatedButton(
          onPressed: (_saving || !_bolehMenilai) ? null : _save,
          child: _saving
              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
              : Text(_bolehMenilai ? 'Simpan Penilaian' : 'Belum bisa dinilai'),
        ),
      ),
    );
  }
}
