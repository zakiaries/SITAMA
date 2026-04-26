import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/usecases/general/get_home_student.dart';
import 'package:sitama/features/student/domain/usecases/notification/get_notification.dart';
import 'package:sitama/features/student/ui/home/bloc/student_display_state.dart';

class StudentDisplayCubit extends Cubit<StudentDisplayState> {
  final GetHomeStudentUseCase _getHomeStudentUseCase;
  final GetNotificationsUseCase _getNotificationsUseCase;

  StudentDisplayCubit({
    required GetHomeStudentUseCase getHomeStudentUseCase,
    required GetNotificationsUseCase getNotificationsUseCase,
  })  : _getHomeStudentUseCase = getHomeStudentUseCase,
        _getNotificationsUseCase = getNotificationsUseCase,
        super(StudentLoading());

  Future<void> displayStudent() async {
    emit(StudentLoading());
    try {
      final homeResult = await _getHomeStudentUseCase.call();
      final notifResult = await _getNotificationsUseCase.call();

      homeResult.fold(
        (error) => emit(LoadStudentFailure(errorMessage: error.toString())),
        (studentHomeEntity) {
          notifResult.fold(
            (error) => emit(LoadStudentFailure(errorMessage: error.toString())),
            (notifications) => emit(StudentLoaded(
              studentHomeEntity: studentHomeEntity,
              notifications: notifications,
            )),
          );
        },
      );
    } catch (e) {
      emit(LoadStudentFailure(errorMessage: e.toString()));
    }
  }
}
