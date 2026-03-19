import 'package:dartz/dartz.dart';
import 'package:sitama/features/student/data/models/job_listing.dart';
import 'package:sitama/features/student/data/sources/job_listing_api_service.dart';
import 'package:sitama/features/student/domain/repositories/job_listing.dart';
import 'package:sitama/service_locator.dart';

class JobListingRepositoryImpl extends JobListingRepository {
  @override
  Future<Either> getJobListings() async {
    Either result = await sl<JobListingApiService>().getJobListings();
    return result.fold(
      (error) => Left(error),
      (data) {
        List<JobListingModel> jobs = List<JobListingModel>.from(data);
        return Right(jobs.map((job) => job.toEntity()).toList());
      },
    );
  }

  @override
  Future<Either> getJobListingsByCategory(String category) async {
    Either result = await sl<JobListingApiService>().getJobListingsByCategory(category);
    return result.fold(
      (error) => Left(error),
      (data) {
        List<JobListingModel> jobs = List<JobListingModel>.from(data);
        return Right(jobs.map((job) => job.toEntity()).toList());
      },
    );
  }

  @override
  Future<Either> searchJobListings(String query) async {
    Either result = await sl<JobListingApiService>().searchJobListings(query);
    return result.fold(
      (error) => Left(error),
      (data) {
        List<JobListingModel> jobs = List<JobListingModel>.from(data);
        return Right(jobs.map((job) => job.toEntity()).toList());
      },
    );
  }

  @override
  Future<Either> getAiRecommendation() async {
    Either result = await sl<JobListingApiService>().getAiRecommendation();
    return result.fold(
      (error) => Left(error),
      (data) {
        JobListingModel job = data as JobListingModel;
        return Right(job.toEntity());
      },
    );
  }
}
