import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/industri/data/industri_api_service.dart';

// ─── States ──────────────────────────────────────────────────────────────────

abstract class IndustriState {}

class IndustriLoading extends IndustriState {}

class IndustriProfileLoaded extends IndustriState {
  final Map<String, dynamic> profile;
  IndustriProfileLoaded(this.profile);
}

class IndustriLowonganLoaded extends IndustriState {
  final List lowongan;
  final String currentStatus;
  IndustriLowonganLoaded(this.lowongan, this.currentStatus);
}

class IndustriPelamarLoaded extends IndustriState {
  final List pelamar;
  final String currentStatus;
  IndustriPelamarLoaded(this.pelamar, this.currentStatus);
}

class IndustriError extends IndustriState {
  final String message;
  IndustriError(this.message);
}

class IndustriActionSuccess extends IndustriState {
  final String message;
  IndustriActionSuccess(this.message);
}

// ─── Cubit ───────────────────────────────────────────────────────────────────

class IndustriCubit extends Cubit<IndustriState> {
  IndustriCubit() : super(IndustriLoading());

  final _api = IndustriApiService();

  Future<void> loadProfile() async {
    emit(IndustriLoading());
    final result = await _api.getProfile();
    result.fold(
      (e) => emit(IndustriError(e.toString())),
      (data) => emit(IndustriProfileLoaded(data as Map<String, dynamic>)),
    );
  }

  Future<void> loadLowongan({String status = 'all', String search = ''}) async {
    emit(IndustriLoading());
    final result = await _api.getLowongan(status: status, search: search);
    result.fold(
      (e) => emit(IndustriError(e.toString())),
      (data) => emit(IndustriLowonganLoaded(data as List, status)),
    );
  }

  Future<void> loadPelamar({String status = 'all', String search = ''}) async {
    emit(IndustriLoading());
    final result = await _api.getPelamar(status: status, search: search);
    result.fold(
      (e) => emit(IndustriError(e.toString())),
      (data) => emit(IndustriPelamarLoaded(data as List, status)),
    );
  }

  Future<void> acceptPelamar(int id, String currentStatus) async {
    await _api.acceptPelamar(id);
    await loadPelamar(status: currentStatus);
  }

  Future<void> rejectPelamar(int id, String currentStatus) async {
    await _api.rejectPelamar(id);
    await loadPelamar(status: currentStatus);
  }

  Future<void> createLowongan(Map<String, dynamic> data) async {
    final result = await _api.createLowongan(data);
    result.fold(
      (e) => emit(IndustriError(e.toString())),
      (_) => emit(IndustriActionSuccess('Lowongan berhasil dibuat')),
    );
  }
}
