import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/data/repositories/lecturer_industry_repository.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_profile_entity.dart';
import 'package:sitama/service_locator.dart';

abstract class LecturerIndustryProfileState {}
class Loading extends LecturerIndustryProfileState {}
class Loaded extends LecturerIndustryProfileState {
  final LecturerIndustryProfileEntity profile;
  Loaded({required this.profile});
}
class Failure extends LecturerIndustryProfileState {
  final String errorMessage;
  Failure({required this.errorMessage});
}

class LecturerIndustryProfileCubit extends Cubit<LecturerIndustryProfileState> {
  LecturerIndustryProfileCubit() : super(Loading());

  Future<void> displayProfile() async {
    emit(Loading());
    try {
      final result = await sl<LecturerIndustryRepository>().getProfile();
      result.fold(
        (error) => emit(Failure(errorMessage: error.toString())),
        (entity) => emit(Loaded(profile: entity)),
      );
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }
}
