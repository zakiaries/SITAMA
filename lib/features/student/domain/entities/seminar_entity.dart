// ignore_for_file: non_constant_identifier_names

class SeminarEntity {
  final int id;
  final String title;
  final String program;
  final DateTime date;
  final String time;
  final String location;
  final String organizer;
  final String description;
  final String qrCode;
  final String status; // scheduled, registered, completed

  SeminarEntity({
    required this.id,
    required this.title,
    required this.program,
    required this.date,
    required this.time,
    required this.location,
    required this.organizer,
    required this.description,
    required this.qrCode,
    required this.status,
  });
}

class ListSeminarEntity {
  final List<SeminarEntity> seminars;

  ListSeminarEntity({required this.seminars});
}
