import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer/domain/usecases/get_home_lecturer.dart';
import 'package:sitama/features/lecturer/domain/entities/lecturer_home_entity.dart';
import 'package:sitama/features/lecturer/ui/home/bloc/display/lecturer_display_state.dart';
import 'package:sitama/features/lecturer/ui/home/bloc/selection/selection_bloc.dart';

class LecturerDisplayCubit extends Cubit<LecturerDisplayState> {
  final SelectionBloc selectionBloc;
  final GetHomeLecturerUseCase _getHomeLecturerUseCase;

  LecturerDisplayCubit({
    required this.selectionBloc,
    required GetHomeLecturerUseCase getHomeLecturerUseCase,
  })  : _getHomeLecturerUseCase = getHomeLecturerUseCase,
        super(LecturerLoading()) {
    selectionBloc.stream.listen((selectionState) {
      if (state is LecturerLoaded && !selectionState.isLocalOperation) {
        displayLecturer();
      }
    });
    displayLecturer();
  }

  Future<void> displayLecturer() async {
    emit(LecturerLoading());
    try {
      final result = await _getHomeLecturerUseCase.call();
      result.fold(
        (error) => emit(LoadLecturerFailure(errorMessage: error.toString())),
        (lecturerHomeEntity) =>
            emit(LecturerLoaded(lecturerHomeEntity: lecturerHomeEntity)),
      );
    } catch (e) {
      emit(LoadLecturerFailure(errorMessage: e.toString()));
    }
  }

  void updateLocalData(LecturerHomeEntity newData) {
    emit(LecturerLoaded(lecturerHomeEntity: newData));
  }
}
