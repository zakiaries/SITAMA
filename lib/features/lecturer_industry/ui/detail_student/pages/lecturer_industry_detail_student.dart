import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/ui/detail_student/bloc/lecturer_industry_detail_cubit.dart'
    as detail;
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';
import 'package:sitama/features/lecturer_industry/ui/assessment/pages/industry_assessment.dart';
import 'package:sitama/features/lecturer_industry/ui/assessment/bloc/industry_score_cubit.dart'
    as score;

class LecturerIndustryDetailStudentPage extends StatefulWidget {
  final int studentId;
  final VoidCallback? onBack;

  const LecturerIndustryDetailStudentPage({
    super.key,
    required this.studentId,
    this.onBack,
  });

  @override
  State<LecturerIndustryDetailStudentPage> createState() =>
      _LecturerIndustryDetailStudentPageState();
}

class _LecturerIndustryDetailStudentPageState
    extends State<LecturerIndustryDetailStudentPage> {
  late detail.LecturerIndustryDetailCubit _cubit;
  final TextEditingController _logbookController = TextEditingController();
  final TextEditingController _activityController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _cubit = context.read<detail.LecturerIndustryDetailCubit>();
    _cubit.displayStudent(widget.studentId);
  }

  @override
  void dispose() {
    _logbookController.dispose();
    _activityController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF2F5FB),
      body: BlocBuilder<detail.LecturerIndustryDetailCubit,
          detail.LecturerIndustryDetailState>(
        builder: (context, state) {
          if (state is detail.DetailLoaded) {
            return _buildContent(state.data);
          } else if (state is detail.Failure) {
            return Center(child: Text('Error: ${state.errorMessage}'));
          }
          return const Center(child: CircularProgressIndicator());
        },
      ),
    );
  }

  Widget _buildContent(LecturerIndustryDetailStudentEntity data) {
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
                padding: const EdgeInsets.fromLTRB(18, 16, 18, 28),
                child: Column(
                  children: [
                    Row(
                      children: [
                        GestureDetector(
                          onTap:
                              widget.onBack ?? (() => Navigator.pop(context)),
                          child: Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withValues(alpha: 0.12),
                            ),
                            child: const Icon(Icons.arrow_back,
                                color: Color(0xFFE8F0FE), size: 16),
                          ),
                        ),
                        const SizedBox(width: 12),
                        const Text(
                          'Detail Mahasiswa',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 13),
                    Row(
                      children: [
                        Container(
                          width: 60,
                          height: 60,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white.withValues(alpha: 0.2),
                            border: Border.all(
                              color: Colors.white.withValues(alpha: 0.3),
                              width: 2,
                            ),
                          ),
                          child: Center(
                            child: Text(
                              _getInitials(data.student_name),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 20,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 13),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                data.student_name,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                'NIM: ${data.nim} · ${data.position}',
                                style: const TextStyle(
                                  color: Color(0xFF93B4F0),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              const SizedBox(height: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 11, vertical: 3),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFE8F0FE),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  'Aktif Magang',
                                  style: TextStyle(
                                    color: const Color(0xFF0D2B6E),
                                    fontSize: 10,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            // Info Grid - Improved spacing and styling
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 24, 16, 20),
              child: GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 10,
                crossAxisSpacing: 10,
                childAspectRatio: 2.2,
                children: [
                  _buildInfoBox('Mulai Magang', _formatDate(data.start_date)),
                  _buildInfoBox(
                      'Selesai', _formatDate(data.end_date ?? DateTime.now()),
                      color: const Color(0xFFFBBF24)),
                  _buildInfoBox(
                      'Total Logbook', '${data.total_logbooks} entri'),
                  _buildInfoBox('Kehadiran',
                      '${data.attendance_percentage.toStringAsFixed(0)}%',
                      color: const Color(0xFF1A4BBB)),
                ],
              ),
            ),
            // Riwayat Catatan - Display logbook history first
            if (data.recent_logbooks.isNotEmpty)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Riwayat Catatan',
                          style: TextStyle(
                            color: Color(0xFF0D2B6E),
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
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
                    const SizedBox(height: 10),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: data.recent_logbooks.length,
                      itemBuilder: (context, index) {
                        final logbook = data.recent_logbooks[index];
                        return _buildLogbookCard(logbook);
                      },
                    ),
                  ],
                ),
              ),
            // Catatan Kerja Form - Add new entries
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Catatan Kerja',
                    style: TextStyle(
                      color: Color(0xFF0D2B6E),
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 10),
                  // Input Form untuk Logbook Baru
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: const Color(0xFFD0DDF5),
                        width: 1,
                      ),
                    ),
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Tambah Catatan Kerja',
                          style: TextStyle(
                            color: Color(0xFF0D2B6E),
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 8),
                        TextField(
                          controller: _activityController,
                          decoration: InputDecoration(
                            hintText: 'Deskripsi pekerjaan...',
                            hintStyle: const TextStyle(
                              color: Color(0xFFA0AEC8),
                              fontSize: 12,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: Color(0xFFE8F0FE),
                              ),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: Color(0xFFE8F0FE),
                              ),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: Color(0xFF0D2B6E),
                              ),
                            ),
                            contentPadding: const EdgeInsets.all(12),
                          ),
                          maxLines: 4,
                        ),
                        const SizedBox(height: 10),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: () {
                              if (_activityController.text.isNotEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text(
                                        'Catatan kerja berhasil ditambahkan'),
                                    duration: Duration(seconds: 1),
                                  ),
                                );
                                _activityController.clear();
                              }
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF1A4BBB),
                              padding: const EdgeInsets.symmetric(vertical: 10),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10),
                              ),
                              elevation: 0,
                            ),
                            child: const Text(
                              'Simpan Catatan',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (data.recent_logbooks.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 20),
                      child: Center(
                        child: Text(
                          'Belum ada catatan kerja',
                          style: TextStyle(
                            color: Color(0xFFA0AEC8),
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            // Beri Penilaian Akhir Button - At the very bottom
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => BlocProvider(
                          create: (context) => score.IndustryScoreCubit(),
                          child: IndustryAssessmentPage(
                            studentId: widget.studentId,
                            studentName: data.student_name,
                          ),
                        ),
                      ),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0D2B6E),
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    elevation: 0,
                  ),
                  child: const Text(
                    'Beri Penilaian Akhir',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 0.3,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoBox(String label, String value, {Color? color}) {
    // Determine icon based on label
    IconData icon = Icons.calendar_today;
    Color bgColor = const Color(0xFFF0F4FF);

    if (label.contains('Selesai')) {
      icon = Icons.check_circle_outline;
      bgColor = const Color(0xFFFEF3F0);
    } else if (label.contains('Logbook')) {
      icon = Icons.description_outlined;
      bgColor = const Color(0xFFF5F0FF);
    } else if (label.contains('Kehadiran')) {
      icon = Icons.trending_up;
      bgColor = const Color(0xFFF0FEF3);
    }

    return Container(
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: (color ?? const Color(0xFF0D2B6E)).withValues(alpha: 0.08),
          width: 1,
        ),
      ),
      padding: const EdgeInsets.fromLTRB(8, 6, 8, 6),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.start,
        children: [
          // Icon + Label
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: const TextStyle(
                  color: Color(0xFF8A9BBF),
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 0.2,
                ),
              ),
              Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Icon(
                  icon,
                  size: 10,
                  color: color ?? const Color(0xFF0D2B6E),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          // Value
          Text(
            value,
            style: TextStyle(
              color: color ?? const Color(0xFF0D2B6E),
              fontSize: 12,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.2,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLogbookCard(LogbookEntryEntity logbook) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(11),
                    color: const Color(0xFFE8F0FE),
                  ),
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          '${logbook.day_number}',
                          style: const TextStyle(
                            color: Color(0xFF0D2B6E),
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            height: 1,
                          ),
                        ),
                        const Text(
                          'DES',
                          style: TextStyle(
                            color: Color(0xFF4A7FD4),
                            fontSize: 8,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        logbook.title,
                        style: const TextStyle(
                          color: Color(0xFF0D2B6E),
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _formatDate(logbook.date),
                        style: const TextStyle(
                          color: Color(0xFF8A9BBF),
                          fontSize: 10,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14),
            child: Text(
              logbook.description,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                color: Color(0xFF3A5A7A),
                fontSize: 11,
                fontWeight: FontWeight.w400,
                height: 1.5,
              ),
            ),
          ),
          const SizedBox(height: 10),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14),
            child: Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE8F0FE),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    logbook.category,
                    style: const TextStyle(
                      color: Color(0xFF1A4BBB),
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 9),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    final months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Des'
    ];
    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }

  String _getInitials(String name) {
    final names = name.split(' ');
    if (names.length >= 2) {
      return (names[0][0] + names[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  }
}
