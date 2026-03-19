import 'package:dartz/dartz.dart';

abstract class JobListingRepository {
  Future<Either> getJobListings();
  Future<Either> getJobListingsByCategory(String category);
  Future<Either> searchJobListings(String query);
  Future<Either> getAiRecommendation();
}
