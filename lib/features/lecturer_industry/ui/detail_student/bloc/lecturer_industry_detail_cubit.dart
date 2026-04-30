import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/data/repositories/lecturer_industry_repository.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';
import 'package:sitama/service_locator.dart';

abstract class LecturerIndustryDetailState {}
class Loading extends LecturerIndustryDetailState {}
class DetailLoaded extends LecturerIndustryDetailState {
  final LecturerIndustryDetailStudentEntity data;
  DetailLoaded({required this.data});
}
class ScoreSubmitted extends LecturerIndustryDetailState {
  final String message;
  ScoreSubmitted({required this.message});
}
class Failure extends LecturerIndustryDetailState {
  final String errorMessage;
  Failure({required this.errorMessage});
}

class LecturerIndustryDetailCubit extends Cubit<LecturerIndustryDetailState> {
  LecturerIndustryDetailCubit() : super(Loading());

  Future<void> displayStudent(int studentId) async {
    emit(Loading());
    try {
      final result = await sl<LecturerIndustryRepository>().getDetailStudent(studentId);
      result.fold(
        (error) => emit(Failure(errorMessage: error.toString())),
        (entity) => emit(DetailLoaded(data: entity)),
      );
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }

  Future<void> submitFinalAssessment(
    int studentId,
    List<Map<String, dynamic>> scores,
    String notes,
    String notesBy,
  ) async {
    try {
      final result = await sl<LecturerIndustryRepository>().submitScores(
        studentId, scores,
        performanceNotes: notes,
        performanceNotesBy: notesBy,
      );
      result.fold(
        (error) => emit(Failure(errorMessage: error.toString())),
        (_) => emit(ScoreSubmitted(message: 'Penilaian akhir berhasil disimpan')),
      );
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }
}
