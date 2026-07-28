import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/file_helper.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class LaporanScreen extends StatefulWidget {
  const LaporanScreen({super.key});
  @override
  State<LaporanScreen> createState() => _LaporanScreenState();
}

class _LaporanScreenState extends State<LaporanScreen> {
  late Future<Map<String, dynamic>> _future;
  bool _uploading = false;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/mahasiswa/laporan', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() { _future = _load(); });

  Future<void> _upload() async {
    final path = await pickFilePath(extensions: ['pdf', 'doc', 'docx']);
    if (path == null) return;
    setState(() => _uploading = true);
    try {
      await ApiClient.upload('/mahasiswa/laporan', fileField: 'file', filePath: path, token: _token);
      if (mounted) showMessage(context, 'Laporan berhasil diunggah.');
      _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Laporan Akhir', subtitle: 'Unggah & pantau laporan'),
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
            final report = snap.data!['report'] as Map<String, dynamic>?;
            final status = (report?['status'] ?? '').toString();
            final canUpload = report == null || status == 'rejected';

            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                if (report == null)
                  const AppCard(child: Text('Anda belum mengunggah laporan akhir.', style: TextStyle(color: AppColors.textMuted)))
                else
                  AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Row(children: [
                      Expanded(child: Text(report['title'] ?? 'Laporan Akhir', style: const TextStyle(fontWeight: FontWeight.w700))),
                      StatusChip(status),
                    ]),
                    if ((report['lecturer_note'] ?? '').toString().isNotEmpty) ...[
                      const SizedBox(height: 10),
                      Text('Catatan dosen:', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12, fontWeight: FontWeight.w600)),
                      Text('${report['lecturer_note']}', style: const TextStyle(fontSize: 13)),
                    ],
                    if (status == 'approved')
                      const Padding(padding: EdgeInsets.only(top: 10), child: Text('✓ Laporan sudah disetujui dosen.', style: TextStyle(color: AppColors.success, fontWeight: FontWeight.w600))),
                  ])),
                const SizedBox(height: 8),
                if (canUpload)
                  ElevatedButton.icon(
                    onPressed: _uploading ? null : _upload,
                    icon: _uploading
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                        : const Icon(Icons.upload_file),
                    label: Text(report == null ? 'Unggah Laporan (PDF/Word)' : 'Unggah Ulang (Revisi)'),
                  )
                else
                  const Text('Laporan sedang diproses / sudah disetujui.', style: TextStyle(color: AppColors.textMuted, fontSize: 13)),
              ],
            );
          },
          ),
          ),
        ),
      ]),
    );
  }
}
