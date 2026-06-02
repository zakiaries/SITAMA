import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/service_locator.dart';

const _base = '${ApiUrls.baseUrl}industri';

class IndustriApiService {
  Future<Options> get _auth async {
    final prefs = await SharedPreferences.getInstance();
    return Options(headers: {'Authorization': 'Bearer ${prefs.getString('token')}'});
  }

  Future<Either> getProfile() async {
    try {
      final r = await sl<DioClient>().get('$_base/profile', options: await _auth);
      return Right(r.data as Map<String, dynamic>);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> getLowongan({String status = 'all', String search = ''}) async {
    try {
      final r = await sl<DioClient>().get(
        '$_base/lowongan',
        queryParameters: {'status': status, 'search': search},
        options: await _auth,
      );
      return Right(r.data['lowongan'] as List);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> createLowongan(Map<String, dynamic> data) async {
    try {
      final r = await sl<DioClient>().post('$_base/lowongan', options: await _auth, data: data);
      return Right(r.data);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?.toString() ?? e.message);
    }
  }

  Future<Either> getPelamar({String status = 'all', String search = ''}) async {
    try {
      final r = await sl<DioClient>().get(
        '$_base/pelamar',
        queryParameters: {'status': status, 'search': search},
        options: await _auth,
      );
      return Right(r.data['pelamar'] as List);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> acceptPelamar(int id) async {
    try {
      final r = await sl<DioClient>().post('$_base/pelamar/$id/accept', options: await _auth);
      return Right(r.data['message']);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> rejectPelamar(int id) async {
    try {
      final r = await sl<DioClient>().post('$_base/pelamar/$id/reject', options: await _auth);
      return Right(r.data['message']);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }

  Future<Either> updateProfile(Map<String, dynamic> data) async {
    try {
      final r = await sl<DioClient>().put('$_base/profile', options: await _auth, data: data);
      return Right(r.data['message']);
    } on DioException catch (e) {
      return Left(e.response?.data?['errors']?['message'] ?? e.message);
    }
  }
}
