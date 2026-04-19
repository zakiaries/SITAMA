import 'package:sitama/features/student/domain/entities/job_listing_entity.dart';

class JobListingModel extends JobListingEntity {
  JobListingModel({
    required super.id,
    required super.position,
    required super.company,
    required super.companyLogo,
    required super.description,
    required super.skills,
    required super.location,
    required super.category,
    required super.isNew,
    required super.createdAt,
  });

  factory JobListingModel.fromMap(Map<String, dynamic> map) {
    return JobListingModel(
      id: map['id'] ?? '',
      position: map['position'] ?? '',
      company: map['company'] ?? '',
      companyLogo: map['company_logo'] ?? '',
      description: map['description'] ?? '',
      skills: List<String>.from(map['skills'] ?? []),
      location: map['location'] ?? '',
      category: map['category'] ?? '',
      isNew: map['is_new'] ?? false,
      createdAt:
          DateTime.parse(map['created_at'] ?? DateTime.now().toIso8601String()),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'position': position,
      'company': company,
      'company_logo': companyLogo,
      'description': description,
      'skills': skills,
      'location': location,
      'category': category,
      'is_new': isNew,
      'created_at': createdAt.toIso8601String(),
    };
  }

  JobListingEntity toEntity() {
    return JobListingEntity(
      id: id,
      position: position,
      company: company,
      companyLogo: companyLogo,
      description: description,
      skills: skills,
      location: location,
      category: category,
      isNew: isNew,
      createdAt: createdAt,
    );
  }
}
