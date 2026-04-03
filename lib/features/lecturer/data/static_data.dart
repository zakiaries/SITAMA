// ignore_for_file: non_constant_identifier_names

import 'package:sitama/features/lecturer/domain/entities/lecturer_home_entity.dart';
import 'package:sitama/features/lecturer/domain/entities/lecturer_profile_entity.dart';
import 'package:sitama/features/lecturer/domain/entities/assessment_entity.dart';
import 'package:sitama/features/lecturer/domain/entities/score_entity.dart';
import 'package:sitama/features/lecturer/domain/entities/lecturer_detail_student.dart';
import 'package:sitama/features/student/domain/entities/guidance_entity.dart';
import 'package:sitama/features/student/domain/entities/log_book_entity.dart';

/// Static data for lecturer home screen
LecturerHomeEntity getStaticLecturerHomeData() {
  return LecturerHomeEntity(
    name: 'Dr. Muhammad Rizki',
    id: 1,
    students: {
      LecturerStudentsEntity(
        id: 1,
        user_id: 101,
        name: 'Andi Wijaya',
        username: 'andi.wijaya',
        photo_profile: 'https://via.placeholder.com/150?text=Andi',
        the_class: 'TI-2021-A',
        study_program: 'Teknik Informatika',
        major: 'Sistem Informasi',
        academic_year: '2021/2022',
        isFinished: false,
        activities: {
          'logbook': true,
          'guidance': true,
          'assessment': false,
        },
        hasNewLogbook: true,
        lastUpdated: DateTime.now().subtract(Duration(days: 2)),
      ),
      LecturerStudentsEntity(
        id: 2,
        user_id: 102,
        name: 'Budi Santoso',
        username: 'budi.santoso',
        photo_profile: 'https://via.placeholder.com/150?text=Budi',
        the_class: 'TI-2021-A',
        study_program: 'Teknik Informatika',
        major: 'Sistem Informasi',
        academic_year: '2021/2022',
        isFinished: false,
        activities: {
          'logbook': true,
          'guidance': false,
          'assessment': true,
        },
        hasNewLogbook: false,
        lastUpdated: DateTime.now().subtract(Duration(days: 5)),
      ),
      LecturerStudentsEntity(
        id: 3,
        user_id: 103,
        name: 'Citra Dewi',
        username: 'citra.dewi',
        photo_profile: 'https://via.placeholder.com/150?text=Citra',
        the_class: 'TI-2021-B',
        study_program: 'Teknik Informatika',
        major: 'Sistem Informasi',
        academic_year: '2021/2022',
        isFinished: true,
        activities: {
          'logbook': true,
          'guidance': true,
          'assessment': true,
        },
        hasNewLogbook: false,
        lastUpdated: DateTime.now().subtract(Duration(days: 10)),
      ),
      LecturerStudentsEntity(
        id: 4,
        user_id: 104,
        name: 'Doni Surya',
        username: 'doni.surya',
        photo_profile: 'https://via.placeholder.com/150?text=Doni',
        the_class: 'TI-2021-B',
        study_program: 'Teknik Informatika',
        major: 'Sistem Informasi',
        academic_year: '2021/2022',
        isFinished: false,
        activities: {
          'logbook': false,
          'guidance': true,
          'assessment': true,
        },
        hasNewLogbook: true,
        lastUpdated: DateTime.now(),
      ),
      LecturerStudentsEntity(
        id: 5,
        user_id: 105,
        name: 'Eka Putri',
        username: 'eka.putri',
        photo_profile: 'https://via.placeholder.com/150?text=Eka',
        the_class: 'TI-2021-B',
        study_program: 'Teknik Informatika',
        major: 'Sistem Informasi',
        academic_year: '2021/2022',
        isFinished: false,
        activities: {
          'logbook': true,
          'guidance': true,
          'assessment': false,
        },
        hasNewLogbook: false,
        lastUpdated: DateTime.now().subtract(Duration(days: 3)),
      ),
    },
    activities: {
      'logbook_review': true,
      'guidance_session': true,
      'score_entry': false,
    },
  );
}

/// Static data for lecturer profile
LecturerProfileEntity getStaticLecturerProfileData() {
  return LecturerProfileEntity(
    name: 'Dr. Muhammad Rizki',
    username: 'dr.rizki',
    photo_profile: 'https://via.placeholder.com/300?text=Dr.Rizki',
  );
}

/// Static data for student assessments
List<AssessmentEntity> getStaticAssessmentData(int studentId) {
  // Sample assessments for demo purposes
  return [
    AssessmentEntity(
      componentName: 'Tahap Persiapan',
      scores: [
        ScoreEntity(
          id: 1,
          name: 'Analisis Kebutuhan',
          score: 85,
        ),
        ScoreEntity(
          id: 2,
          name: 'Rencana Proyek',
          score: 80,
        ),
        ScoreEntity(
          id: 3,
          name: 'Desain Sistem',
          score: 90,
        ),
      ],
    ),
    AssessmentEntity(
      componentName: 'Tahap Implementasi',
      scores: [
        ScoreEntity(
          id: 4,
          name: 'Kualitas Kode',
          score: 88,
        ),
        ScoreEntity(
          id: 5,
          name: 'Testing',
          score: 82,
        ),
        ScoreEntity(
          id: 6,
          name: 'Dokumentasi',
          score: 78,
        ),
      ],
    ),
    AssessmentEntity(
      componentName: 'Tahap Finalalisasi',
      scores: [
        ScoreEntity(
          id: 7,
          name: 'Presentasi',
          score: 92,
        ),
        ScoreEntity(
          id: 8,
          name: 'Penanganan Pertanyaan',
          score: 85,
        ),
      ],
    ),
  ];
}

/// Static data for student detail page
DetailStudentEntity getStaticDetailStudentData(int studentId) {
  return DetailStudentEntity(
    student: InfoStudentEntity(
      name: 'Andi Wijaya',
      username: 'andi.wijaya',
      email: 'andi.wijaya@student.ac.id',
      photo_profile: 'https://via.placeholder.com/150?text=Andi',
      isFinished: false,
    ),
    username: 'andi.wijaya',
    the_class: 'TI-2021-A',
    major: 'Sistem Informasi',
    internships: [
      InternshipStudentEntity(
        name: 'PT. Telekomunikasi Indonesia',
        start_date: DateTime(2024, 1, 15),
        end_date: DateTime(2024, 6, 15),
        status: 'Magang',
        isApproved: true,
      ),
    ],
    guidances: [
      GuidanceEntity(
        id: 1,
        title: 'Bimbingan Topik Proposal',
        activity: 'Diskusi mengenai topik magang dan proposal project',
        date: DateTime(2024, 2, 1),
        lecturer_note: 'Topik sudah sesuai dengan kebutuhan industri',
        name_file: 'proposal_v1.pdf',
        status: 'Disetujui',
      ),
      GuidanceEntity(
        id: 2,
        title: 'Bimbingan Progress Report 1',
        activity: 'Review progress minggu ke-1 dan ke-2',
        date: DateTime(2024, 2, 15),
        lecturer_note: 'Progress berjalan baik, lanjutkan dengan tahap berikutnya',
        name_file: 'progress_1.pdf',
        status: 'Disetujui',
      ),
    ],
    log_book: [
      LogBookEntity(
        id: 1,
        title: 'Hari ke-1: Orientasi',
        activity: 'Orientasi perusahaan dan perkenalan dengan team',
        date: DateTime(2024, 1, 15),
        lecturer_note: 'Aktivitas orientasi berjalan lancar',
      ),
      LogBookEntity(
        id: 2,
        title: 'Hari ke-2: Setup Development Environment',
        activity: 'Setup tools dan environment development',
        date: DateTime(2024, 1, 16),
        lecturer_note: 'Semua tools berhasil diinstall dan dikonfigurasi',
      ),
      LogBookEntity(
        id: 3,
        title: 'Hari ke-3: Dokumentasi Proyek',
        activity: 'Membaca dan mempelajari dokumentasi proyek',
        date: DateTime(2024, 1, 17),
        lecturer_note: 'Harap lanjutkan membaca API documentation',
      ),
    ],
    assessments: [
      ShowAssessmentEntity(
        component_name: 'Tahap Persiapan',
        average_score: 85.0,
      ),
      ShowAssessmentEntity(
        component_name: 'Tahap Implementasi',
        average_score: 82.5,
      ),
      ShowAssessmentEntity(
        component_name: 'Tahap Finalisasi',
        average_score: 88.0,
      ),
    ],
    average_all_assessments: '85.2',
  );
}
