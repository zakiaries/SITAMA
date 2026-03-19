import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/entities/student_home_entity.dart';
import 'package:sitama/features/lecturer/domain/entities/lecturer_detail_student.dart';
import 'package:sitama/features/student/ui/profile/bloc/profile_student_state.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ProfileStudentCubit extends Cubit<ProfileStudentState> {
  final SharedPreferences prefs;
  
  ProfileStudentCubit({
    required this.prefs,
  }) : super(StudentLoading());

  void displayStudent() async {
    try {
      // Mock data untuk development
      final mockProfile = StudentProfileEntity(
        name: 'Budi Santoso',
        username: 'student',
        email: 'budi.santoso@student.edu',
        photo_profile: null,
        internships: [
          InternshipStudentEntity(
            name: 'PT. Maju Jaya Indonesia - Flutter Developer',
            start_date: DateTime.now().subtract(Duration(days: 60)),
            end_date: DateTime.now().add(Duration(days: 30)),
            status: 'active',
          ),
          InternshipStudentEntity(
            name: 'PT. Digital Indonesia - Frontend Developer',
            start_date: DateTime.now().subtract(Duration(days: 180)),
            end_date: DateTime.now().subtract(Duration(days: 120)),
            status: 'completed',
          ),
        ],
      );

      emit(StudentLoaded(studentProfileEntity: mockProfile));
    } catch (e) {
      emit(LoadStudentFailure(errorMessage: e.toString()));
    }
  }
}
