import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';
import 'penilaian_screen.dart';

class IndustriMahasiswaDetail extends StatefulWidget {
  final int studentId;
  const IndustriMahasiswaDetail({super.key, required this.studentId});
  @override
  State<IndustriMahasiswaDetail> createState() => _IndustriMahasiswaDetailState();
}

class _IndustriMahasiswaDetailState extends State<IndustriMahasiswaDetail> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  Object? _error;
  String get _token => context.read<AuthProvider>().token ?? '';
  int get _sid => widget.studentId;
  int _filter = 0; // 0 Semua, 1 Belum Dikomen, 2 Sudah Dikomen

  @override
  void initState() {
    super.initState();
    _reload();
  }

  Future<void> _reload() async {
    setState(() { _loading = _data == null; _error = null; });
    try {
      final data = await ApiClient.get('/dosen-industri/mahasiswa/$_sid', token: _token);
      if (!mounted) return;
      setState(() { _data = Map<String, dynamic>.from(data); _loading = false; });
    } catch (e) {
      if (!mounted) return;
      setState(() { _error = e; _loading = false; });
    }
  }

  Future<void> _action(Future<void> Function() run) async {
    try {
      await run();
      if (mounted) showMessage(context, 'Berhasil.');
      await _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    }
  }

  Future<void> _komentar(Map l) async {
    final note = await showNoteDialog(context,
        title: 'Komentar Log Book',
        initial: l['industry_note'] ?? '',
        requiredNote: true,
        okLabel: 'Kirim');
    if (note == null) return;
    _action(() => ApiClient.post('/dosen-industri/mahasiswa/$_sid/logbook/${l['id']}/komentar', token: _token, body: {'komentar': note}));
  }

  Future<void> _hapusKomentar(Map l) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Hapus komentar?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Hapus')),
        ],
      ),
    );
    if (ok != true) return;
    _action(() => ApiClient.delete('/dosen-industri/mahasiswa/$_sid/logbook/${l['id']}/komentar', token: _token));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Detail Mahasiswa'),
        Expanded(
          child: RefreshIndicator(
            onRefresh: _reload,
            child: Builder(builder: (context) {
            // Spinner hanya saat load PERTAMA (belum ada data). Saat reload, data
            // lama tetap tampil lalu di-update di tempat (tanpa kedip/lompat scroll).
            if (_data == null && _loading) {
              return const Center(child: CircularProgressIndicator());
            }
            if (_data == null && _error != null) {
              return ErrorRetry(message: '$_error', onRetry: _reload);
            }
            final d = _data!;
            final student = Map<String, dynamic>.from(d['student'] ?? {});
            final internship = Map<String, dynamic>.from(d['internship'] ?? {});
            final logbooks = List<Map<String, dynamic>>.from(d['logbooks'] ?? [])
              // Urutan stabil (tanggal terbaru dulu, seri di-tie-break dengan id) supaya
              // item TIDAK pindah posisi saat baru dikomentari — komentar langsung tampil di tempatnya.
              ..sort((a, b) {
                final byDate = '${b['date'] ?? ''}'.compareTo('${a['date'] ?? ''}');
                if (byDate != 0) return byDate;
                return ((b['id'] ?? 0) as num).compareTo((a['id'] ?? 0) as num);
              });
            final name = student['name'] ?? '';

            final belum = logbooks.where((l) => (l['industry_note'] ?? '').toString().isEmpty).toList();
            final sudah = logbooks.where((l) => (l['industry_note'] ?? '').toString().isNotEmpty).toList();
            final shown = _filter == 1 ? belum : _filter == 2 ? sudah : logbooks;

            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Header
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(15),
                    border: Border.all(color: AppColors.borderSubtle),
                    boxShadow: kSoftShadow,
                  ),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                    if ((student['username'] ?? '').toString().isNotEmpty)
                      Container(margin: const EdgeInsets.only(top: 5), padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 2),
                        decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(9999)),
                        child: Text('${student['username']}', style: const TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w700))),
                    const SizedBox(height: 6),
                    Text('${internship['position'] ?? ''} · ${internship['company'] ?? ''}', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                  ]),
                ),
                const SizedBox(height: 12),

                // Beri Penilaian
                SizedBox(width: double.infinity, child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(50)),
                  onPressed: () async {
                    final saved = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => IndustriPenilaianScreen(studentId: _sid, studentName: name)));
                    if (saved == true) _reload();
                  },
                  icon: const Icon(Icons.star_border),
                  label: const Text('Beri / Ubah Penilaian Akhir'),
                )),
                const SizedBox(height: 16),

                const Text('Logbook Mahasiswa', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800)),
                const SizedBox(height: 10),
                // ── Filter segmented (seragam dgn dosen) ──
                SegTabs(
                  small: true,
                  labels: ['Semua (${logbooks.length})', 'Belum Dikomen (${belum.length})', 'Sudah Dikomen (${sudah.length})'],
                  index: _filter,
                  onChanged: (i) => setState(() => _filter = i),
                ),
                const SizedBox(height: 12),

                if (shown.isEmpty)
                  const AppCard(child: Text('Tidak ada log book pada filter ini.', style: TextStyle(color: AppColors.textMuted)))
                else
                  ...shown.map((l) {
                    final hasNote = (l['industry_note'] ?? '').toString().isNotEmpty;
                    return AppCard(
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Row(children: [
                          Expanded(child: Text(l['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700))),
                          Text(l['date'] ?? '', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                        ]),
                        const SizedBox(height: 6),
                        Text(l['activity'] ?? '', style: const TextStyle(fontSize: 13, height: 1.5)),
                        if (hasNote)
                          Container(
                            margin: const EdgeInsets.only(top: 10),
                            padding: const EdgeInsets.only(left: 10),
                            decoration: const BoxDecoration(border: Border(left: BorderSide(color: AppColors.success, width: 3))),
                            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              const Text('Komentar Anda', style: TextStyle(color: AppColors.success, fontSize: 12, fontWeight: FontWeight.w700)),
                              Text('${l['industry_note']}', style: const TextStyle(fontSize: 13)),
                            ]),
                          ),
                        Row(mainAxisAlignment: MainAxisAlignment.end, children: [
                          if (hasNote)
                            TextButton.icon(onPressed: () => _hapusKomentar(l), icon: const Icon(Icons.delete_outline, size: 18, color: AppColors.error), label: const Text('Hapus', style: TextStyle(color: AppColors.error))),
                          TextButton.icon(onPressed: () => _komentar(l), icon: const Icon(Icons.comment_outlined, size: 18), label: Text(hasNote ? 'Ubah' : 'Komentar')),
                        ]),
                      ]),
                    );
                  }),
                const SizedBox(height: 24),
              ],
            );
          }),
          ),
        ),
      ]),
    );
  }
}
