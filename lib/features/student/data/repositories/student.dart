import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';
import 'package:sitama/features/shared/data/models/guidance.dart';
import 'package:sitama/features/shared/data/models/log_book.dart';
import 'package:sitama/features/student/data/models/notification.dart';
import 'package:sitama/features/student/data/models/student_home.dart';
import 'package:sitama/features/student/domain/entities/notification_entity.dart';
import 'package:sitama/features/student/data/sources/student_api_service.dart';
import 'package:sitama/features/student/domain/repositories/student.dart';
import 'package:sitama/service_locator.dart';

class StudentRepositoryImpl extends StudentRepository {
  @override
  Future<Either> getStudentHome() async {
    Either result = await sl<StudentApiService>().getStudentHome();
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          var dataModel = StudentHomeModel.fromMap(response.data['data']);
          return Right(dataModel.toEntity());
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either> getGuidances() async {
    Either result = await sl<StudentApiService>().getGuidances();
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          var dataModel = ListGuidanceModel.fromMap(response.data);
          return Right(dataModel.toEntity());
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either> addGuidances(AddGuidanceReqParams request) async {
    Either result = await sl<StudentApiService>().addGuidances(request);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> editGuidances(EditGuidanceReqParams request) async {
    Either result = await sl<StudentApiService>().editGuidances(request);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> deleteGuidances(int id) async {
    Either result = await sl<StudentApiService>().deleteGuidances(id);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> getLogBook() async {
    Either result = await sl<StudentApiService>().getLogBook();
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          var dataModel = ListLogBookModel.fromMap(response.data);
          return Right(dataModel.toEntity());
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either> addLogBook(AddLogBookReqParams request) async {
    Either result = await sl<StudentApiService>().addLogBook(request);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> editLogBook(EditLogBookReqParams request) async {
    Either result = await sl<StudentApiService>().editLogBook(request);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> deleteLogBook(int id) async {
    Either result = await sl<StudentApiService>().deleteLogBook(id);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> getStudentProfile() async {
    Either result = await sl<StudentApiService>().getStudentProfile();
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          var dataModel = StudentProfileModel.fromMap(response.data);
          return Right(dataModel.toEntity());
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either> getNotifications() async {
    Either result = await sl<StudentApiService>().getNotifications();
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          var dataModel = NotificationDataModel.fromJson(response.data);
          return Right(dataModel.toEntity());
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either> markAllNotificationsAsRead() async {
    Either result = await sl<StudentApiService>().markAllNotificationsAsRead();
    return result.fold((error) => Left(error), (data) => Right(data));
  }
}
