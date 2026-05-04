import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/entities/guidance_entity.dart';
import 'package:sitama/features/student/ui/guidance/bloc/guidance_student_state.dart';

class GuidanceStudentCubit extends Cubit<GuidanceStudentState> {
  GuidanceStudentCubit() : super(GuidanceLoading());

  Future<void> displayGuidance() async {
    try {
      // Mock data untuk development tanpa backend
      final mockGuidances = [
        GuidanceEntity(
          id: 1,
          title: 'Konsultasi Magang',
          activity: 'Diskusi tentang rencana kerja magang',
          date: DateTime.now().subtract(Duration(days: 5)),
          lecturer_note: 'Bagus, lanjutkan dengan implementasi',
          name_file: 'rencana_kerja.pdf',
          status: 'approved',
        ),
        GuidanceEntity(
          id: 2,
          title: 'Progress Update',
          activity: 'Update progress minggu pertama',
          date: DateTime.now().subtract(Duration(days: 3)),
          lecturer_note: 'Perlu perbaikan di bagian X',
          name_file: 'progress_week1.pdf',
          status: 'in-progress',
        ),
        GuidanceEntity(
          id: 3,
          title: 'Presentasi Hasil',
          activity: 'Presentasi hasil kerja magang',
          date: DateTime.now().subtract(Duration(days: 1)),
          lecturer_note: 'Excellent work!',
          name_file: 'presentasi_final.pdf',
          status: 'approved',
        ),
      ];

      emit(GuidanceLoaded(
        guidanceEntity: ListGuidanceEntity(guidances: mockGuidances),
      ));
    } catch (e) {
      emit(LoadGuidanceFailure(errorMessage: e.toString()));
    }
  }
}
