import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter/material.dart';
import 'package:sitama/features/student/domain/entities/job_listing_entity.dart';
import 'package:sitama/features/student/domain/usecases/job_listing/get_job_listings.dart';
import 'package:sitama/features/student/domain/usecases/job_listing/search_job_listings.dart';
import 'package:sitama/features/student/domain/usecases/job_listing/get_job_listings_by_category.dart';
import 'package:sitama/features/student/domain/usecases/job_listing/get_ai_recommendation.dart';
import 'package:sitama/service_locator.dart';

part 'job_listing_state.dart';

class JobListingCubit extends Cubit<JobListingState> {
  JobListingCubit() : super(JobListingInitial());

  final List<String> categories = [
    'Semua',
    'IT',
    'Desain',
    'Data',
    'Marketing'
  ];

  Future<void> getJobListings() async {
    emit(JobListingLoading());

    var result = await sl<GetJobListingsUseCase>().call();
    var recommendationResult = await sl<GetAiRecommendationUseCase>().call();

    result.fold(
      (error) => emit(JobListingError(error.toString())),
      (jobListings) {
        JobListingEntity? recommendation;
        recommendationResult.fold(
          (error) => null,
          (data) => recommendation = data,
        );

        emit(JobListingLoaded(
          jobListings: jobListings,
          aiRecommendation: recommendation,
          selectedCategory: 'Semua',
        ));
      },
    );
  }

  Future<void> filterByCategory(String category) async {
    if (category == 'Semua') {
      await getJobListings();
      if (state is JobListingLoaded) {
        emit((state as JobListingLoaded).copyWith(selectedCategory: category));
      }
    } else {
      emit(JobListingLoading());

      var result =
          await sl<GetJobListingsByCategoryUseCase>().call(param: category);

      result.fold(
        (error) => emit(JobListingError(error.toString())),
        (jobListings) {
          if (state is JobListingLoaded) {
            var currentState = state as JobListingLoaded;
            emit(JobListingLoaded(
              jobListings: jobListings,
              aiRecommendation: currentState.aiRecommendation,
              selectedCategory: category,
            ));
          }
        },
      );
    }
  }

  Future<void> searchJobListings(String query) async {
    if (query.isEmpty) {
      await getJobListings();
      return;
    }

    emit(JobListingLoading());

    var result = await sl<SearchJobListingsUseCase>().call(param: query);

    result.fold(
      (error) => emit(JobListingError(error.toString())),
      (jobListings) {
        if (state is JobListingLoaded) {
          var currentState = state as JobListingLoaded;
          emit(JobListingLoaded(
            jobListings: jobListings,
            aiRecommendation: currentState.aiRecommendation,
            selectedCategory: currentState.selectedCategory,
          ));
        }
      },
    );
  }
}
