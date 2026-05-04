import 'package:dartz/dartz.dart';
import 'package:sitama/features/student/domain/repositories/job_listing.dart';
import 'package:sitama/service_locator.dart';
import 'package:sitama/utils/usecase.dart';

class GetAiRecommendationUseCase implements UseCase<Either, void> {
  @override
  Future<Either> call({void param}) async {
    return await sl<JobListingRepository>().getAiRecommendation();
  }
}
