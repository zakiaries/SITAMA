import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/features/student/data/models/job_listing.dart';
import 'package:sitama/service_locator.dart';

final _baseUrl = '${ApiUrls.baseUrl}job-listings';

abstract class JobListingApiService {
  Future<Either> getJobListings();
  Future<Either> getJobListingsByCategory(String category);
  Future<Either> searchJobListings(String query);
  Future<Either> getAiRecommendation();
}

class JobListingApiServiceImpl implements JobListingApiService {
  @override
  Future<Either> getJobListings() async {
    try {
      final response = await sl<DioClient>().get(_baseUrl);
      final jobs = (response.data['job_listings'] as List)
          .map((j) => JobListingModel.fromMap(j))
          .toList();
      return Right(jobs);
    } on DioException catch (e) {
      return Left(e.message ?? 'Error fetching job listings');
    }
  }

  @override
  Future<Either> getJobListingsByCategory(String category) async {
    try {
      final response = await sl<DioClient>().get(
        _baseUrl,
        queryParameters: {'category': category},
      );
      final jobs = (response.data['job_listings'] as List)
          .map((j) => JobListingModel.fromMap(j))
          .toList();
      return Right(jobs);
    } on DioException catch (e) {
      return Left(e.message ?? 'Error fetching by category');
    }
  }

  @override
  Future<Either> searchJobListings(String query) async {
    try {
      final response = await sl<DioClient>().get(
        _baseUrl,
        queryParameters: {'search': query},
      );
      final jobs = (response.data['job_listings'] as List)
          .map((j) => JobListingModel.fromMap(j))
          .toList();
      return Right(jobs);
    } on DioException catch (e) {
      return Left(e.message ?? 'Error searching job listings');
    }
  }

  @override
  Future<Either> getAiRecommendation() async {
    try {
      final response = await sl<DioClient>().get(_baseUrl);
      final jobs = response.data['job_listings'] as List;
      if (jobs.isEmpty) return Left('No jobs available');
      jobs.shuffle();
      return Right(JobListingModel.fromMap(jobs.first));
    } on DioException catch (e) {
      return Left(e.message ?? 'Error getting recommendation');
    }
  }
}
