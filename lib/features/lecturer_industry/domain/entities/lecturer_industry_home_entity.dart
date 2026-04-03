// ignore_for_file: non_constant_identifier_names

class LecturerIndustryHomeEntity {
  final String lecturer_name;
  final String company_name;
  final String position;
  final String division;
  final List<IndustryStudentEntity> students;
  final int total_students;
  final int active_students;
  final int new_logbooks;

  LecturerIndustryHomeEntity({
    required this.lecturer_name,
    required this.company_name,
    required this.position,
    required this.division,
    required this.students,
    required this.total_students,
    required this.active_students,
    required this.new_logbooks,
  });
}

class IndustryStudentEntity {
  final int id;
  final String name;
  final String nim;
  final String class_name;
  final String position;
  final String status; // 'Aktif', 'Selesai', 'Baru'
  final double progress_percentage;
  final DateTime start_date;
  final DateTime? end_date;
  final int total_logbooks;
  final double attendance_percentage;

  IndustryStudentEntity({
    required this.id,
    required this.name,
    required this.nim,
    required this.class_name,
    required this.position,
    required this.status,
    required this.progress_percentage,
    required this.start_date,
    this.end_date,
    required this.total_logbooks,
    required this.attendance_percentage,
  });
}
