import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/domain/entities/student_home_entity.dart';
import 'package:sitama/features/student/domain/entities/guidance_entity.dart';
import 'package:sitama/features/student/domain/entities/log_book_entity.dart';
import 'package:sitama/features/student/domain/entities/notification_entity.dart';
import 'package:sitama/features/student/ui/home/bloc/student_display_state.dart';

class StudentDisplayCubit extends Cubit<StudentDisplayState> {
  StudentDisplayCubit() : super(StudentLoading());

  void displayStudent() async {
    try {
      // Mock data untuk student home
      final mockGuidances = [
        GuidanceEntity(
          id: 1,
          title: 'Konsultasi Magang',
          activity: 'Diskusi progress project',
          date: DateTime.now().subtract(Duration(days: 5)),
          lecturer_note: 'Lanjutkan dengan sprint berikutnya',
          name_file: '',
          status: 'approved',
        ),
      ];

      final mockLogBooks = [
        LogBookEntity(
          id: 1,
          title: 'Hari Pertama Magang',
          activity: 'Setup development environment',
          date: DateTime.now().subtract(Duration(days: 10)),
          lecturer_note: 'Perkenalan dengan tim',
        ),
      ];

      final studentHomeEntity = StudentHomeEntity(
        name: 'Budi Santoso',
        latest_guidances: mockGuidances,
        latest_log_books: mockLogBooks,
      );

      // Mock data untuk notifications
      final mockNotifications = NotificationDataEntity(
        notifications: [
          NotificationItemEntity(
            id: 1,
            userId: 1,
            message: 'Pembimbing Anda telah memberikan feedback',
            date: DateTime.now().subtract(Duration(days: 1)).toString(),
            category: 'general',
            isRead: 0,
            detailText: 'Silakan cek feedback di bagian bimbingan',
            createdAt: DateTime.now().subtract(Duration(days: 1)).toString(),
            updatedAt: DateTime.now().subtract(Duration(days: 1)).toString(),
          ),
          NotificationItemEntity(
            id: 2,
            userId: 1,
            message: 'Jadwal bimbingan telah ditentukan',
            date: DateTime.now().subtract(Duration(days: 2)).toString(),
            category: 'general',
            isRead: 1,
            detailText: 'Jadwal: Kamis, 14:00',
            createdAt: DateTime.now().subtract(Duration(days: 2)).toString(),
            updatedAt: DateTime.now().subtract(Duration(days: 2)).toString(),
          ),
        ],
      );

      emit(StudentLoaded(
        studentHomeEntity: studentHomeEntity,
        notifications: mockNotifications,
      ));
    } catch (e) {
      emit(LoadStudentFailure(errorMessage: e.toString()));
    }
  }
}
