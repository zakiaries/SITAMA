import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/service_locator.dart';

const _base = '${ApiUrls.baseUrl}lecturer-industry';

abstract class LecturerIndustryApiService {
  Future<Either> getHome();
  Future<Either> getDetailStudent(int studentId);
  Future<Either> addLogbookComment(int logbookId, String comment);
  Future<Either> submitScores(int studentId, List<Map<String, dynamic>> scores,
      {String? performanceNotes, String? performanceNotesBy});
  Future<Either> getProfile();
}

class LecturerIndustryApiServiceImpl extends LecturerIndustryApiService {
  Future<Options> get _authOptions async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    return Options(headers: {'Authorization': 'Bearer $token'});
  }

  @override
  Future<Either> getHome() async {
    try {
      final response = await sl<DioClient>().get('$_base/home', options: await _authOptions);
      return Right(response);
    } on DioException catch (e) {
      return Left(e.response?.data['errors']['message'] ?? e.message);
    }
  }

  @override
  Future<Either> getDetailStudent(int studentId) async {
    try {
      final response = await sl<DioClient>().get('$_base/detailStudent/$studentId', options: await _authOptions);
      return Right(response);
    } on DioException catch (e) {
      return Left(e.response?.data['errors']['message'] ?? e.message);
    }
  }

  @override
  Future<Either> addLogbookComment(int logbookId, String comment) async {
    try {
      final response = await sl<DioClient>().post(
        '$_base/logBook/$logbookId/comment',
        options: await _authOptions,
        data: {'comment': comment},
      );
      return Right(response);
    } on DioException catch (e) {
      return Left(e.response?.data['errors']?.toString() ?? e.message);
    }
  }

  @override
  Future<Either> submitScores(int studentId, List<Map<String, dynamic>> scores,
      {String? performanceNotes, String? performanceNotesBy}) async {
    try {
      final response = await sl<DioClient>().post(
        '$_base/addAssessment/$studentId',
        options: await _authOptions,
        data: {
          'scores': scores,
          if (performanceNotes != null) 'performance_notes': performanceNotes,
          if (performanceNotesBy != null) 'performance_notes_by': performanceNotesBy,
        },
      );
      return Right(response);
    } on DioException catch (e) {
      return Left(e.response?.data['errors']?.toString() ?? e.message);
    }
  }

  @override
  Future<Either> getProfile() async {
    try {
      final response = await sl<DioClient>().get('$_base/profile', options: await _authOptions);
      return Right(response);
    } on DioException catch (e) {
      return Left(e.response?.data['errors']['message'] ?? e.message);
    }
  }
}
