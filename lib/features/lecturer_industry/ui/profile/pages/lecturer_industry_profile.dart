import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/ui/profile/bloc/lecturer_industry_profile_cubit.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_profile_entity.dart';

class LecturerIndustryProfilePage extends StatefulWidget {
  const LecturerIndustryProfilePage({super.key});

  @override
  State<LecturerIndustryProfilePage> createState() =>
      _LecturerIndustryProfilePageState();
}

class _LecturerIndustryProfilePageState
    extends State<LecturerIndustryProfilePage> {
  late LecturerIndustryProfileCubit _cubit;

  @override
  void initState() {
    super.initState();
    _cubit = context.read<LecturerIndustryProfileCubit>();
    _cubit.displayProfile();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF2F5FB),
      body: BlocBuilder<LecturerIndustryProfileCubit,
          LecturerIndustryProfileState>(
        builder: (context, state) {
          if (state is Loaded) {
            return _buildContent(state.profile);
          } else if (state is Failure) {
            return Center(child: Text('Error: ${state.errorMessage}'));
          }
          return const Center(child: CircularProgressIndicator());
        },
      ),
    );
  }

  Widget _buildContent(LecturerIndustryProfileEntity profile) {
    return SingleChildScrollView(
      child: Column(
        children: [
          // Header - Full Width
          Container(
            width: double.infinity,
            decoration: const BoxDecoration(
              color: Color(0xFF0D2B6E),
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(32),
                bottomRight: Radius.circular(32),
              ),
            ),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(18, 24, 18, 36),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Stack(
                    alignment: Alignment.bottomRight,
                    children: [
                      Container(
                        width: 76,
                        height: 76,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white.withOpacity(0.2),
                          border: Border.all(
                            color: Colors.white.withOpacity(0.3),
                            width: 2.5,
                          ),
                        ),
                        child: Center(
                          child: Text(
                            _getInitials(profile.name),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 24,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ),
                      Container(
                        width: 24,
                        height: 24,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          color: Color(0xFFFBBF24),
                        ),
                        child: const Icon(Icons.edit,
                            size: 12, color: Color(0xFF1A1A2E)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Text(
                    profile.name,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 19,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    'Pembimbing Industri · ${profile.company_name}',
                    style: const TextStyle(
                      color: Color(0xFF93B4F0),
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    profile.division,
                    style: const TextStyle(
                      color: Color(0xFF93B4F0),
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          ),
          // Stats
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 18, 14, 16),
            child: GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 10,
              crossAxisSpacing: 10,
              childAspectRatio: 1.85,
              children: [
                _buildStatBox(
                    'Total Mahasiswa', '${profile.total_students} orang'),
                _buildStatBox(
                    'Sedang Aktif', '${profile.active_students} orang',
                    color: const Color(0xFF1A4BBB)),
                _buildStatBox(
                    'Telah Dinilai', '${profile.evaluated_students} orang'),
                _buildStatBox(
                    'Rata-rata Nilai', profile.average_score.toStringAsFixed(1),
                    color: const Color(0xFF1A4BBB)),
              ],
            ),
          ),
          // Divider
          Container(
            height: 1,
            color: const Color(0xFFE8F0FE),
            margin: const EdgeInsets.symmetric(vertical: 6),
          ),
          // Menu Items
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 10, 14, 16),
            child: Column(
              children: [
                _buildMenuItem(
                  'Edit Profil',
                  icon: Icons.person,
                  onTap: () {},
                ),
                _buildMenuItem(
                  'Pengaturan',
                  icon: Icons.settings,
                  onTap: () {},
                ),
                _buildMenuItem(
                  'Notifikasi',
                  icon: Icons.notifications,
                  onTap: () {},
                ),
                _buildMenuItem(
                  'Help & Support',
                  icon: Icons.help,
                  onTap: () {},
                ),
                _buildMenuItem(
                  'Ganti Password',
                  icon: Icons.lock,
                  onTap: () {},
                ),
                _buildMenuItem(
                  'Logout',
                  icon: Icons.logout,
                  isDanger: true,
                  onTap: () {},
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildStatBox(String label, String value, {Color? color}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(13),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            label,
            style: const TextStyle(
              color: Color(0xFF8A9BBF),
              fontSize: 10,
              fontWeight: FontWeight.w600,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              color: color ?? const Color(0xFF1E3A6E),
              fontSize: 14,
              fontWeight: FontWeight.w800,
              letterSpacing: -0.2,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem(
    String label, {
    required IconData icon,
    required VoidCallback onTap,
    bool isDanger = false,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFF2F5FB), width: 0.8),
        ),
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(10),
                color: isDanger
                    ? const Color(0xFFFCE8E6)
                    : const Color(0xFFE8F0FE),
              ),
              child: Icon(
                icon,
                size: 15,
                color: isDanger
                    ? const Color(0xFF9B2F2F)
                    : const Color(0xFF1A4BBB),
              ),
            ),
            const SizedBox(width: 12),
            Text(
              label,
              style: TextStyle(
                color: isDanger
                    ? const Color(0xFF9B2F2F)
                    : const Color(0xFF0D2B6E),
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
            ),
            const Spacer(),
            Icon(
              Icons.arrow_forward_ios,
              size: 20,
              color: const Color(0xFFC8D8F0),
            ),
          ],
        ),
      ),
    );
  }

  String _getInitials(String name) {
    final names = name.split(' ');
    if (names.length >= 2) {
      return (names[0][0] + names[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  }
}
