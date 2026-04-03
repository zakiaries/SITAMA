import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer/data/static_data.dart';
import 'package:sitama/features/lecturer/ui/detail_student/bloc/detail_student_display_state.dart';

class DetailStudentDisplayCubit extends Cubit<DetailStudentDisplayState> {
  DetailStudentDisplayCubit() : super(DetailLoading());

  void displayStudent(int id) async {
    try {
      // Emit static student detail data with a small delay to simulate loading
      await Future.delayed(Duration(milliseconds: 500));
      final staticData = getStaticDetailStudentData(id);
      emit(DetailLoaded(
        detailStudentEntity: staticData,
        isOffline: false,
      ));
    } catch (e) {
      emit(DetailFailure(
        errorMessage: e.toString(),
        isOffline: false,
      ));
    }
  }

  Future<void> updateStudentStatus({
    required int id,
    required bool status,
  }) async {
    try {
      // Simulate status update
      await Future.delayed(Duration(milliseconds: 800));
      displayStudent(id);
    } catch (e) {
      emit(DetailFailure(
        errorMessage: e.toString(),
        isOffline: false,
      ));
    }
  }

  void toggleInternshipApproval(int index) {
    // Simulate intern ship approval toggle (static)
    try {
      // In static mode, just reload the student data
      if (state is DetailLoaded) {
        final currentState = state as DetailLoaded;
        emit(DetailLoaded(
          detailStudentEntity: currentState.detailStudentEntity,
          isOffline: false,
        ));
      }
    } catch (e) {
      emit(DetailFailure(
        errorMessage: e.toString(),
        isOffline: false,
      ));
    }
  }
}
