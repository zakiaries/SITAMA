import 'package:flutter/material.dart';
import 'package:sitama/features/student/domain/entities/job_listing_entity.dart';

class JobCard extends StatelessWidget {
  final JobListingEntity job;
  final VoidCallback onApply;

  const JobCard({
    super.key,
    required this.job,
    required this.onApply,
  });

  Color _getColorFromLogo(String logo) {
    final colors = {
      'TI': Color(0xFF4A90E2),
      'BN': Color(0xFF52C77A),
      'GK': Color(0xFF9B59B6),
      'TP': Color(0xFFE8A87C),
      'BL': Color(0xFFF5A623),
      'OV': Color(0xFF1E90FF),
    };
    return colors[logo] ?? Color(0xFF4A90E2);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.only(bottom: 0),
      padding: EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: Colors.grey[200]!,
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 10,
            offset: Offset(0, 3),
          )
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row - Logo + Details + Badge
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Logo Container - Enhanced
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: _getColorFromLogo(job.company_logo),
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color:
                          _getColorFromLogo(job.company_logo).withOpacity(0.2),
                      blurRadius: 8,
                      offset: Offset(0, 2),
                    )
                  ],
                ),
                child: Center(
                  child: Text(
                    job.company_logo,
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w700,
                      fontSize: 16,
                    ),
                  ),
                ),
              ),
              SizedBox(width: 14),
              // Job Details
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Expanded(
                          child: Text(
                            job.position,
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1E3A8A),
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (job.is_new)
                          Padding(
                            padding: EdgeInsets.only(left: 8),
                            child: Container(
                              padding: EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: Color(0xFF10B981),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                'Baru',
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
                    SizedBox(height: 6),
                    Row(
                      children: [
                        Icon(Icons.business, size: 13, color: Colors.grey[500]),
                        SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            job.company,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                              fontWeight: FontWeight.w500,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),

          SizedBox(height: 14),

          // Skills Tags - Enhanced
          if (job.skills.isNotEmpty)
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: job.skills.take(3).map((skill) {
                    return Container(
                      padding: EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [
                            Color(0xFF1E3A8A).withOpacity(0.08),
                            Color(0xFF3D568F).withOpacity(0.08)
                          ],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                            color: Color(0xFF1E3A8A).withOpacity(0.2)),
                      ),
                      child: Text(
                        skill,
                        style: TextStyle(
                          fontSize: 11,
                          color: Color(0xFF1E3A8A),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    );
                  }).toList(),
                ),
                if (job.skills.length > 3)
                  Padding(
                    padding: EdgeInsets.only(top: 8),
                    child: Text(
                      '+${job.skills.length - 3} keahlian lainnya',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey[500],
                        fontStyle: FontStyle.italic,
                      ),
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

          // Footer - Location + Button
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Row(
                  children: [
                    Icon(
                      Icons.location_on,
                      size: 16,
                      color: Color(0xFF1E3A8A),
                    ),
                    SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        job.location,
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey[700],
                          fontWeight: FontWeight.w600,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              SizedBox(width: 12),
              ElevatedButton(
                onPressed: onApply,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Color(0xFF1E3A8A),
                  foregroundColor: Colors.white,
                  padding: EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 10,
                  ),
                  elevation: 2,
                  shadowColor: Color(0xFF1E3A8A).withOpacity(0.3),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.add_circle_outline, size: 16),
                    SizedBox(width: 6),
                    Text(
                      'Daftar',
                      style: TextStyle(
                        fontSize: 12,
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
    );
  }
}
