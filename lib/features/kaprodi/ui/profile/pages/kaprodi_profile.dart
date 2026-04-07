import 'package:flutter/material.dart';

class KaprodiProfile extends StatefulWidget {
  const KaprodiProfile({super.key});

  @override
  State<KaprodiProfile> createState() => _KaprodiProfileState();
}

class _KaprodiProfileState extends State<KaprodiProfile> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFEEF1F8),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Profile Header
            Container(
              width: double.infinity,
              decoration: const BoxDecoration(
                color: Color(0xFF1A1A3E),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
              ),
              padding: const EdgeInsets.fromLTRB(18, 30, 18, 36),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Container(
                    width: 78,
                    height: 78,
                    decoration: const BoxDecoration(
                      shape: BoxShape.circle,
                      color: Color.fromARGB(46, 255, 255, 255),
                      border: Border.fromBorderSide(
                        BorderSide(
                          color: Color.fromARGB(71, 255, 255, 255),
                          width: 2.5,
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
                            fontSize: 26,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        Positioned(
                          bottom: 0,
                          right: 0,
                          child: Container(
                            width: 24,
                            height: 24,
                            decoration: const BoxDecoration(
                              color: Color(0xFFFBBF24),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.edit,
                              color: Color(0xFF1A1A2E),
                              size: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 13),
                  const Text(
                    'Idhawati Hestiningsih, M.Kom',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 3),
                  const Text(
                    'NIP: 197101172003121001',
                    style: TextStyle(
                      color: Color(0xFF9FA8DA),
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 2),
                  const Text(
                    'Koordinator Prodi Teknik Informatika · Polines',
                    style: TextStyle(
                      color: Color(0xFF9FA8DA),
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 9),
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
                      mainAxisSize: MainAxisSize.min,
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
                          'Superadmin SITAMA',
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
            ),
            // Content
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Info Grid
                  GridView.count(
                    crossAxisCount: 2,
                    childAspectRatio: 2.2,
                    mainAxisSpacing: 8,
                    crossAxisSpacing: 8,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    children: [
                      _buildInfoBox('Total Mahasiswa', '48 orang'),
                      _buildInfoBox('Dosen Aktif', '12 dosen',
                          valueColor: const Color(0xFF1A4BBB)),
                      _buildInfoBox('Industri Mitra', '7 industri',
                          valueColor: const Color(0xFF1A4BBB)),
                      _buildInfoBox('Seminar Bulan Ini', '5 seminar',
                          valueColor: const Color(0xFF1A4BBB)),
                    ],
                  ),
                  const SizedBox(height: 16),
                  // Account Settings
                  const Text(
                    'Pengaturan Akun',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF8A9BC0),
                      letterSpacing: 0.8,
                    ),
                  ),
                  const SizedBox(height: 8),
                  _buildMenuItem(
                    icon: Icons.person,
                    title: 'Edit Profil',
                    bgColor: const Color(0xFFE8F0FE),
                    iconColor: const Color(0xFF1A4BBB),
                  ),
                  _buildMenuItem(
                    icon: Icons.lock,
                    title: 'Ganti Password',
                    bgColor: const Color(0xFFE8F0FE),
                    iconColor: const Color(0xFF1A4BBB),
                  ),
                  const SizedBox(height: 14),
                  // System Management
                  const Text(
                    'Manajemen Sistem',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF8A9BC0),
                      letterSpacing: 0.8,
                    ),
                  ),
                  const SizedBox(height: 8),
                  _buildMenuItem(
                    icon: Icons.people,
                    title: 'Kelola Akun Dosen',
                    bgColor: const Color(0xFFF3E8FF),
                    iconColor: const Color(0xFF6D28D9),
                  ),
                  _buildMenuItem(
                    icon: Icons.notifications,
                    title: 'Notifikasi',
                    bgColor: const Color(0xFFE8F0FE),
                    iconColor: const Color(0xFF1A4BBB),
                  ),
                  _buildMenuItem(
                    icon: Icons.settings,
                    title: 'Pengaturan',
                    bgColor: const Color(0xFFE8F0FE),
                    iconColor: const Color(0xFF1A4BBB),
                  ),
                  _buildMenuItem(
                    icon: Icons.logout,
                    title: 'Logout',
                    bgColor: const Color(0xFFFCE8E6),
                    iconColor: const Color(0xFF9B2F2F),
                    isDanger: true,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoBox(String label, String value, {Color? valueColor}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(11),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 10,
              color: Color(0xFF8A9BC0),
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: valueColor ?? const Color(0xFF1A2050),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required String title,
    required Color bgColor,
    required Color iconColor,
    bool isDanger = false,
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
      padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 13),
      margin: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          Container(
            width: 34,
            height: 34,
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: iconColor, size: 15),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              title,
              style: TextStyle(
                fontSize: 13,
                color: isDanger
                    ? const Color(0xFF9B2F2F)
                    : const Color(0xFF1A2050),
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          Icon(
            Icons.arrow_forward_ios,
            color: const Color(0xFFC8D0E8),
            size: 20,
          ),
        ],
      ),
    );
  }
}
