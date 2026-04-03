import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_home_entity.dart';
import 'package:sitama/features/lecturer_industry/data/static_industry_data.dart';

// ──── STATES ────
abstract class LecturerIndustryDisplayState {}

class Loading extends LecturerIndustryDisplayState {}

class DetailLoaded extends LecturerIndustryDisplayState {
  final LecturerIndustryHomeEntity data;
  DetailLoaded({required this.data});
}

class Failure extends LecturerIndustryDisplayState {
  final String errorMessage;
  Failure({required this.errorMessage});
}

// ──── CUBIT ────
class LecturerIndustryDisplayCubit extends Cubit<LecturerIndustryDisplayState> {
  LecturerIndustryDisplayCubit() : super(Loading());

  void displayLecturerIndus() async {
    try {
      await Future.delayed(const Duration(milliseconds: 500));
      final data = getStaticIndustryLecturerHomeData();
      emit(DetailLoaded(data: data));
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }
}
