import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/data/repositories/lecturer_industry_repository.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_home_entity.dart';
import 'package:sitama/service_locator.dart';

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

class LecturerIndustryDisplayCubit extends Cubit<LecturerIndustryDisplayState> {
  LecturerIndustryDisplayCubit() : super(Loading());

  Future<void> displayLecturerIndus() async {
    emit(Loading());
    try {
      final result = await sl<LecturerIndustryRepository>().getHome();
      result.fold(
        (error) => emit(Failure(errorMessage: error.toString())),
        (entity) => emit(DetailLoaded(data: entity)),
      );
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }
}
