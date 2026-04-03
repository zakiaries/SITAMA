import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';
import 'package:sitama/features/lecturer_industry/data/static_industry_data.dart';

// ──── STATES ────
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

// ──── CUBIT ────
class LecturerIndustryDetailCubit extends Cubit<LecturerIndustryDetailState> {
  LecturerIndustryDetailCubit() : super(Loading());

  void displayStudent(int studentId) async {
    try {
      await Future.delayed(const Duration(milliseconds: 500));
      final data = getStaticIndustryDetailStudentData(studentId);
      emit(DetailLoaded(data: data));
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }

  void submitFinalAssessment(int studentId, String notes) async {
    try {
      await Future.delayed(const Duration(milliseconds: 800));
      emit(ScoreSubmitted(
          message:
              'Penilaian akhir berhasil disimpan untuk ${getStaticIndustryDetailStudentData(studentId).student_name}'));
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }
}
