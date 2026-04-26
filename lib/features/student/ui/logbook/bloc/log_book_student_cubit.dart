import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/usecases/logbook/get_log_book_student.dart';
import 'package:sitama/features/student/ui/logbook/bloc/log_book_student_state.dart';

class LogBookStudentCubit extends Cubit<LogBookStudentState> {
  final GetLogBookStudentUseCase _getLogBookUseCase;

  LogBookStudentCubit({
    required GetLogBookStudentUseCase getLogBookUseCase,
  })  : _getLogBookUseCase = getLogBookUseCase,
        super(LogBookLoading());

  Future<void> displayLogBook() async {
    emit(LogBookLoading());
    try {
      final result = await _getLogBookUseCase.call();
      result.fold(
        (error) => emit(LoadLogBookFailure(errorMessage: error.toString())),
        (logBookEntity) => emit(LogBookLoaded(logBookEntity: logBookEntity)),
      );
    } catch (e) {
      emit(LoadLogBookFailure(errorMessage: e.toString()));
    }
  }
}
