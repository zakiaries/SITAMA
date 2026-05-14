import 'package:dio/dio.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/features/student/domain/entities/seminar_entity.dart';
import 'package:sitama/service_locator.dart';

class SeminarCubit extends Cubit<SeminarState> {
  SeminarCubit() : super(SeminarLoading());

  Future<void> displaySeminars() async {
    emit(SeminarLoading());
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      final response = await sl<DioClient>().get(
        '${ApiUrls.baseUrl}seminars',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      final list = (response.data['seminars'] as List).map((s) => SeminarEntity(
            id:          s['id'],
            title:       s['title'],
            program:     s['program'],
            date:        DateTime.parse(s['date'] ?? DateTime.now().toIso8601String()),
            time:        s['time'] ?? '-',
            location:    s['location'] ?? '-',
            organizer:   s['organizer'] ?? '-',
            description: s['description'] ?? '',
            qrCode:      s['qr_code'] ?? '',
            status:      s['status'] ?? 'scheduled',
          )).toList();

      emit(SeminarLoaded(list));
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
