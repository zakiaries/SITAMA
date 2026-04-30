import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/kaprodi/data/kaprodi_api_service.dart';

// ─── States ──────────────────────────────────────────────────────────────────

abstract class KaprodiState {}

class KaprodiLoading extends KaprodiState {}

class KaprodiHomeLoaded extends KaprodiState {
  final Map<String, dynamic> profile;
  final Map<String, dynamic> dashboard;
  KaprodiHomeLoaded({required this.profile, required this.dashboard});
}

class KaprodiMahasiswaLoaded extends KaprodiState {
  final List mahasiswa;
  KaprodiMahasiswaLoaded(this.mahasiswa);
}

class KaprodiDosenLoaded extends KaprodiState {
  final List dosen;
  KaprodiDosenLoaded(this.dosen);
}

class KaprodiDosenStudentsLoaded extends KaprodiState {
  final Map<String, dynamic> data;
  KaprodiDosenStudentsLoaded(this.data);
}

class KaprodiIndustriLoaded extends KaprodiState {
  final List industri;
  final String currentStatus;
  KaprodiIndustriLoaded(this.industri, this.currentStatus);
}

class KaprodiError extends KaprodiState {
  final String message;
  KaprodiError(this.message);
}

// ─── Cubit ───────────────────────────────────────────────────────────────────

class KaprodiCubit extends Cubit<KaprodiState> {
  KaprodiCubit() : super(KaprodiLoading());

  final _api = KaprodiApiService();

  Future<void> loadHome() async {
    emit(KaprodiLoading());
    final profileResult   = await _api.getProfile();
    final dashboardResult = await _api.getDashboard();

    profileResult.fold(
      (e) => emit(KaprodiError(e.toString())),
      (profile) => dashboardResult.fold(
        (e) => emit(KaprodiError(e.toString())),
        (dashboard) => emit(KaprodiHomeLoaded(
          profile:   profile as Map<String, dynamic>,
          dashboard: dashboard as Map<String, dynamic>,
        )),
      ),
    );
  }

  Future<void> loadMahasiswa({String status = 'all', String search = ''}) async {
    emit(KaprodiLoading());
    final result = await _api.getMahasiswa(status: status, search: search);
    result.fold(
      (e) => emit(KaprodiError(e.toString())),
      (data) => emit(KaprodiMahasiswaLoaded(data as List)),
    );
  }

  Future<void> loadDosen({String search = ''}) async {
    emit(KaprodiLoading());
    final result = await _api.getDosen(search: search);
    result.fold(
      (e) => emit(KaprodiError(e.toString())),
      (data) => emit(KaprodiDosenLoaded(data as List)),
    );
  }

  Future<void> loadDosenStudents(int lecturerId) async {
    emit(KaprodiLoading());
    final result = await _api.getDosenStudents(lecturerId);
    result.fold(
      (e) => emit(KaprodiError(e.toString())),
      (data) => emit(KaprodiDosenStudentsLoaded(data as Map<String, dynamic>)),
    );
  }

  Future<void> loadIndustri({String status = 'pending', String search = ''}) async {
    emit(KaprodiLoading());
    final result = await _api.getIndustri(status: status, search: search);
    result.fold(
      (e) => emit(KaprodiError(e.toString())),
      (data) => emit(KaprodiIndustriLoaded(data as List, status)),
    );
  }

  Future<void> verifyIndustri(int id, String currentStatus) async {
    await _api.verifyIndustri(id);
    await loadIndustri(status: currentStatus);
  }

  Future<void> rejectIndustri(int id, String currentStatus, {String? reason}) async {
    await _api.rejectIndustri(id, reason: reason);
    await loadIndustri(status: currentStatus);
  }
}
