import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/entities/log_book_entity.dart';
import 'package:sitama/features/student/ui/logbook/bloc/log_book_student_state.dart';

class LogBookStudentCubit extends Cubit<LogBookStudentState> {
  LogBookStudentCubit() : super(LogBookLoading());

  Future<void> displayLogBook() async {
    try {
      // Mock data untuk development
      final mockLogBooks = [
        LogBookEntity(
          id: 1,
          title: 'Hari Pertama Magang',
          date: DateTime.now().subtract(Duration(days: 10)),
          activity: 'Setup development environment dan familiarize dengan codebase',
          lecturer_note: 'Bagus, lanjutkan dengan tahap berikutnya',
        ),
        LogBookEntity(
          id: 2,
          title: 'Implementasi Feature Login',
          date: DateTime.now().subtract(Duration(days: 7)),
          activity: 'Implementasi login page dengan validation',
          lecturer_note: 'Validasi sudah baik, tambahkan error handling',
        ),
        LogBookEntity(
          id: 3,
          title: 'Bug Fixing dan Testing',
          date: DateTime.now().subtract(Duration(days: 3)),
          activity: 'Testing dan bug fixing untuk feature sebelumnya',
          lecturer_note: 'Test coverage perlu ditingkatkan',
        ),
        LogBookEntity(
          id: 4,
          title: 'Dokumentasi Code',
          date: DateTime.now().subtract(Duration(days: 1)),
          activity: 'Dokumentasi lengkap untuk source code',
          lecturer_note: 'Dokumentasi bagus dan informatif',
        ),
      ];

      emit(LogBookLoaded(logBookEntity: ListLogBookEntity(log_books: mockLogBooks)));
    } catch (e) {
      emit(LoadLogBookFailure(errorMessage: e.toString()));
    }
  }
}
