import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/entities/seminar_entity.dart';

class SeminarCubit extends Cubit<SeminarState> {
  SeminarCubit() : super(SeminarLoading());

  Future<void> displaySeminars() async {
    try {
      emit(SeminarLoading());

      // Mock data - seminars
      final seminars = [
        SeminarEntity(
          id: 1,
          title: 'Hasil Magang dan Pembelajaran',
          program: 'Program Teknik Informatika',
          date: DateTime(2025, 1, 25),
          time: '13:00 - 15:00 WIB',
          location: 'Aula Blok B Lantai 3',
          organizer: 'Dr. Rina Fiati, S.Kom., M.Cs.\nAida Fitri, S.Kom., M.Sc.',
          description: 'Seminar wajib hasil magang untuk semua mahasiswa',
          qrCode: 'mock_qr_code_1',
          status: 'scheduled',
        ),
        SeminarEntity(
          id: 2,
          title: 'Flutter Development Workshop',
          program: 'Program Teknik Informatika',
          date: DateTime(2025, 2, 1),
          time: '10:00 - 12:00 WIB',
          location: 'Aula Blok C Lantai 2',
          organizer: 'Budi Hermawan, S.Kom., M.Eng.\nSiti Nurhaliza, S.Kom.',
          description: 'Workshop pengembangan aplikasi mobile menggunakan Flutter',
          qrCode: 'mock_qr_code_2',
          status: 'scheduled',
        ),
        SeminarEntity(
          id: 3,
          title: 'Sharing Pengalaman Praktik Industri',
          program: 'Program Teknik Informatika',
          date: DateTime(2025, 1, 18),
          time: '14:00 - 16:00 WIB',
          location: 'Aula Blok A Lantai 1',
          organizer: 'Prof. Ahmad Syaraf, Ph.D.\nDr. Yusri, S.Kom., M.M.',
          description: 'Sharing session dari alumni tentang pengalaman magang mereka',
          qrCode: 'mock_qr_code_3',
          status: 'scheduled',
        ),
      ];

      emit(SeminarLoaded(seminars));
    } catch (e) {
      emit(SeminarError(message: e.toString()));
    }
  }
}

abstract class SeminarState {}

class SeminarInitial extends SeminarState {}

class SeminarLoading extends SeminarState {}

class SeminarLoaded extends SeminarState {
  final List<SeminarEntity> seminars;

  SeminarLoaded(this.seminars);
}

class SeminarError extends SeminarState {
  final String message;

  SeminarError({required this.message});
}
