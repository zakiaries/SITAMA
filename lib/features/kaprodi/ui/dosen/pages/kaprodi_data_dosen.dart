import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/kaprodi/ui/bloc/kaprodi_cubit.dart';
import 'package:sitama/service_locator.dart';
import 'kaprodi_dosen_detail.dart';

class KaprodiDataDosen extends StatefulWidget {
  const KaprodiDataDosen({super.key});

  @override
  State<KaprodiDataDosen> createState() => _KaprodiDataDosenState();
}

class _KaprodiDataDosenState extends State<KaprodiDataDosen> {
  final _colors = [
    const Color(0xFF3D5AF1),
    const Color(0xFF7C3AED),
    const Color(0xFFE53E3E),
    const Color(0xFF1A4BBB),
    const Color(0xFF1E6E3E),
  ];

  @override
  void initState() {
    super.initState();
    sl<KaprodiCubit>().loadDosen();
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider.value(
      value: sl<KaprodiCubit>(),
      child: Scaffold(
        backgroundColor: const Color(0xFFF5F6FA),
        body: NestedScrollView(
          headerSliverBuilder: (context, _) => [
            SliverToBoxAdapter(
              child: ClipRRect(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.fromLTRB(18, 12, 18, 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 16),
                      const Text('Data Dosen',
                          style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 16),
                      Container(
                        height: 40,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          color: Colors.white,
                          border: Border.all(color: const Color(0xFFE5E7EB)),
                        ),
                        child: TextField(
                          onSubmitted: (q) => sl<KaprodiCubit>().loadDosen(search: q),
                          decoration: const InputDecoration(
                            hintText: 'Cari nama dosen...',
                            hintStyle: TextStyle(color: Color(0xFF9CA3AF), fontSize: 13),
                            prefixIcon: Icon(Icons.search, size: 18, color: Color(0xFF9CA3AF)),
                            contentPadding: EdgeInsets.symmetric(vertical: 0, horizontal: 12),
                            border: InputBorder.none,
                            isDense: true,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
          body: BlocBuilder<KaprodiCubit, KaprodiState>(
            builder: (context, state) {
              if (state is KaprodiLoading) {
                return const Center(child: CircularProgressIndicator());
              }
              if (state is KaprodiError) {
                return Center(child: Text(state.message,
                    style: const TextStyle(color: Colors.red)));
              }
              final dosen = state is KaprodiDosenLoaded ? state.dosen : [];
              if (dosen.isEmpty) {
                return const Center(
                  child: Text('Tidak ada data dosen',
                      style: TextStyle(fontSize: 14, color: Color(0xFF8A9BC0))),
                );
              }
              return SingleChildScrollView(
                padding: const EdgeInsets.all(14),
                child: Column(
                  children: List.generate(dosen.length, (i) {
                    final d   = dosen[i] as Map<String, dynamic>;
                    final clr = _colors[i % _colors.length];
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 14),
                      child: GestureDetector(
                        onTap: () async {
                          await sl<KaprodiCubit>().loadDosenStudents(d['id'] as int);
                          if (!context.mounted) return;
                          final cubitState = sl<KaprodiCubit>().state;
                          if (cubitState is KaprodiDosenStudentsLoaded) {
                            final students = (cubitState.data['students'] as List)
                                .cast<Map<String, dynamic>>();
                            Navigator.push(context, MaterialPageRoute(
                              builder: (_) => KaprodiDosenDetail(
                                dosenName: d['name'] as String,
                                students: students,
                              ),
                            ));
                          }
                        },
                        child: _buildDosenCard(d, clr),
                      ),
                    );
                  }),
                ),
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildDosenCard(Map<String, dynamic> d, Color clr) {
    final name     = d['name'] as String? ?? '';
    final username = d['username'] as String? ?? '';
    final count    = d['student_count'] as int? ?? 0;
    final initials = name.trim().split(' ').take(2)
        .map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
      ),
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 50, height: 50,
            decoration: BoxDecoration(
              color: clr.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Center(
              child: Text(initials,
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: clr)),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF1A2050))),
                const SizedBox(height: 2),
                Text('Username: $username',
                    style: const TextStyle(fontSize: 9, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w500)),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('$count/20 Mahasiswa',
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Color(0xFF8A9BC0))),
                    SizedBox(
                      width: 100,
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: LinearProgressIndicator(
                          value: (count / 20).clamp(0.0, 1.0),
                          minHeight: 6,
                          backgroundColor: const Color(0xFFD0D6EB),
                          valueColor: AlwaysStoppedAnimation<Color>(clr),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 16),
          Column(
            children: [
              Container(
                width: 52, height: 52,
                decoration: BoxDecoration(color: clr, borderRadius: BorderRadius.circular(10)),
                child: Center(
                  child: Text('$count',
                      style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: Colors.white)),
                ),
              ),
              const SizedBox(height: 4),
              const Text('Mahasiswa',
                  style: TextStyle(fontSize: 9, fontWeight: FontWeight.w600, color: Color(0xFF8A9BC0))),
            ],
          ),
        ],
      ),
    );
  }
}
