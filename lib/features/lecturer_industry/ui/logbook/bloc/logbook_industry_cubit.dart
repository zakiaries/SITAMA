import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';
import 'package:sitama/features/lecturer_industry/data/static_industry_data.dart';

// ──── STATES ────
abstract class LogbookIndustryState {}

class Loading extends LogbookIndustryState {}

class Loaded extends LogbookIndustryState {
  final List<LogbookEntryEntity> logbooks;
  final String filter; // 'Semua', 'Belum Dikomen', 'Sudah Dikomen'

  Loaded({required this.logbooks, required this.filter});
}

class CommentAdded extends LogbookIndustryState {
  final String message;
  CommentAdded({required this.message});
}

class Failure extends LogbookIndustryState {
  final String errorMessage;
  Failure({required this.errorMessage});
}

// ──── CUBIT ────
class LogbookIndustryCubit extends Cubit<LogbookIndustryState> {
  LogbookIndustryCubit() : super(Loading());

  void fetchLogbooks(int studentId) async {
    try {
      await Future.delayed(const Duration(milliseconds: 500));
      final data = getStaticIndustryLogbooksData(studentId);
      emit(Loaded(logbooks: data, filter: 'Semua'));
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }

  void filterLogbooks(int studentId, String filterType) async {
    try {
      await Future.delayed(const Duration(milliseconds: 300));
      final allData = getStaticIndustryLogbooksData(studentId);

      List<LogbookEntryEntity> filtered = allData;
      if (filterType == 'Belum Dikomen') {
        filtered = allData.where((l) => !l.has_comment).toList();
      } else if (filterType == 'Sudah Dikomen') {
        filtered = allData.where((l) => l.has_comment).toList();
      }

      emit(Loaded(logbooks: filtered, filter: filterType));
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }

  void addComment(int studentId, int logbookId, String comment) async {
    try {
      await Future.delayed(const Duration(milliseconds: 600));
      emit(CommentAdded(message: 'Komentar berhasil ditambahkan'));
    } catch (e) {
      emit(Failure(errorMessage: e.toString()));
    }
  }
}
