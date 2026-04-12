import 'package:flutter/material.dart';

class KaprodiHome extends StatefulWidget {
  const KaprodiHome({super.key});

  @override
  State<KaprodiHome> createState() => _KaprodiHomeState();
}

class _KaprodiHomeState extends State<KaprodiHome> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFEEF1F8),
      body: SingleChildScrollView(
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
                  // Profile row with greeting
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
                          const Text(
                            'Idhawati H., M.Kom',
                            style: TextStyle(
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
                            child: Stack(
                              alignment: Alignment.center,
                              children: [
                                const Text(
                                  'IH',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 15,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                                Positioned(
                                  top: 0,
                                  right: 0,
                                  child: Container(
                                    width: 9,
                                    height: 9,
                                    decoration: const BoxDecoration(
                                      color: Color(0xFFFBBF24),
                                      shape: BoxShape.circle,
                                      border: Border.fromBorderSide(
                                        BorderSide(
                                          color: Color(0xFF1A1A3E),
                                          width: 2,
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 5,
                            ),
                            decoration: BoxDecoration(
                              color: const Color.fromARGB(38, 251, 191, 36),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.fromBorderSide(
                                BorderSide(
                                  color: const Color.fromARGB(77, 251, 191, 36),
                                ),
                              ),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 7,
                                  height: 7,
                                  decoration: const BoxDecoration(
                                    color: Color(0xFFFBBF24),
                                    shape: BoxShape.circle,
                                  ),
                                ),
                                const SizedBox(width: 6),
                                const Text(
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
                  // Search bar
                  Container(
                    height: 40,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      color: Colors.white,
                      border: Border.all(
                        color: const Color(0xFFE5E7EB),
                        width: 1,
                      ),
                    ),
                    child: TextField(
                      decoration: InputDecoration(
                        hintText: 'Cari mahasiswa, dosen, industri...',
                        hintStyle: const TextStyle(
                          color: Color(0xFF9CA3AF),
                          fontSize: 13,
                        ),
                        prefixIcon: const Icon(
                          Icons.search,
                          size: 18,
                          color: Color(0xFF9CA3AF),
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          vertical: 0,
                          horizontal: 12,
                        ),
                        border: InputBorder.none,
                        isDense: true,
                      ),
                      style: const TextStyle(
                        color: Color(0xFF1F2937),
                        fontSize: 13,
                      ),
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
                  // Stat Cards
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
                        value: '48',
                        subtitle: '38 aktif · 6 selesai ›',
                        subtitleColor: const Color(0xFF3D5AF1),
                      ),
                      _buildStatCard(
                        title: 'Belum Dospem',
                        value: '5',
                        subtitle: 'Tugaskan sekarang ›',
                        subtitleColor: const Color(0xFFE53E3E),
                        valueColor: const Color(0xFFE53E3E),
                      ),
                      _buildStatCard(
                        title: 'Verif. Industri',
                        value: '3',
                        subtitle: 'Akun industri baru ›',
                        subtitleColor: const Color(0xFF7C3AED),
                        valueColor: const Color(0xFF7C3AED),
                      ),
                      _buildStatCard(
                        title: 'Seminar',
                        value: '4',
                        subtitle: 'Terjadwal ›',
                        subtitleColor: const Color(0xFF1A4BBB),
                        valueColor: const Color(0xFF1A4BBB),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  // Quick Actions
                  const Text(
                    'Aksi Cepat',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1A2050),
                    ),
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
                      _buildQuickActionCard(
                        icon: Icons.person_add,
                        title: 'Tugaskan Dosen',
                        subtitle: '5 belum ada',
                        bgColor: const Color(0xFFFFF8E1),
                        iconColor: const Color(0xFFF59E0B),
                      ),
                      _buildQuickActionCard(
                        icon: Icons.verified,
                        title: 'Verifikasi Industri',
                        subtitle: '3 menunggu',
                        bgColor: const Color(0xFFF3E8FF),
                        iconColor: const Color(0xFF7C3AED),
                      ),
                      _buildQuickActionCard(
                        icon: Icons.calendar_today,
                        title: 'Seminar',
                        subtitle: '4 terjadwal',
                        bgColor: const Color(0xFFE8F0FE),
                        iconColor: const Color(0xFF1A4BBB),
                      ),
                      _buildQuickActionCard(
                        icon: Icons.receipt,
                        title: 'Data Dosen',
                        subtitle: '12 dosen',
                        bgColor: const Color(0xFFE6F4EA),
                        iconColor: const Color(0xFF1E6E3E),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  // Latest Students
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Mahasiswa Terbaru',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1A2050),
                        ),
                      ),
                      Text(
                        'Lihat semua',
                        style: TextStyle(
                          fontSize: 11,
                          color: const Color(0xFF3D5AF1),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  _buildStudentCard(
                    initials: 'MZ',
                    name: 'Muhammad Zaki A.P.',
                    nimAndCompany:
                        '3.34.23.2.15 · IK-3C · PT. Telkom Indonesia',
                    advisor: 'Kuwat Santoso, M.Kom',
                    status: 'Aktif',
                    statusColor: const Color(0xFFE8F0FE),
                    statusTextColor: const Color(0xFF1A3A8E),
                    initialsColor: const Color(0xFFE8F0FE),
                  ),
                  const SizedBox(height: 6),
                  _buildStudentCard(
                    initials: 'VC',
                    name: 'Vincencius Kurnia Putra',
                    nimAndCompany: '3.34.23.2.24 · IK-3C · PT. BNI',
                    status: 'Belum Dospem',
                    statusColor: const Color(0xFFFCE8E6),
                    statusTextColor: const Color(0xFF9B2F2F),
                    initialsColor: const Color(0xFFFCE8E6),
                    isWithoutAdvisor: true,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required String subtitle,
    required Color subtitleColor,
    Color? valueColor,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(13),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.all(8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: valueColor ?? const Color(0xFF1A1A3E),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            title,
            style: const TextStyle(
              fontSize: 10,
              color: Color(0xFF8A9BC0),
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            subtitle,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: subtitleColor,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color bgColor,
    required Color iconColor,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.all(5),
      child: Row(
        children: [
          Container(
            width: 24,
            height: 24,
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(5),
            ),
            child: Icon(icon, color: iconColor, size: 12),
          ),
          const SizedBox(width: 4),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1A2050),
                  ),
                ),
                Text(
                  subtitle,
                  style: const TextStyle(
                    fontSize: 9,
                    color: Color(0xFF8A9BC0),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStudentCard({
    required String initials,
    required String name,
    required String nimAndCompany,
    required String status,
    required Color statusColor,
    required Color statusTextColor,
    required Color initialsColor,
    String? advisor,
    bool isWithoutAdvisor = false,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.all(13),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: initialsColor,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    initials,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1A3A8E),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A2050),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      nimAndCompany,
                      style: const TextStyle(
                        fontSize: 10,
                        color: Color(0xFF8A9BC0),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 9,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: statusColor,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  status,
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w700,
                    color: statusTextColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Container(
            color: const Color(0xFFEEF0FA),
            height: 0.5,
          ),
          const SizedBox(height: 8),
          if (advisor != null)
            Row(
              children: [
                const Text(
                  'Dospem: ',
                  style: TextStyle(
                    fontSize: 10,
                    color: Color(0xFF8A9BC0),
                    fontWeight: FontWeight.w500,
                  ),
                ),
                Expanded(
                  child: Text(
                    advisor,
                    style: const TextStyle(
                      fontSize: 10,
                      color: Color(0xFF1A2050),
                      fontWeight: FontWeight.w700,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            )
          else if (isWithoutAdvisor)
            Row(
              children: [
                Expanded(
                  child: Text(
                    'Belum ada dosen pembimbing',
                    style: const TextStyle(
                      fontSize: 10,
                      color: Color(0xFFEA4335),
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: () {},
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1A1A3E),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Text(
                      'Tugaskan',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),
        ],
      ),
    );
  }
}
