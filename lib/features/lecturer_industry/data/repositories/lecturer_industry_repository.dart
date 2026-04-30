import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';
import 'package:sitama/features/lecturer_industry/data/sources/lecturer_industry_api_service.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_home_entity.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_profile_entity.dart';
import 'package:sitama/service_locator.dart';

abstract class LecturerIndustryRepository {
  Future<Either> getHome();
  Future<Either> getDetailStudent(int studentId);
  Future<Either> addLogbookComment(int logbookId, String comment);
  Future<Either> submitScores(int studentId, List<Map<String, dynamic>> scores,
      {String? performanceNotes, String? performanceNotesBy});
  Future<Either> getProfile();
}

class LecturerIndustryRepositoryImpl extends LecturerIndustryRepository {
  @override
  Future<Either> getHome() async {
    final result = await sl<LecturerIndustryApiService>().getHome();
    return result.fold(
      (e) => Left(e),
      (data) {
        try {
          final r = data as Response;
          final d = r.data as Map<String, dynamic>;
          final entity = LecturerIndustryHomeEntity(
            lecturer_name:   d['lecturer_name'],
            company_name:    d['company_name'],
            position:        d['position'],
            division:        d['division'],
            total_students:  d['total_students'],
            active_students: d['active_students'],
            new_logbooks:    d['new_logbooks'],
            students: (d['students'] as List)
                .map((s) => IndustryStudentEntity(
                      id:                    s['id'],
                      name:                  s['name'],
                      nim:                   s['nim'],
                      class_name:            s['class_name'],
                      position:              s['position'],
                      status:                s['status'],
                      progress_percentage:   (s['progress_percentage'] as num).toDouble(),
                      start_date:            DateTime.parse(s['start_date']),
                      end_date:              s['end_date'] != null ? DateTime.parse(s['end_date']) : null,
                      total_logbooks:        s['total_logbooks'],
                      attendance_percentage: (s['attendance_percentage'] as num).toDouble(),
                    ))
                .toList(),
          );
          return Right(entity);
        } catch (e) {
          return Left('Parsing error: $e');
        }
      },
    );
  }

  @override
  Future<Either> getDetailStudent(int studentId) async {
    final result = await sl<LecturerIndustryApiService>().getDetailStudent(studentId);
    return result.fold(
      (e) => Left(e),
      (data) {
        try {
          final r = data as Response;
          final d = r.data as Map<String, dynamic>;

          IndustryScoreEntity? scores;
          if (d['assessment_scores'] != null) {
            final s = d['assessment_scores'] as Map<String, dynamic>;
            scores = IndustryScoreEntity(
              average_score: (s['average_score'] as num).toDouble(),
              score_quality: s['score_quality'],
              categories: (s['categories'] as List).map((c) => ScoreCategoryEntity(
                    category_name: c['category_name'],
                    items: (c['items'] as List).map((i) => ScoreItemEntity(
                          item_name: i['item_name'],
                          score:     i['score'],
                          max_score: i['max_score'] ?? 100,
                        )).toList(),
                  )).toList(),
            );
          }

          final entity = LecturerIndustryDetailStudentEntity(
            student_id:             d['student_id'],
            student_name:           d['student_name'],
            nim:                    d['nim'],
            position:               d['position'],
            status:                 d['status'],
            start_date:             DateTime.parse(d['start_date']),
            end_date:               d['end_date'] != null ? DateTime.parse(d['end_date']) : null,
            total_logbooks:         d['total_logbooks'],
            attendance_percentage:  (d['attendance_percentage'] as num).toDouble(),
            performance_notes:      d['performance_notes'] ?? '',
            performance_notes_by:   d['performance_notes_by'] ?? '',
            performance_notes_date: d['performance_notes_date'] != null
                ? DateTime.parse(d['performance_notes_date'])
                : null,
            recent_logbooks: (d['recent_logbooks'] as List).map((lb) => LogbookEntryEntity(
                  id:                  lb['id'],
                  day_number:          lb['day_number'],
                  title:               lb['title'],
                  description:         lb['description'],
                  category:            lb['category'],
                  date:                DateTime.parse(lb['date']),
                  comment_by_lecturer: lb['comment_by_lecturer'],
                  has_comment:         lb['has_comment'],
                  comment_status:      lb['comment_status'],
                )).toList(),
            assessment_scores: scores,
          );
          return Right(entity);
        } catch (e) {
          return Left('Parsing error: $e');
        }
      },
    );
  }

  @override
  Future<Either> addLogbookComment(int logbookId, String comment) async {
    final result = await sl<LecturerIndustryApiService>().addLogbookComment(logbookId, comment);
    return result.fold((e) => Left(e), (d) => Right(d));
  }

  @override
  Future<Either> submitScores(int studentId, List<Map<String, dynamic>> scores,
      {String? performanceNotes, String? performanceNotesBy}) async {
    final result = await sl<LecturerIndustryApiService>().submitScores(
      studentId, scores,
      performanceNotes: performanceNotes,
      performanceNotesBy: performanceNotesBy,
    );
    return result.fold((e) => Left(e), (d) => Right(d));
  }

  @override
  Future<Either> getProfile() async {
    final result = await sl<LecturerIndustryApiService>().getProfile();
    return result.fold(
      (e) => Left(e),
      (data) {
        try {
          final r = data as Response;
          final d = r.data as Map<String, dynamic>;
          final entity = LecturerIndustryProfileEntity(
            name:               d['name'],
            company_name:       d['company_name'],
            position:           d['position'],
            division:           d['division'],
            photo_profile:      d['photo_profile'],
            total_students:     d['total_students'],
            active_students:    d['active_students'],
            evaluated_students: d['evaluated_students'],
            average_score:      (d['average_score'] as num).toDouble(),
          );
          return Right(entity);
        } catch (e) {
          return Left('Parsing error: $e');
        }
      },
    );
  }
}
