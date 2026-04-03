import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer/data/static_data.dart';
import 'package:sitama/features/lecturer/ui/profile/bloc/profile_lecturer_state.dart';

class ProfileLecturerCubit extends Cubit<ProfileLecturerState> {
  ProfileLecturerCubit() : super(LecturerLoading());

  void displayLecturer() async {
    try {
      // Emit static profile data with a small delay to simulate loading
      await Future.delayed(Duration(milliseconds: 500));
      final staticData = getStaticLecturerProfileData();
      emit(LecturerLoaded(lecturerProfileEntity: staticData));
    } catch (e) {
      emit(LoadLecturerFailure(errorMessage: e.toString()));
    }
  }
}
