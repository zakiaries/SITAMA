import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/ui/logbook/bloc/logbook_industry_cubit.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';

class LogbookIndustryPage extends StatefulWidget {
  final int studentId;
  final String studentName;

  const LogbookIndustryPage({
    super.key,
    required this.studentId,
    required this.studentName,
  });

  @override
  State<LogbookIndustryPage> createState() => _LogbookIndustryPageState();
}

class _LogbookIndustryPageState extends State<LogbookIndustryPage> {
  late LogbookIndustryCubit _cubit;

  @override
  void initState() {
    super.initState();
    _cubit = context.read<LogbookIndustryCubit>();
    _cubit.fetchLogbooks(widget.studentId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF2F5FB),
      body: BlocBuilder<LogbookIndustryCubit, LogbookIndustryState>(
        builder: (context, state) {
          if (state is Loaded) {
            return _buildContent(state.logbooks, state.filter);
          } else if (state is Failure) {
            return Center(child: Text('Error: ${state.errorMessage}'));
          }
          return const Center(child: CircularProgressIndicator());
        },
      ),
    );
  }

  Widget _buildContent(List<LogbookEntryEntity> logbooks, String filter) {
    return SafeArea(
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
              padding: const EdgeInsets.fromLTRB(18, 16, 18, 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 34,
                          height: 34,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white.withOpacity(0.12),
                          ),
                          child: const Icon(Icons.arrow_back,
                              color: Color(0xFFE8F0FE), size: 16),
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Text(
                        'Logbook Mahasiswa',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 17,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 13, vertical: 9),
                    child: Row(
                      children: [
                        Container(
                          width: 30,
                          height: 30,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white.withOpacity(0.2),
                          ),
                          child: Center(
                            child: Text(
                              _getInitials(widget.studentName),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
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
                                widget.studentName,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const Text(
                                '12 entri · 28 hari aktif',
                                style: TextStyle(
                                  color: Color(0xFF93B4F0),
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
                ],
              ),
            ),
          ),
          // Filter Tabs
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: [
                  _buildFilterTab('Semua', filter == 'Semua',
                      () => _cubit.filterLogbooks(widget.studentId, 'Semua')),
                  const SizedBox(width: 7),
                  _buildFilterTab(
                      'Belum Dikomen',
                      filter == 'Belum Dikomen',
                      () => _cubit.filterLogbooks(
                          widget.studentId, 'Belum Dikomen')),
                  const SizedBox(width: 7),
                  _buildFilterTab(
                      'Sudah Dikomen',
                      filter == 'Sudah Dikomen',
                      () => _cubit.filterLogbooks(
                          widget.studentId, 'Sudah Dikomen')),
                ],
              ),
            ),
          ),
          // Logbooks List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: logbooks.length,
              itemBuilder: (context, index) {
                final logbook = logbooks[index];
                return _buildLogbookCard(logbook);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterTab(String label, bool isActive, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 5),
        decoration: BoxDecoration(
          color: isActive ? const Color(0xFF0D2B6E) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isActive ? const Color(0xFF0D2B6E) : const Color(0xFFC8D8F0),
            width: 0.5,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isActive ? Colors.white : const Color(0xFF5A6E90),
            fontSize: 11,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
    );
  }

  Widget _buildLogbookCard(LogbookEntryEntity logbook) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD0DDF5), width: 0.5),
      ),
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(10),
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
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            height: 1,
                          ),
                        ),
                        const Text(
                          'DES',
                          style: TextStyle(
                            color: Color(0xFF4A7FD4),
                            fontSize: 9,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        logbook.title,
                        style: const TextStyle(
                          color: Color(0xFF1E3A6E),
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 2),
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
            padding: const EdgeInsets.symmetric(horizontal: 13),
            child: Text(
              logbook.description,
              style: const TextStyle(
                color: Color(0xFF3A5A7A),
                fontSize: 11,
                fontWeight: FontWeight.w400,
                height: 1.6,
              ),
            ),
          ),
          // Comment Box if exists
          if (logbook.has_comment) ...[
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 13),
              child: Container(
                decoration: BoxDecoration(
                  color: const Color(0xFFF7F9FF),
                  border: const Border(
                    left: BorderSide(
                      color: Color(0xFF4A7FD4),
                      width: 3,
                    ),
                  ),
                ),
                padding: const EdgeInsets.all(11),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Komentar Saya',
                      style: TextStyle(
                        color: Color(0xFF4A7FD4),
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        height: 1,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      logbook.comment_by_lecturer ?? '',
                      style: const TextStyle(
                        color: Color(0xFF1E3A6E),
                        fontSize: 11,
                        fontWeight: FontWeight.w400,
                        height: 1.5,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
          const SizedBox(height: 9),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 13),
            child: Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
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
                const SizedBox(width: 7),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(
                    color: logbook.has_comment
                        ? const Color(0xFFE6F4EA)
                        : const Color(0xFFFFF3E0),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    logbook.comment_status,
                    style: TextStyle(
                      color: logbook.has_comment
                          ? const Color(0xFF1E6E3E)
                          : const Color(0xFF7A4200),
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
                const Spacer(),
                GestureDetector(
                  onTap: () => _showCommentSheet(logbook.id),
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 13, vertical: 5),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0D2B6E),
                      borderRadius: BorderRadius.circular(9),
                    ),
                    child: const Text(
                      '+ Komentar',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                      ),
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

  void _showCommentSheet(int logbookId) {
    final commentController = TextEditingController();
    showModalBottomSheet(
      context: context,
      builder: (context) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(22),
              topRight: Radius.circular(22),
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 44,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFFD0DDF5),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Tambah Komentar Logbook',
                  style: TextStyle(
                    color: Color(0xFF1E3A6E),
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 13),
                const Text(
                  'Komentar / Catatan untuk Mahasiswa',
                  style: TextStyle(
                    color: Color(0xFF5A6E90),
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 5),
                TextField(
                  controller: commentController,
                  maxLines: 4,
                  decoration: InputDecoration(
                    hintText: 'Tuliskan masukan, apresiasi, atau arahan...',
                    filled: true,
                    fillColor: const Color(0xFFF7F9FF),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(
                        color: Color(0xFFC8D8F0),
                        width: 0.5,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: TextButton(
                        onPressed: () => Navigator.pop(context),
                        style: TextButton.styleFrom(
                          backgroundColor: const Color(0xFFF0F4F8),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(11),
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 11),
                        ),
                        child: const Text(
                          'Batal',
                          style: TextStyle(
                            color: Color(0xFF5A6E90),
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 9),
                    Expanded(
                      flex: 2,
                      child: ElevatedButton(
                        onPressed: () {
                          _cubit.addComment(widget.studentId, logbookId,
                              commentController.text);
                          Navigator.pop(context);
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0D2B6E),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(11),
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 11),
                        ),
                        child: const Text(
                          'Kirim Komentar',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
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
    final days = [
      'Senin',
      'Selasa',
      'Rabu',
      'Kamis',
      'Jumat',
      'Sabtu',
      'Minggu'
    ];
    return '${days[date.weekday - 1]}, ${date.day} ${months[date.month - 1]} ${date.year}';
  }

  String _getInitials(String name) {
    final names = name.split(' ');
    if (names.length >= 2) {
      return (names[0][0] + names[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  }
}
