part of 'job_listing_cubit.dart';

@immutable
abstract class JobListingState {}

class JobListingInitial extends JobListingState {}

class JobListingLoading extends JobListingState {}

class JobListingLoaded extends JobListingState {
  final List<JobListingEntity> jobListings;
  final JobListingEntity? aiRecommendation;
  final String selectedCategory;

  JobListingLoaded({
    required this.jobListings,
    this.aiRecommendation,
    this.selectedCategory = 'Semua',
  });

  JobListingLoaded copyWith({
    List<JobListingEntity>? jobListings,
    JobListingEntity? aiRecommendation,
    String? selectedCategory,
  }) {
    return JobListingLoaded(
      jobListings: jobListings ?? this.jobListings,
      aiRecommendation: aiRecommendation ?? this.aiRecommendation,
      selectedCategory: selectedCategory ?? this.selectedCategory,
    );
  }
}

class JobListingError extends JobListingState {
  final String message;

  JobListingError(this.message);
}
