import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer/data/static_data.dart';
import 'package:sitama/features/lecturer/ui/input_score/bloc/assessment_state.dart';

class AssessmentCubit extends Cubit<AssessmentState> {
  AssessmentCubit() : super(AssessmentLoading());

  void fetchAssessments(int id) async {
    try {
      // Emit static assessment data with a small delay to simulate loading
      await Future.delayed(Duration(milliseconds: 500));
      final staticData = getStaticAssessmentData(id);
      emit(AssessmentLoaded(assessments: staticData));
    } catch (e) {
      emit(LoadAssessmentFailure(errorMessage: e.toString()));
    }
  }

  Future<void> submitScores(int id, List<dynamic> scores) async {
    try {
      // Simulate score submission
      await Future.delayed(Duration(milliseconds: 800));
      emit(AssessmentSubmittedSuccess());
      // Reload assessments after submission
      await Future.delayed(Duration(milliseconds: 300));
      fetchAssessments(id);
    } catch (e) {
      emit(AssessmentSubmissionFailed(errorMessage: e.toString()));
    }
  }
}
