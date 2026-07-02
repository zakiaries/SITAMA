import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/features/auth/domain/usecases/is_logged_in.dart';
import 'package:sitama/features/auth/ui/bloc/auth_state.dart';
import 'package:sitama/service_locator.dart';

class AuthStateCubit extends Cubit<AuthState>{
  AuthStateCubit() : super(AppInitialState());

  void appStarted() async {
    final isLoggedIn = await sl<IsLoggedInUseCase>().call();

    if (isLoggedIn) {
      final prefs = await SharedPreferences.getInstance();
      final role = prefs.getString('role');

      if (role == 'Student') {
        final status = prefs.getString('student_status') ?? 'active';
        emit(_studentStateFor(status));
      } else if (role == 'Lecturer') {
        emit(AuthenticatedLecturer());
      } else if (role == 'Lecturer Industry') {
        emit(AuthenticatedLecturerIndustry());
      } else {
        // Kaprodi dan Industri tidak diizinkan di mobile
        prefs.clear();
        emit(UnAuthenticated());
      }
    } else {
      emit(UnAuthenticated());
    }
  }

  AuthState _studentStateFor(String status) {
    switch (status) {
      case 'pending':
        return AuthenticatedStudentPending();
      case 'rejected':
        return AuthenticatedStudentRejected();
      default:
        return AuthenticatedStudent();
    }
  }

}