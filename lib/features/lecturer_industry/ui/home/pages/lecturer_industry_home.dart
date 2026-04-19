import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/ui/home/bloc/lecturer_industry_display_cubit.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_home_entity.dart';

class LecturerIndustryHomePage extends StatefulWidget {
  final Function(int, String)? onStudentSelected;

  const LecturerIndustryHomePage({
    super.key,
    this.onStudentSelected,
  });

  @override
  State<LecturerIndustryHomePage> createState() =>
      _LecturerIndustryHomePageState();
}

class _LecturerIndustryHomePageState extends State<LecturerIndustryHomePage> {
  late LecturerIndustryDisplayCubit _cubit;

  @override
  void initState() {
    super.initState();
    _cubit = context.read<LecturerIndustryDisplayCubit>();
    _cubit.displayLecturerIndus();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF2F5FB),
      body: BlocBuilder<LecturerIndustryDisplayCubit,
          LecturerIndustryDisplayState>(
        builder: (context, state) {
          if (state is DetailLoaded) {
            return _buildContent(state.data);
          } else if (state is Failure) {
            return Center(child: Text('Error: ${state.errorMessage}'));
          }
          return const Center(child: CircularProgressIndicator());
        },
      ),
    );
  }

  Widget _buildContent(LecturerIndustryHomeEntity data) {
    return SafeArea(
      child: SingleChildScrollView(
        child: Column(
          children: [
            // Header
            Container(
              decoration: const BoxDecoration(
                color: Color(0xFF0D2B6E),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
              ),
              child: Padding(
                padding: const EdgeInsets.fromLTRB(18, 16, 18, 24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Pembimbing Industri',
                              style: TextStyle(
                                color: Color(0xFF93B4F0),
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              data.lecturer_name,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 19,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              '${data.company_name} · ${data.division}',
                              style: const TextStyle(
                                color: Color(0xFF93B4F0),
                                fontSize: 11,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          width: 46,
                          height: 46,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white.withValues(alpha: 0.15),
                            border: Border.all(
                              color: Colors.white.withValues(alpha: 0.25),
                              width: 2,
                            ),
                          ),
                          child: Center(
                            child: Text(
                              _getInitials(data.lecturer_name),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 15,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    // Search
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: TextField(
                        decoration: InputDecoration(
                          hintText: 'Cari nama mahasiswa...',
                          hintStyle: const TextStyle(
                            color: Color(0xFF93B4F0),
                            fontSize: 12,
                          ),
                          border: InputBorder.none,
                          contentPadding:
                              const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          prefixIcon: const Icon(Icons.search,
                              size: 15, color: Color(0xFF93B4F0)),
                        ),
                        style: const TextStyle(color: Color(0xFFE8F0FE)),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            // Stats
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Expanded(
                    child: _buildStatCard('${data.total_students}', 'Mahasiswa'),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildStatCard('${data.active_students}', 'Aktif'),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildStatCard('${data.new_logbooks}', 'Logbook Baru'),
                  ),
                ],
              ),
            ),
            // Students List
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Mahasiswa Bimbingan',
                    style: TextStyle(
                      color: Color(0xFF1E3A6E),
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  Text(
                    'Lihat semua',
                    style: TextStyle(
                      color: const Color(0xFF4A7FD4),
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: data.students.length,
                itemBuilder: (context, index) {
                  final student = data.students[index];
                  return _buildStudentCard(student);
                },
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String value, String label) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFD0DDF5), width: 0.5),
      ),
      padding: const EdgeInsets.all(10),
      child: Column(
        children: [
          Text(
            value,
            style: const TextStyle(
              color: Color(0xFF0D2B6E),
              fontSize: 22,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(
              color: Color(0xFF8A9BBF),
              fontSize: 10,
              fontWeight: FontWeight.w600,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildStudentCard(IndustryStudentEntity student) {
    return GestureDetector(
      onTap: () {
        widget.onStudentSelected?.call(student.id, student.name);
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFFD0DDF5), width: 0.5),
        ),
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(13),
        child: Column(
          children: [
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: _getStatusColor(student.status).withValues(alpha: 0.2),
                  ),
                  child: Center(
                    child: Text(
                      _getInitials(student.name),
                      style: TextStyle(
                        color: _getStatusColor(student.status),
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 11),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        student.name,
                        style: const TextStyle(
                          color: Color(0xFF1E3A6E),
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'NIM: ${student.nim} · ${student.class_name}',
                        style: const TextStyle(
                          color: Color(0xFF8A9BBF),
                          fontSize: 10,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 1),
                      Text(
                        student.position,
                        style: const TextStyle(
                          color: Color(0xFF8A9BBF),
                          fontSize: 10,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 11, vertical: 3),
                  decoration: BoxDecoration(
                    color: _getStatusBgColor(student.status),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    student.status,
                    style: TextStyle(
                      color: _getStatusColor(student.status),
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Progress Logbook',
                      style: TextStyle(
                        color: Color(0xFF8A9BBF),
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    Text(
                      '${student.progress_percentage.toInt()}%',
                      style: const TextStyle(
                        color: Color(0xFF8A9BBF),
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 5),
                ClipRRect(
                  borderRadius: BorderRadius.circular(3),
                  child: LinearProgressIndicator(
                    value: student.progress_percentage / 100,
                    minHeight: 5,
                    backgroundColor: const Color(0xFFD8E8F8),
                    valueColor: AlwaysStoppedAnimation<Color>(
                      _getProgressColor(student.progress_percentage),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Aktif':
        return const Color(0xFF0D2B6E);
      case 'Selesai':
        return const Color(0xFF1E6E3E);
      case 'Baru':
        return const Color(0xFF7A5800);
      default:
        return const Color(0xFF0D2B6E);
    }
  }

  Color _getStatusBgColor(String status) {
    switch (status) {
      case 'Aktif':
        return const Color(0xFFE8F0FE);
      case 'Selesai':
        return const Color(0xFFE6F4EA);
      case 'Baru':
        return const Color(0xFFFFF8E1);
      default:
        return const Color(0xFFE8F0FE);
    }
  }

  Color _getProgressColor(double percentage) {
    if (percentage >= 100) {
      return const Color(0xFF34A853);
    } else if (percentage >= 75) {
      return const Color(0xFF1A4BBB);
    } else if (percentage >= 50) {
      return const Color(0xFF1A4BBB);
    }
    return const Color(0xFFFBBF24);
  }

  String _getInitials(String name) {
    final names = name.split(' ');
    if (names.length >= 2) {
      return (names[0][0] + names[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  }
}
