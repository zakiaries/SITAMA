import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/app_config.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';
import 'nilai_screen.dart';

/// Buka file lampiran (bimbingan/laporan) di aplikasi eksternal.
Future<void> _openFileUrl(BuildContext context, String url) async {
  if (url.trim().isEmpty) return;
  final uri = Uri.parse(AppConfig.absoluteFileUrl(url));
  final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
  if (!ok && context.mounted) showMessage(context, 'Tidak bisa membuka file.', error: true);
}

class DosenMahasiswaDetail extends StatefulWidget {
  final int studentId;
  const DosenMahasiswaDetail({super.key, required this.studentId});
  @override
  State<DosenMahasiswaDetail> createState() => _DosenMahasiswaDetailState();
}

class _DosenMahasiswaDetailState extends State<DosenMahasiswaDetail> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  Object? _error;
  String get _token => context.read<AuthProvider>().token ?? '';
  int get _sid => widget.studentId;
  int _tab = 0; // 0 Bimbingan, 1 Log Book, 2 Laporan Akhir

  @override
  void initState() {
    super.initState();
    _reload();
  }

  Future<void> _reload() async {
    setState(() { _loading = _data == null; _error = null; });
    try {
      final data = await ApiClient.get('/dosen/mahasiswa/$_sid', token: _token);
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
            final nilai = Map<String, dynamic>.from(d['nilai'] ?? {});
            final guidances = List<Map<String, dynamic>>.from(d['guidances'] ?? []);
            final report = d['report'] as Map<String, dynamic>?;
            final logbooks = List<Map<String, dynamic>>.from(d['logbooks'] ?? [])
              // Terbaru ditambah/diedit di paling atas (updated_at), seri di-tie-break
              // dengan id — konsisten dengan sisi mahasiswa.
              ..sort((a, b) {
                final byUpdated = '${b['updated_at'] ?? ''}'.compareTo('${a['updated_at'] ?? ''}');
                if (byUpdated != 0) return byUpdated;
                return ((b['id'] ?? 0) as num).compareTo((a['id'] ?? 0) as num);
              });
            final name = student['name'] ?? '';

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
                  child: Row(children: [
                    CircleAvatar(radius: 26, backgroundColor: AppColors.primary, child: Text(_initials(name), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800))),
                    const SizedBox(width: 14),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                      if ((student['username'] ?? '').toString().isNotEmpty)
                        Container(margin: const EdgeInsets.only(top: 5), padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 2),
                          decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(9999)),
                          child: Text('${student['username']}', style: const TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w700))),
                      Text('${student['the_class'] ?? ''}', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                    ])),
                  ]),
                ),
                const SizedBox(height: 14),

                // Info magang
                const SectionTitle('Info Magang'),
                AppCard(child: Column(children: [
                  InfoRow('Perusahaan', internship['company'] ?? '-'),
                  InfoRow('Posisi', internship['position'] ?? '-'),
                  InfoRow('Periode', '${internship['start_date'] ?? '-'} s/d ${internship['end_date'] ?? '-'}'),
                ])),
                const SizedBox(height: 4),

                // ── MENU KECIL (segmented) ──
                SegTabs(
                  labels: ['Bimbingan (${guidances.length})', 'Log Book (${logbooks.length})', 'Laporan Akhir'],
                  index: _tab,
                  onChanged: (i) => setState(() => _tab = i),
                ),
                const SizedBox(height: 14),

                // Konten tab terpilih
                if (_tab == 0) ...[
                  if (guidances.isEmpty)
                    const AppCard(child: Text('Belum ada bimbingan.', style: TextStyle(color: AppColors.textMuted)))
                  else
                    ...guidances.map((g) => _GuidanceCard(g: g, onApprove: () => _approveGuidance(g), onRevisi: () => _revisiGuidance(g))),
                ] else if (_tab == 1) ...[
                  if (logbooks.isEmpty)
                    const AppCard(child: Text('Belum ada log book.', style: TextStyle(color: AppColors.textMuted)))
                  else
                    ...logbooks.map((l) => _LogbookCard(l: l, onNote: () => _logbookNote(l))),
                ] else ...[
                  if (report == null)
                    const AppCard(child: Text('Mahasiswa belum mengunggah laporan.', style: TextStyle(color: AppColors.textMuted)))
                  else
                    _ReportCard(r: report, onApprove: () => _approveReport(report), onRevisi: () => _revisiReport(report)),
                ],

                const SizedBox(height: 8),
                // Nilai
                AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    const Text('Rata-rata Nilai: ', style: TextStyle(color: AppColors.textSecondary)),
                    Text('${nilai['overall'] ?? '-'}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: AppColors.primary)),
                  ]),
                  const SizedBox(height: 10),
                  SizedBox(width: double.infinity, child: ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(46)),
                    onPressed: () async {
                      final saved = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => DosenNilaiScreen(studentId: _sid, studentName: name)));
                      if (saved == true) _reload();
                    },
                    icon: const Icon(Icons.edit_outlined, size: 18),
                    label: const Text('Input / Ubah Nilai'),
                  )),
                ])),
                const SizedBox(height: 24),
              ],
            );
          }),
          ),
        ),
      ]),
    );
  }

  // ── Actions ──
  Future<void> _approveGuidance(Map g) async {
    final note = await showNoteDialog(context, title: 'Setujui Bimbingan', hint: 'Catatan (opsional)...', okLabel: 'Setujui');
    if (note == null) return;
    _action(() => ApiClient.post('/dosen/mahasiswa/$_sid/bimbingan/${g['id']}/approve', token: _token, body: {'note': note}));
  }

  Future<void> _revisiGuidance(Map g) async {
    final note = await showNoteDialog(context, title: 'Minta Revisi Bimbingan', requiredNote: true, okLabel: 'Kirim Revisi');
    if (note == null) return;
    _action(() => ApiClient.post('/dosen/mahasiswa/$_sid/bimbingan/${g['id']}/revisi', token: _token, body: {'note': note}));
  }

  Future<void> _approveReport(Map r) async {
    final note = await showNoteDialog(context, title: 'Setujui Laporan', hint: 'Catatan (opsional)...', okLabel: 'Setujui');
    if (note == null) return;
    _action(() => ApiClient.post('/dosen/mahasiswa/$_sid/laporan/${r['id']}/approve', token: _token, body: {'note': note}));
  }

  Future<void> _revisiReport(Map r) async {
    final note = await showNoteDialog(context, title: 'Minta Revisi Laporan', requiredNote: true, okLabel: 'Kirim Revisi');
    if (note == null) return;
    _action(() => ApiClient.post('/dosen/mahasiswa/$_sid/laporan/${r['id']}/revisi', token: _token, body: {'note': note}));
  }

  Future<void> _logbookNote(Map l) async {
    final note = await showNoteDialog(context, title: 'Catatan Log Book', initial: l['lecturer_note'] ?? '', hint: 'Tulis catatan...');
    if (note == null) return;
    _action(() => ApiClient.post('/dosen/mahasiswa/$_sid/logbook/${l['id']}/note', token: _token, body: {'note': note}));
  }

  String _initials(String n) {
    final p = n.trim().split(RegExp(r'\s+'));
    return p.take(2).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
  }
}

class _GuidanceCard extends StatelessWidget {
  final Map g;
  final VoidCallback onApprove, onRevisi;
  const _GuidanceCard({required this.g, required this.onApprove, required this.onRevisi});
  @override
  Widget build(BuildContext context) {
    final status = (g['status'] ?? '').toString();
    return AppCard(
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Expanded(child: Text(g['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700))),
          StatusChip(status),
        ]),
        const SizedBox(height: 4),
        Text(g['date'] ?? '', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
        const SizedBox(height: 8),
        Text(g['activity'] ?? '', style: const TextStyle(fontSize: 13, height: 1.5)),
        if ((g['file_url'] ?? '').toString().isNotEmpty)
          Align(
            alignment: Alignment.centerLeft,
            child: TextButton.icon(
              onPressed: () => _openFileUrl(context, '${g['file_url']}'),
              icon: const Icon(Icons.attach_file, size: 18),
              label: const Text('Lihat File Bimbingan'),
            ),
          ),
        if ((g['lecturer_note'] ?? '').toString().isNotEmpty)
          Padding(padding: const EdgeInsets.only(top: 8), child: Text('Catatan: ${g['lecturer_note']}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary))),
        if (status == 'pending')
          Padding(
            padding: const EdgeInsets.only(top: 10),
            child: Row(children: [
              Expanded(child: ElevatedButton(onPressed: onApprove, style: ElevatedButton.styleFrom(minimumSize: const Size(0, 42)), child: const Text('Setujui'))),
              const SizedBox(width: 8),
              Expanded(child: OutlinedButton(onPressed: onRevisi, style: OutlinedButton.styleFrom(foregroundColor: AppColors.error, side: const BorderSide(color: AppColors.error), minimumSize: const Size(0, 42)), child: const Text('Revisi'))),
            ]),
          ),
      ]),
    );
  }
}

class _ReportCard extends StatelessWidget {
  final Map r;
  final VoidCallback onApprove, onRevisi;
  const _ReportCard({required this.r, required this.onApprove, required this.onRevisi});
  @override
  Widget build(BuildContext context) {
    final status = (r['status'] ?? '').toString();
    return AppCard(
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Expanded(child: Text(r['title'] ?? 'Laporan Akhir', style: const TextStyle(fontWeight: FontWeight.w700))),
          StatusChip(status),
        ]),
        if ((r['file_url'] ?? '').toString().isNotEmpty)
          Align(
            alignment: Alignment.centerLeft,
            child: TextButton.icon(
              onPressed: () => _openFileUrl(context, '${r['file_url']}'),
              icon: const Icon(Icons.attach_file, size: 18),
              label: const Text('Lihat File Laporan'),
            ),
          ),
        if ((r['lecturer_note'] ?? '').toString().isNotEmpty)
          Padding(padding: const EdgeInsets.only(top: 8), child: Text('Catatan: ${r['lecturer_note']}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary))),
        if (status == 'pending')
          Padding(
            padding: const EdgeInsets.only(top: 10),
            child: Row(children: [
              Expanded(child: ElevatedButton(onPressed: onApprove, style: ElevatedButton.styleFrom(minimumSize: const Size(0, 42)), child: const Text('Setujui'))),
              const SizedBox(width: 8),
              Expanded(child: OutlinedButton(onPressed: onRevisi, style: OutlinedButton.styleFrom(foregroundColor: AppColors.error, side: const BorderSide(color: AppColors.error), minimumSize: const Size(0, 42)), child: const Text('Revisi'))),
            ]),
          ),
      ]),
    );
  }
}

class _LogbookCard extends StatelessWidget {
  final Map l;
  final VoidCallback onNote;
  const _LogbookCard({required this.l, required this.onNote});
  @override
  Widget build(BuildContext context) {
    final hasNote = (l['lecturer_note'] ?? '').toString().isNotEmpty;
    return AppCard(
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Expanded(child: Text(l['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700))),
          Text(l['date'] ?? '', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
        ]),
        const SizedBox(height: 6),
        Text(l['activity'] ?? '', style: const TextStyle(fontSize: 13, height: 1.5)),
        if (hasNote)
          Padding(padding: const EdgeInsets.only(top: 8), child: Text('Catatan Anda: ${l['lecturer_note']}', style: const TextStyle(fontSize: 12, color: AppColors.primary))),
        Align(
          alignment: Alignment.centerRight,
          child: TextButton.icon(
            onPressed: onNote,
            icon: const Icon(Icons.edit_note, size: 20),
            label: Text(hasNote ? 'Ubah Catatan' : 'Beri Catatan'),
          ),
        ),
      ]),
    );
  }
}
