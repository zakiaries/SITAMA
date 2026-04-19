import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/entities/seminar_entity.dart';
import 'package:sitama/features/student/ui/seminar/pages/seminar_detail.dart';
import 'package:sitama/features/student/ui/seminar/bloc/seminar_cubit.dart';
import 'package:sitama/service_locator.dart';

class SeminarListingPage extends StatefulWidget {
  const SeminarListingPage({super.key});

  @override
  State<SeminarListingPage> createState() => _SeminarListingPageState();
}

class _SeminarListingPageState extends State<SeminarListingPage> {
  late SeminarCubit _seminarCubit;

  @override
  void initState() {
    super.initState();
    _seminarCubit = sl<SeminarCubit>();
    _seminarCubit.displaySeminars();
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider<SeminarCubit>.value(
      value: _seminarCubit,
      child: Scaffold(
        body: BlocBuilder<SeminarCubit, SeminarState>(
          builder: (context, state) {
            if (state is SeminarLoading) {
              return Center(
                child: CircularProgressIndicator(
                  color: Color(0xFF1E3A8A),
                ),
              );
            } else if (state is SeminarLoaded) {
              return CustomScrollView(
                slivers: [
                  SliverAppBar(
                    floating: false,
                    snap: false,
                    pinned: true,
                    elevation: 0,
                    backgroundColor: Theme.of(context).colorScheme.surface,
                    title: Text(
                      'Seminar Magang',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.onSurface,
                      ),
                    ),
                    centerTitle: true,
                  ),

                  // Content
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Header Info Card
                          Container(
                            padding: EdgeInsets.all(18),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [Color(0xFF1E3A8A), Color(0xFF2D5AA8)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: Color(0xFF1E3A8A).withValues(alpha: 0.2),
                                  blurRadius: 12,
                                  offset: Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Icon(Icons.school,
                                        color: Colors.white, size: 24),
                                    SizedBox(width: 12),
                                    Expanded(
                                      child: Text(
                                        'Seminar Wajib Magang',
                                        style: TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.w700,
                                          color: Colors.white,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                SizedBox(height: 12),
                                Text(
                                  'Daftarkan diri Anda untuk mengikuti seminar magang kami. Absensi dapat dilakukan dengan scan QR code di lokasi seminar.',
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: Colors.white.withValues(alpha: 0.95),
                                    height: 1.6,
                                    fontWeight: FontWeight.w400,
                                  ),
                                ),
                              ],
                            ),
                          ),

                          SizedBox(height: 28),

                          // Section Header
                          Row(
                            children: [
                              Container(
                                width: 4,
                                height: 24,
                                decoration: BoxDecoration(
                                  color: Color(0xFF1E3A8A),
                                  borderRadius: BorderRadius.circular(2),
                                ),
                              ),
                              SizedBox(width: 12),
                              Text(
                                'Daftar Seminar (${state.seminars.length})',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF1E3A8A),
                                ),
                              ),
                            ],
                          ),

                          SizedBox(height: 16),

                          // Seminars Grid/List
                          ...state.seminars.map(
                              (seminar) => _buildSeminarCard(seminar, context)),

                          SizedBox(height: 24),
                        ],
                      ),
                    ),
                  ),
                ],
              );
            } else if (state is SeminarError) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.error_outline,
                        size: 48, color: Colors.grey[400]),
                    SizedBox(height: 16),
                    Text(
                      'Error: ${state.message}',
                      style: TextStyle(color: Colors.grey[600], fontSize: 14),
                    ),
                  ],
                ),
              );
            }
            return Center(child: Text('No data'));
          },
        ),
      ),
    );
  }

  Widget _buildSeminarCard(SeminarEntity seminar, BuildContext context) {
    // Format tanggal tanpa locale untuk kompatibilitas web
    final monthNames = [
      '',
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
      'Dec'
    ];
    final dateStr =
        '${seminar.date.day} ${monthNames[seminar.date.month]} ${seminar.date.year}';

    final statusColor = seminar.status == 'scheduled'
        ? Color(0xFFFCBA28)
        : seminar.status == 'registered'
            ? Color(0xFF10B981)
            : Colors.grey;
    final statusText = seminar.status == 'scheduled'
        ? 'Terjadwal'
        : seminar.status == 'registered'
            ? 'Terdaftar'
            : 'Selesai';

    final statusIcon = seminar.status == 'scheduled'
        ? Icons.schedule
        : seminar.status == 'registered'
            ? Icons.check_circle
            : Icons.done_all;

    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => SeminarDetailPage(seminar: seminar),
          ),
        );
      },
      child: Container(
        margin: EdgeInsets.only(bottom: 14),
        padding: EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: Colors.grey[200]!, width: 1),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
              offset: Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Title and Status Row
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        seminar.title,
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1E3A8A),
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      SizedBox(height: 4),
                      Text(
                        seminar.program,
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey[600],
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                SizedBox(width: 8),
                Container(
                  padding: EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(statusIcon, size: 14, color: statusColor),
                      SizedBox(width: 4),
                      Text(
                        statusText,
                        style: TextStyle(
                          fontSize: 12,
                          color: statusColor,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            SizedBox(height: 14),

            // Divider
            Container(
              height: 1,
              color: Colors.grey[200],
            ),

            SizedBox(height: 14),

            // Info Items - Enhanced Layout
            Row(
              children: [
                Expanded(
                  child: _buildInfoItem(
                    icon: Icons.calendar_today,
                    label: 'Tanggal',
                    value: dateStr,
                  ),
                ),
                SizedBox(width: 12),
                Expanded(
                  child: _buildInfoItem(
                    icon: Icons.access_time,
                    label: 'Waktu',
                    value: seminar.time,
                  ),
                ),
                SizedBox(width: 12),
                Expanded(
                  child: _buildInfoItem(
                    icon: Icons.location_on,
                    label: 'Ruang',
                    value: seminar.location,
                  ),
                ),
              ],
            ),

            // Tap to view detail hint
            SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Text(
                  'Tap untuk detail »',
                  style: TextStyle(
                    fontSize: 12,
                    color: Color(0xFF1E3A8A),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoItem({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 14, color: Color(0xFF1E3A8A)),
            SizedBox(width: 4),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  fontSize: 11,
                  color: Colors.grey[500],
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        ),
        SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: Colors.black87,
          ),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
      ],
    );
  }
}
