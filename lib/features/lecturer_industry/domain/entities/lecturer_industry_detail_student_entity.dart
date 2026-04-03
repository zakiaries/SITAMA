// ignore_for_file: non_constant_identifier_names

class LecturerIndustryDetailStudentEntity {
  final int student_id;
  final String student_name;
  final String nim;
  final String position;
  final String status;
  final DateTime start_date;
  final DateTime? end_date;
  final int total_logbooks;
  final double attendance_percentage;
  final List<LogbookEntryEntity> recent_logbooks;
  final String performance_notes;
  final String performance_notes_by;
  final DateTime? performance_notes_date;
  final IndustryScoreEntity? assessment_scores;

  LecturerIndustryDetailStudentEntity({
    required this.student_id,
    required this.student_name,
    required this.nim,
    required this.position,
    required this.status,
    required this.start_date,
    required this.end_date,
    required this.total_logbooks,
    required this.attendance_percentage,
    required this.recent_logbooks,
    required this.performance_notes,
    required this.performance_notes_by,
    this.performance_notes_date,
    this.assessment_scores,
  });
}

class LogbookEntryEntity {
  final int id;
  final int day_number;
  final String title;
  final String description;
  final String category; // Laporan, Pengembangan, Rapat, Orientasi
  final DateTime date;
  final String? comment_by_lecturer;
  final bool has_comment;
  final String comment_status; // 'Sudah dikomen', 'Belum dikomen'

  LogbookEntryEntity({
    required this.id,
    required this.day_number,
    required this.title,
    required this.description,
    required this.category,
    required this.date,
    this.comment_by_lecturer,
    required this.has_comment,
    required this.comment_status,
  });
}

class IndustryScoreEntity {
  final List<ScoreCategoryEntity> categories;
  final double average_score;
  final String score_quality; // Sangat Baik, Baik, Cukup, Kurang

  IndustryScoreEntity({
    required this.categories,
    required this.average_score,
    required this.score_quality,
  });
}

class ScoreCategoryEntity {
  final String category_name;
  final List<ScoreItemEntity> items;

  ScoreCategoryEntity({
    required this.category_name,
    required this.items,
  });
}

class ScoreItemEntity {
  final String item_name;
  final int score;
  final int max_score;

  ScoreItemEntity({
    required this.item_name,
    required this.score,
    this.max_score = 100,
  });
}
