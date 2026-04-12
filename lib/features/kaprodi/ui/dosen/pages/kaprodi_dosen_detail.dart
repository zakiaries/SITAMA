import 'package:flutter/material.dart';

class KaprodiDosenDetail extends StatelessWidget {
  final String dosenName;
  final List<Map<String, dynamic>> students;

  const KaprodiDosenDetail({
    super.key,
    required this.dosenName,
    required this.students,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      body: NestedScrollView(
        headerSliverBuilder: (BuildContext context, bool innerBoxIsScrolled) {
          return [
            SliverToBoxAdapter(
              child: ClipRRect(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.fromLTRB(18, 12, 18, 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 16),
                      // Title
                      Text(
                        dosenName,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Bimbingan ${students.length} Mahasiswa',
                        style: const TextStyle(
                          color: Color(0xFFB0BDD4),
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ];
        },
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(14),
          child: Column(
            children: [
              ...students.map((student) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 10),
                  child: _buildStudentCard(student),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStudentCard(Map<String, dynamic> student) {
    final statusBgColor = _getStatusBgColor(student['status']);
    final statusTextColor = _getStatusTextColor(student['status']);

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
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: statusBgColor,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    student['initials'],
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: statusTextColor,
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
                      student['name'],
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A2050),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${student['nim']} · ${student['className']} · ${student['company']}',
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
                  horizontal: 11,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: statusBgColor,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  student['status'],
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: statusTextColor,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Color _getStatusBgColor(String status) {
    if (status == 'Aktif') {
      return const Color(0xFFE8F0FE);
    } else if (status == 'Seminar') {
      return const Color(0xFFE6F4EA);
    } else if (status == 'Selesai') {
      return const Color(0xFFE6F4EA);
    }
    return const Color(0xFFFCE8E6);
  }

  Color _getStatusTextColor(String status) {
    if (status == 'Aktif') {
      return const Color(0xFF1A3A8E);
    } else if (status == 'Seminar') {
      return const Color(0xFF1E6E3E);
    } else if (status == 'Selesai') {
      return const Color(0xFF1E6E3E);
    }
    return const Color(0xFF9B2F2F);
  }
}
