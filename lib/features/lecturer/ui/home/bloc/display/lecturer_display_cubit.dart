import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer/domain/entities/lecturer_home_entity.dart';
import 'package:sitama/features/lecturer/data/static_data.dart';
import 'package:sitama/features/lecturer/ui/home/bloc/display/lecturer_display_state.dart';
import 'package:sitama/features/lecturer/ui/home/bloc/selection/selection_bloc.dart';

class LecturerDisplayCubit extends Cubit<LecturerDisplayState> {
  final SelectionBloc selectionBloc;
  late LecturerHomeEntity _staticData;

  LecturerDisplayCubit({
    required this.selectionBloc,
  }) : super(LecturerLoading()) {
    selectionBloc.stream.listen((selectionState) {
      if (state is LecturerLoaded && !selectionState.isLocalOperation) {
        displayLecturer();
      }
    });
    // Load static data on initialization
    _initializeData();
  }

  void _initializeData() {
    _staticData = getStaticLecturerHomeData();
    displayLecturer();
  }

  Future<void> displayLecturer() async {
    try {
      // Emit static data with a small delay to simulate loading
      await Future.delayed(Duration(milliseconds: 500));
      emit(LecturerLoaded(lecturerHomeEntity: _staticData));
    } catch (e) {
      emit(LoadLecturerFailure(errorMessage: e.toString()));
    }
  }

  void updateLocalData(LecturerHomeEntity newData) {
    _staticData = newData;
    emit(LecturerLoaded(lecturerHomeEntity: newData));
  }
}
