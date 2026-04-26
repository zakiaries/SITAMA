import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';
import 'package:sitama/features/lecturer/data/models/assessment.dart';
import 'package:sitama/features/lecturer/data/models/lecturer_detail_student.dart';
import 'package:sitama/features/lecturer/data/models/lecturer_home.dart';
import 'package:sitama/features/lecturer/data/models/lecturer_profile.dart';
import 'package:sitama/features/lecturer/data/sources/lecturer_api_service.dart';
import 'package:sitama/features/lecturer/domain/repositories/lecturer.dart';
import 'package:sitama/features/shared/data/models/guidance.dart';
import 'package:sitama/features/shared/data/models/log_book.dart';
import 'package:sitama/features/student/data/models/notification.dart';
import 'package:sitama/service_locator.dart';

class LecturerRepositoryImpl extends LecturerRepository {
  @override
  Future<Either> getLecturerHome() async {
    Either result = await sl<LecturerApiService>().getLecturerHome();
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          var dataModel = LecturerHomeModel.fromMap(response.data);
          return Right(dataModel.toEntity());
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either> getDetailStudent(int id) async {
    Either result = await sl<LecturerApiService>().getDetailStudent(id);
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          var dataModel = DetailStudentModel.fromMap(response.data);
          return Right(dataModel.toEntity());
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either> updateStatusGuidance(UpdateStatusGuidanceReqParams request) async {
    Either result = await sl<LecturerApiService>().updateStatusGuidance(request);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> updateLogBookNote(UpdateLogBookReqParams request) async {
    Either result = await sl<LecturerApiService>().updateLogBookNote(request);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> getLecturerProfile() async {
    Either result = await sl<LecturerApiService>().getLecturerProfile();
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          var dataModel = LecturerProfileModel.fromMap(response.data);
          return Right(dataModel.toEntity());
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either> fetchAssessments(int id) async {
    Either result = await sl<LecturerApiService>().fetchAssessments(id);
    return result.fold(
      (error) => Left(error),
      (data) {
        Response response = data;
        try {
          final list = (response.data as List)
              .map((item) => AssessmentModel.fromMap(item).toEntity())
              .toList();
          return Right(list);
        } catch (e) {
          return Left("Parsing error: $e");
        }
      },
    );
  }

  @override
  Future<Either<String, Response>> submitScores(
      int id, List<Map<String, dynamic>> scores) async {
    Either result = await sl<LecturerApiService>().submitScores(id, scores);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either> updateFinishedStudent({required bool status, required int id}) async {
    Either result = await sl<LecturerApiService>().updateFinishedStudent(status, id);
    return result.fold((error) => Left(error), (data) => Right(data));
  }

  @override
  Future<Either<String, Response>> addNotification(AddNotificationReqParams request) async {
    return await sl<LecturerApiService>().addNotification(request);
  }
}
