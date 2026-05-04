import 'package:dartz/dartz.dart';
import 'package:sitama/features/student/domain/repositories/job_listing.dart';
import 'package:sitama/service_locator.dart';
import 'package:sitama/utils/usecase.dart';

class GetJobListingsByCategoryUseCase implements UseCase<Either, String> {
  @override
  Future<Either> call({String param = ''}) async {
    return await sl<JobListingRepository>().getJobListingsByCategory(param);
  }
}
