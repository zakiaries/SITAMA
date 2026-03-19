class JobListingEntity {
  final String id;
  final String position;
  final String company;
  final String company_logo;
  final String description;
  final List<String> skills;
  final String location;
  final String category;
  final bool is_new;
  final DateTime created_at;

  JobListingEntity({
    required this.id,
    required this.position,
    required this.company,
    required this.company_logo,
    required this.description,
    required this.skills,
    required this.location,
    required this.category,
    required this.is_new,
    required this.created_at,
  });
}
