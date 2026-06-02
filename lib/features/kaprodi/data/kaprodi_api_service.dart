import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/service_locator.dart';

const _base = '${ApiUrls.baseUrl}kaprodi';

class KaprodiApiService {
  Future<Options> get _auth async {
    final prefs = await SharedPreferences.getInstance();
    return Options(headers: {'Authorization': 'Bearer ${prefs.getString('token')}'});
  }

  Future<Either> getDashboard() async {
    try {
      final r = await sl<DioClient>().get('$_base/dashboard', options: await _auth);
      return Right(r.data as Map<String, dynamic>);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> getProfile() async {
    try {
      final r = await sl<DioClient>().get('$_base/profile', options: await _auth);
      return Right(r.data as Map<String, dynamic>);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> getMahasiswa({String status = 'all', String search = ''}) async {
    try {
      final r = await sl<DioClient>().get(
        '$_base/mahasiswa',
        queryParameters: {'status': status, 'search': search},
        options: await _auth,
      );
      return Right(r.data['mahasiswa'] as List);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> getDosen({String search = ''}) async {
    try {
      final r = await sl<DioClient>().get(
        '$_base/dosen',
        queryParameters: {'search': search},
        options: await _auth,
      );
      return Right(r.data['dosen'] as List);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> getDosenStudents(int lecturerId) async {
    try {
      final r = await sl<DioClient>().get('$_base/dosen/$lecturerId/students', options: await _auth);
      return Right(r.data as Map<String, dynamic>);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> getIndustri({String status = 'pending', String search = ''}) async {
    try {
      final r = await sl<DioClient>().get(
        '$_base/industri',
        queryParameters: {'status': status, 'search': search},
        options: await _auth,
      );
      return Right(r.data['industri'] as List);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> verifyIndustri(int id) async {
    try {
      final r = await sl<DioClient>().post('$_base/industri/$id/verify', options: await _auth);
      return Right(r.data['message']);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> rejectIndustri(int id, {String? reason}) async {
    try {
      final r = await sl<DioClient>().post(
        '$_base/industri/$id/reject',
        options: await _auth,
        data: {'reason': reason},
      );
      return Right(r.data['message']);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> assignLecturer(int studentId, int lecturerId) async {
    try {
      final r = await sl<DioClient>().put(
        '$_base/mahasiswa/$studentId/assign-lecturer',
        options: await _auth,
        data: {'lecturer_id': lecturerId},
      );
      return Right(r.data['message']);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }
}
