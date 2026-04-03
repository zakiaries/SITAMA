import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';
import 'package:sitama/features/lecturer_industry/data/static_industry_data.dart';

// ──── STATES ────
abstract class IndustryScoreState {}

class Loading extends IndustryScoreState {}

class Loaded extends IndustryScoreState {
  final IndustryScoreEntity scores;
  Loaded({required this.scores});
}

class ScoreSaved extends IndustryScoreState {
  final String message;
  ScoreSaved({required this.message});
}

class Failure extends IndustryScoreState {
  final String errorMessage;
  Failure({required this.errorMessage});
}

// ──── CUBIT ────
class IndustryScoreCubit extends Cubit<IndustryScoreState> {
  IndustryScoreCubit() : super(Loading());

  void fetchScores(int studentId) async {
    try {
      await Future.delayed(const Duration(milliseconds: 500));
      final data = getStaticIndustryDetailStudentData(studentId);
      if (data.assessment_scores != null) {
        emit(Loaded(scores: data.assessment_scores!));
      } else {
        emit(Failure(errorMessage: 'Data penilaian tidak tersedia'));
      }
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }

  void saveScores(int studentId, String notes) async {
    try {
      await Future.delayed(const Duration(milliseconds: 800));
      emit(ScoreSaved(
          message:
              'Penilaian berhasil disimpan dan dikirim ke sistem'));
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }
}
