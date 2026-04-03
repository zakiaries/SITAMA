import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_profile_entity.dart';
import 'package:sitama/features/lecturer_industry/data/static_industry_data.dart';

// ──── STATES ────
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

// ──── CUBIT ────
class LecturerIndustryProfileCubit extends Cubit<LecturerIndustryProfileState> {
  LecturerIndustryProfileCubit() : super(Loading());

  void displayProfile() async {
    try {
      await Future.delayed(const Duration(milliseconds: 500));
      final data = getStaticIndustryProfileData();
      emit(Loaded(profile: data));
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }
}
