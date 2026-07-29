import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class NilaiScreen extends StatefulWidget {
  const NilaiScreen({super.key});
  @override
  State<NilaiScreen> createState() => _NilaiScreenState();
}

class _NilaiScreenState extends State<NilaiScreen> {
  late Future<Map<String, dynamic>> _future;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/mahasiswa/nilai', token: _token);
    return Map<String, dynamic>.from(data);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Nilai Akhir', subtitle: 'Rekap penilaian magang'),
        Expanded(
          child: FutureBuilder<Map<String, dynamic>>(
            future: _future,
            builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return ErrorRetry(message: '${snap.error}', onRetry: () => setState(() { _future = _load(); }));
          }
          final nilai = snap.data!['nilai'] as Map<String, dynamic>?;
          if (nilai == null) {
            return const EmptyState('Belum ada data nilai / magang aktif.', icon: Icons.grade_outlined);
          }
          final lecturer = Map<String, dynamic>.from(nilai['lecturer'] ?? {});
          final industry = Map<String, dynamic>.from(nilai['industry'] ?? {});
          final lecComps = List<Map<String, dynamic>>.from(lecturer['components'] ?? []);
          final indComps = List<Map<String, dynamic>>.from(industry['components'] ?? []);
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              NilaiHero('${nilai['final'] ?? '-'}'),
              Padding(
                padding: const EdgeInsets.only(top: 6, bottom: 2),
                child: Text(
                  nilai['final'] == null
                      ? 'Menunggu penilaian lengkap dari dosen & industri.'
                      : 'Rata dosen ${lecturer['average']} + rata industri ${industry['average']}  (skala 1–10)',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 12, color: AppColors.textMuted),
                ),
              ),
              _section('Penilaian Dosen Pembimbing', lecturer['average'], lecComps, showWeight: true),
              _section('Penilaian Pembimbing Industri', industry['average'], indComps, showWeight: false),
            ],
          );
            },
          ),
        ),
      ]),
    );
  }

  Widget _section(String title, dynamic avg, List<Map<String, dynamic>> comps, {required bool showWeight}) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Padding(
        padding: const EdgeInsets.fromLTRB(2, 16, 2, 8),
        child: Row(children: [
          Expanded(child: Text(title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14))),
          _badge(avg),
        ]),
      ),
      ...comps.map((it) => AppCard(child: Row(children: [
            Expanded(child: Text(
              showWeight && it['weight'] != null
                  ? '${it['name']}  (bobot ${(it['weight'] as num).toInt()}%)'
                  : '${it['name'] ?? ''}',
              style: const TextStyle(fontWeight: FontWeight.w600))),
            _badge(it['avg']),
          ]))),
    ]);
  }

  Widget _badge(dynamic v) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
        decoration: BoxDecoration(
          color: v == null ? AppColors.warm : AppColors.blueTint,
          borderRadius: BorderRadius.circular(999),
        ),
        child: Text(v?.toString() ?? 'Belum dinilai',
            style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: v == null ? AppColors.textMuted : AppColors.primary)),
      );
}
