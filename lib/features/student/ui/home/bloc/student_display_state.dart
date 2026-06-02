
import 'package:sitama/features/student/domain/entities/notification_entity.dart';
import 'package:sitama/features/student/domain/entities/student_home_entity.dart';

abstract class StudentDisplayState {}

class StudentLoading extends StudentDisplayState{}

class StudentNotMapped extends StudentDisplayState {
  final String name;
  StudentNotMapped({required this.name});
}

class StudentLoaded extends StudentDisplayState {
  final StudentHomeEntity studentHomeEntity;
  final NotificationDataEntity? notifications;  

  StudentLoaded({
    required this.studentHomeEntity,
    this.notifications,
  });
}

class LoadStudentFailure extends StudentDisplayState {
  final String errorMessage;

  LoadStudentFailure({required this.errorMessage}); 
}