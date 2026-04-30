import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/kaprodi/ui/bloc/kaprodi_cubit.dart';
import 'package:sitama/service_locator.dart';

class KaprodiHome extends StatefulWidget {
  const KaprodiHome({super.key});

  @override
  State<KaprodiHome> createState() => _KaprodiHomeState();
}

class _KaprodiHomeState extends State<KaprodiHome> {
  @override
  void initState() {
    super.initState();
    sl<KaprodiCubit>().loadHome();
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider.value(
      value: sl<KaprodiCubit>(),
      child: BlocBuilder<KaprodiCubit, KaprodiState>(
        builder: (context, state) {
          final profile   = state is KaprodiHomeLoaded ? state.profile   : <String, dynamic>{};
          final dashboard = state is KaprodiHomeLoaded ? state.dashboard : <String, dynamic>{};
          final isLoading = state is KaprodiLoading;

          final name     = profile['name'] as String? ?? 'Kaprodi';
          final initials = name.trim().split(' ').take(2)
              .map((w) => w.isNotEmpty ? w[0].toUpperCase() : '')
              .join();

          final totalMahasiswa = dashboard['total_mahasiswa'] ?? 0;
          final belumDospem    = dashboard['belum_dospem']    ?? 0;
          final verifIndustri  = dashboard['verif_industri']  ?? 0;
          final aktif          = dashboard['aktif']           ?? 0;
          final selesai        = dashboard['selesai']         ?? 0;

          return Scaffold(
            backgroundColor: const Color(0xFFEEF1F8),
            body: isLoading
                ? const Center(child: CircularProgressIndicator())
                : SingleChildScrollView(
                    child: Column(
                      children: [
                        // Header
                        Container(
                          width: double.infinity,
                          decoration: const BoxDecoration(
                            color: Color(0xFF1A1A3E),
                            borderRadius: BorderRadius.only(
                              bottomLeft: Radius.circular(28),
                              bottomRight: Radius.circular(28),
                            ),
                          ),
                          padding: const EdgeInsets.fromLTRB(18, 12, 18, 16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              SizedBox(height: MediaQuery.of(context).padding.top + 16),
                              Row(
                                children: [
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Ketua Program Studi',
                                        style: TextStyle(
                                          color: Color(0xFF9FA8DA),
                                          fontSize: 11,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        name,
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 19,
                                          fontWeight: FontWeight.w800,
                                        ),
                                      ),
                                      const SizedBox(height: 3),
                                      const Text(
                                        'Teknik Informatika · Polines',
                                        style: TextStyle(
                                          color: Color(0xFF9FA8DA),
                                          fontSize: 11,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ],
                                  ),
                                  const Spacer(),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Container(
                                        width: 46,
                                        height: 46,
                                        decoration: const BoxDecoration(
                                          shape: BoxShape.circle,
                                          color: Color.fromARGB(38, 255, 255, 255),
                                          border: Border.fromBorderSide(
                                            BorderSide(
                                              color: Color.fromARGB(64, 255, 255, 255),
                                              width: 2,
                                            ),
                                          ),
                                        ),
                                        child: Center(
                                          child: Text(
                                            initials,
                                            style: const TextStyle(
                                              color: Colors.white,
                                              fontSize: 15,
                                              fontWeight: FontWeight.w800,
                                            ),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(height: 6),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                                        decoration: BoxDecoration(
                                          color: const Color.fromARGB(38, 251, 191, 36),
                                          borderRadius: BorderRadius.circular(20),
                                          border: const Border.fromBorderSide(
                                            BorderSide(color: Color.fromARGB(77, 251, 191, 36)),
                                          ),
                                        ),
                                        child: const Row(
                                          children: [
                                            SizedBox(
                                              width: 7, height: 7,
                                              child: DecoratedBox(
                                                decoration: BoxDecoration(
                                                  color: Color(0xFFFBBF24),
                                                  shape: BoxShape.circle,
                                                ),
                                              ),
                                            ),
                                            SizedBox(width: 6),
                                            Text(
                                              'Superadmin',
                                              style: TextStyle(
                                                color: Color(0xFFFBBF24),
                                                fontSize: 10,
                                                fontWeight: FontWeight.w700,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                              const SizedBox(height: 14),
                              Container(
                                height: 40,
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(8),
                                  color: Colors.white,
                                  border: Border.all(color: const Color(0xFFE5E7EB)),
                                ),
                                child: const TextField(
                                  decoration: InputDecoration(
                                    hintText: 'Cari mahasiswa, dosen, industri...',
                                    hintStyle: TextStyle(color: Color(0xFF9CA3AF), fontSize: 13),
                                    prefixIcon: Icon(Icons.search, size: 18, color: Color(0xFF9CA3AF)),
                                    contentPadding: EdgeInsets.symmetric(vertical: 0, horizontal: 12),
                                    border: InputBorder.none,
                                    isDense: true,
                                  ),
                                  style: TextStyle(color: Color(0xFF1F2937), fontSize: 13),
                                ),
                              ),
                            ],
                          ),
                        ),
                        // Content
                        Padding(
                          padding: const EdgeInsets.all(10),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              GridView.count(
                                crossAxisCount: 2,
                                mainAxisSpacing: 5,
                                crossAxisSpacing: 5,
                                shrinkWrap: true,
                                childAspectRatio: 2.2,
                                physics: const NeverScrollableScrollPhysics(),
                                children: [
                                  _buildStatCard(
                                    title: 'Total Mahasiswa',
                                    value: '$totalMahasiswa',
                                    subtitle: '$aktif aktif · $selesai selesai ›',
                                    subtitleColor: const Color(0xFF3D5AF1),
                                  ),
                                  _buildStatCard(
                                    title: 'Belum Dospem',
                                    value: '$belumDospem',
                                    subtitle: 'Tugaskan sekarang ›',
                                    subtitleColor: const Color(0xFFE53E3E),
                                    valueColor: const Color(0xFFE53E3E),
                                  ),
                                  _buildStatCard(
                                    title: 'Verif. Industri',
                                    value: '$verifIndustri',
                                    subtitle: 'Akun industri baru ›',
                                    subtitleColor: const Color(0xFF7C3AED),
                                    valueColor: const Color(0xFF7C3AED),
                                  ),
                                  _buildStatCard(
                                    title: 'Seminar',
                                    value: '$aktif',
                                    subtitle: 'Terjadwal ›',
                                    subtitleColor: const Color(0xFF1A4BBB),
                                    valueColor: const Color(0xFF1A4BBB),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 10),
                              const Text(
                                'Aksi Cepat',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF1A2050)),
                              ),
                              const SizedBox(height: 4),
                              GridView.count(
                                crossAxisCount: 2,
                                mainAxisSpacing: 3,
                                crossAxisSpacing: 3,
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                childAspectRatio: 2.8,
                                children: [
                                  _buildQuickActionCard(icon: Icons.person_add, title: 'Tugaskan Dosen',
                                      subtitle: '$belumDospem belum ada', bgColor: const Color(0xFFFFF8E1), iconColor: const Color(0xFFF59E0B)),
                                  _buildQuickActionCard(icon: Icons.verified, title: 'Verifikasi Industri',
                                      subtitle: '$verifIndustri menunggu', bgColor: const Color(0xFFF3E8FF), iconColor: const Color(0xFF7C3AED)),
                                  _buildQuickActionCard(icon: Icons.calendar_today, title: 'Seminar',
                                      subtitle: '$aktif terjadwal', bgColor: const Color(0xFFE8F0FE), iconColor: const Color(0xFF1A4BBB)),
                                  _buildQuickActionCard(icon: Icons.receipt, title: 'Data Dosen',
                                      subtitle: 'Lihat semua', bgColor: const Color(0xFFE6F4EA), iconColor: const Color(0xFF1E6E3E)),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
          );
        },
      ),
    );
  }

  Widget _buildStatCard({required String title, required String value,
      required String subtitle, required Color subtitleColor, Color? valueColor}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(13),
        border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
      ),
      padding: const EdgeInsets.all(8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: valueColor ?? const Color(0xFF1A1A3E))),
          const SizedBox(height: 2),
          Text(title, style: const TextStyle(fontSize: 10, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w600)),
          const SizedBox(height: 2),
          Text(subtitle, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: subtitleColor)),
        ],
      ),
    );
  }

  Widget _buildQuickActionCard({required IconData icon, required String title,
      required String subtitle, required Color bgColor, required Color iconColor}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
      ),
      padding: const EdgeInsets.all(5),
      child: Row(
        children: [
          Container(
            width: 24, height: 24,
            decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(5)),
            child: Icon(icon, color: iconColor, size: 12),
          ),
          const SizedBox(width: 4),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(title, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF1A2050))),
                Text(subtitle, style: const TextStyle(fontSize: 9, color: Color(0xFF8A9BC0))),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
