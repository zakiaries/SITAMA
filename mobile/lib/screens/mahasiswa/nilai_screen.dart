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
            return ErrorRetry(message: '${snap.error}', onRetry: () => setState(() => _future = _load()));
          }
          final nilai = snap.data!['nilai'] as Map<String, dynamic>?;
          if (nilai == null) {
            return const EmptyState('Belum ada data nilai / magang aktif.', icon: Icons.grade_outlined);
          }
          final items = List<Map<String, dynamic>>.from(nilai['items'] ?? []);
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              NilaiHero('${nilai['overall'] ?? '-'}'),
              const SectionTitle('Rincian per Komponen'),
              ...items.map((it) => AppCard(child: Row(children: [
                    Expanded(child: Text(it['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600))),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                      decoration: BoxDecoration(
                        color: it['avg'] == null ? AppColors.warm : AppColors.blueTint,
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(it['avg']?.toString() ?? 'Belum dinilai',
                          style: TextStyle(fontWeight: FontWeight.w700, color: it['avg'] == null ? AppColors.textMuted : AppColors.primary)),
                    ),
                  ]))),
            ],
          );
            },
          ),
        ),
      ]),
    );
  }
}
