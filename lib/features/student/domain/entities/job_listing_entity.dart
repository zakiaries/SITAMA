class JobListingEntity {
  final String id;
  final String position;
  final String company;
  final String companyLogo;
  final String description;
  final List<String> skills;
  final String location;
  final String category;
  final bool isNew;
  final DateTime createdAt;

  JobListingEntity({
    required this.id,
    required this.position,
    required this.company,
    required this.companyLogo,
    required this.description,
    required this.skills,
    required this.location,
    required this.category,
    required this.isNew,
    required this.createdAt,
  });
}
