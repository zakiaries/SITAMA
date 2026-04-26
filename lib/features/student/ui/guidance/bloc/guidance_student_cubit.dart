import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/usecases/guidances/get_guidances_student.dart';
import 'package:sitama/features/student/ui/guidance/bloc/guidance_student_state.dart';

class GuidanceStudentCubit extends Cubit<GuidanceStudentState> {
  final GetGuidancesStudentUseCase _getGuidancesUseCase;

  GuidanceStudentCubit({
    required GetGuidancesStudentUseCase getGuidancesUseCase,
  })  : _getGuidancesUseCase = getGuidancesUseCase,
        super(GuidanceLoading());

  Future<void> displayGuidance() async {
    emit(GuidanceLoading());
    try {
      final result = await _getGuidancesUseCase.call();
      result.fold(
        (error) => emit(LoadGuidanceFailure(errorMessage: error.toString())),
        (guidanceEntity) => emit(GuidanceLoaded(guidanceEntity: guidanceEntity)),
      );
    } catch (e) {
      emit(LoadGuidanceFailure(errorMessage: e.toString()));
    }
  }
}
