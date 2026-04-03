// ignore_for_file: non_constant_identifier_names

class LecturerIndustryProfileEntity {
  final String name;
  final String company_name;
  final String position;
  final String division;
  final String? photo_profile;
  final int total_students;
  final int active_students;
  final int evaluated_students;
  final double average_score;

  LecturerIndustryProfileEntity({
    required this.name,
    required this.company_name,
    required this.position,
    required this.division,
    this.photo_profile,
    required this.total_students,
    required this.active_students,
    required this.evaluated_students,
    required this.average_score,
  });
}
