import 'package:sitama/features/student/domain/entities/job_listing_entity.dart';

class JobListingModel extends JobListingEntity {
  JobListingModel({
    required super.id,
    required super.position,
    required super.company,
    required super.company_logo,
    required super.description,
    required super.skills,
    required super.location,
    required super.category,
    required super.is_new,
    required super.created_at,
  });

  factory JobListingModel.fromMap(Map<String, dynamic> map) {
    return JobListingModel(
      id: map['id'] ?? '',
      position: map['position'] ?? '',
      company: map['company'] ?? '',
      company_logo: map['company_logo'] ?? '',
      description: map['description'] ?? '',
      skills: List<String>.from(map['skills'] ?? []),
      location: map['location'] ?? '',
      category: map['category'] ?? '',
      is_new: map['is_new'] ?? false,
      created_at: DateTime.parse(map['created_at'] ?? DateTime.now().toIso8601String()),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'position': position,
      'company': company,
      'company_logo': company_logo,
      'description': description,
      'skills': skills,
      'location': location,
      'category': category,
      'is_new': is_new,
      'created_at': created_at.toIso8601String(),
    };
  }

  JobListingEntity toEntity() {
    return JobListingEntity(
      id: id,
      position: position,
      company: company,
      company_logo: company_logo,
      description: description,
      skills: skills,
      location: location,
      category: category,
      is_new: is_new,
      created_at: created_at,
    );
  }
}
