import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/file_helper.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class MagangSayaScreen extends StatefulWidget {
  const MagangSayaScreen({super.key});
  @override
  State<MagangSayaScreen> createState() => _MagangSayaScreenState();
}

class _MagangSayaScreenState extends State<MagangSayaScreen> {
  late Future<Map<String, dynamic>> _future;
  bool _busy = false;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/mahasiswa/magang-saya', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() => _future = _load());

  Future<void> _uploadCertificate() async {
    final path = await pickFilePath(extensions: ['pdf', 'jpg', 'jpeg', 'png']);
    if (path == null) return;
    setState(() => _busy = true);
    try {
      await ApiClient.upload('/mahasiswa/magang-saya/sertifikat', fileField: 'certificate', filePath: path, token: _token);
      if (mounted) showMessage(context, 'Sertifikat berhasil diunggah.');
      _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _requestFinish() async {
    setState(() => _busy = true);
    try {
      await ApiClient.post('/mahasiswa/magang-saya/ajukan-selesai', token: _token);
      if (mounted) showMessage(context, 'Pengajuan selesai magang dikirim.');
      _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Magang Saya', subtitle: 'Status magang & syarat selesai'),
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
            final internship = snap.data!['internship'] as Map<String, dynamic>?;
            if (internship == null) {
              return const EmptyState('Belum ada magang aktif.', icon: Icons.work_outline);
            }
            final checklist = List<Map<String, dynamic>>.from(snap.data!['finish_checklist'] ?? []);
            final canFinish = snap.data!['can_request_finish'] == true;
            final finishRequested = internship['finish_requested'] == true;
            final hasCert = (internship['certificate_url'] ?? '').toString().isNotEmpty;

            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                const SectionTitle('Detail Penempatan'),
                AppCard(child: Column(children: [
                  InfoRow('Perusahaan', internship['company'] ?? '-'),
                  InfoRow('Posisi', internship['position'] ?? '-'),
                  InfoRow('Dosen Pembimbing', internship['lecturer'] ?? '-'),
                  InfoRow('Pembimbing Industri', internship['lecturer_industry'] ?? '-'),
                  InfoRow('Status', internship['is_finished'] == true ? 'Selesai' : 'Aktif'),
                ])),

                const SectionTitle('Sertifikat Magang'),
                AppCard(child: Row(children: [
                  Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(
                      color: hasCert ? AppColors.successBg : AppColors.warnBg,
                      borderRadius: BorderRadius.circular(11),
                    ),
                    child: Icon(Icons.workspace_premium_outlined, size: 20,
                        color: hasCert ? AppColors.success : AppColors.warnText),
                  ),
                  const SizedBox(width: 12),
                  Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(hasCert ? 'Sertifikat sudah diunggah' : 'Belum ada sertifikat',
                        style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600)),
                    const Text('Format PDF/JPG/PNG.', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                  ])),
                  TextButton(
                    onPressed: _busy ? null : _uploadCertificate,
                    child: Text(hasCert ? 'Ganti' : 'Unggah'),
                  ),
                ])),

                if (internship['is_finished'] != true) ...[
                  const SectionTitle('Selesai Magang'),
                  AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    ...checklist.map((c) {
                      final met = c['met'] == true;
                      final hint = (c['hint'] ?? c['note'] ?? c['detail'] ?? '').toString();
                      return Padding(
                        padding: const EdgeInsets.symmetric(vertical: 7),
                        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Container(
                            width: 22, height: 22,
                            decoration: BoxDecoration(
                              color: met ? AppColors.successBg : AppColors.errorBg,
                              shape: BoxShape.circle,
                            ),
                            child: Icon(met ? Icons.check : Icons.close, size: 13,
                                color: met ? AppColors.success : AppColors.error),
                          ),
                          const SizedBox(width: 10),
                          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            Text(c['label'] ?? '', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600)),
                            if (hint.isNotEmpty) ...[
                              const SizedBox(height: 1),
                              Text(hint, style: TextStyle(fontSize: 10.5, color: met ? AppColors.textMuted : AppColors.error)),
                            ],
                          ])),
                        ]),
                      );
                    }),
                    const SizedBox(height: 12),
                    if (finishRequested)
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(color: AppColors.warnBg, borderRadius: BorderRadius.circular(8)),
                        child: const Text('Menunggu ACC Kaprodi.', style: TextStyle(color: AppColors.warnText)),
                      )
                    else
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: (canFinish && !_busy) ? _requestFinish : null,
                          child: const Text('Ajukan Selesai Magang'),
                        ),
                      ),
                    if (!canFinish && !finishRequested)
                      const Padding(padding: EdgeInsets.only(top: 8), child: Text('Lengkapi semua syarat di atas terlebih dahulu.', style: TextStyle(color: AppColors.textMuted, fontSize: 12))),
                  ])),
                ],
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
}
