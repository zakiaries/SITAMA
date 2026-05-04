// ignore_for_file: non_constant_identifier_names

import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_home_entity.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_profile_entity.dart';

// ──── HOME DATA ────
LecturerIndustryHomeEntity getStaticIndustryLecturerHomeData() {
  return LecturerIndustryHomeEntity(
    lecturer_name: "Budi Santoso, S.T.",
    company_name: "PT. Telkom Indonesia",
    position: "Pembimbing Industri",
    division: "Divisi IT",
    total_students: 5,
    active_students: 3,
    new_logbooks: 8,
    students: [
      IndustryStudentEntity(
        id: 1,
        name: "Muhammad Zaki Aries Putra",
        nim: "3.34.23.2.15",
        class_name: "IK-3C",
        position: "Frontend Developer Intern",
        status: "Aktif",
        progress_percentage: 75,
        start_date: DateTime(2024, 11, 1),
        end_date: DateTime(2025, 6, 30),
        total_logbooks: 12,
        attendance_percentage: 96,
      ),
      IndustryStudentEntity(
        id: 2,
        name: "Alif Rahman Maulana",
        nim: "3.34.23.2.01",
        class_name: "IK-3C",
        position: "UI/UX Design Intern",
        status: "Aktif",
        progress_percentage: 60,
        start_date: DateTime(2024, 11, 15),
        end_date: DateTime(2025, 6, 30),
        total_logbooks: 10,
        attendance_percentage: 94,
      ),
      IndustryStudentEntity(
        id: 3,
        name: "Eka Pramudita",
        nim: "3.34.23.2.07",
        class_name: "IK-3C",
        position: "Mobile App Intern",
        status: "Selesai",
        progress_percentage: 100,
        start_date: DateTime(2024, 11, 1),
        end_date: DateTime(2024, 12, 20),
        total_logbooks: 15,
        attendance_percentage: 98,
      ),
      IndustryStudentEntity(
        id: 4,
        name: "Vincencius Kurnia Putra",
        nim: "3.34.23.2.24",
        class_name: "IK-3C",
        position: "Backend Developer Intern",
        status: "Baru",
        progress_percentage: 20,
        start_date: DateTime(2024, 12, 20),
        end_date: DateTime(2025, 6, 30),
        total_logbooks: 3,
        attendance_percentage: 85,
      ),
      IndustryStudentEntity(
        id: 5,
        name: "Siti Nurhaliza",
        nim: "3.34.23.2.18",
        class_name: "IK-3C",
        position: "QA Engineer Intern",
        status: "Aktif",
        progress_percentage: 55,
        start_date: DateTime(2024, 11, 8),
        end_date: DateTime(2025, 6, 30),
        total_logbooks: 9,
        attendance_percentage: 92,
      ),
    ],
  );
}

// ──── DETAIL STUDENT DATA ────
LecturerIndustryDetailStudentEntity getStaticIndustryDetailStudentData(
    int studentId) {
  // Get base home data to find student
  final homeData = getStaticIndustryLecturerHomeData();
  final student = homeData.students.firstWhere(
    (s) => s.id == studentId,
    orElse: () => homeData.students[0],
  );

  return LecturerIndustryDetailStudentEntity(
    student_id: student.id,
    student_name: student.name,
    nim: student.nim,
    position: student.position,
    status: student.status,
    start_date: student.start_date,
    end_date: student.end_date,
    total_logbooks: student.total_logbooks,
    attendance_percentage: student.attendance_percentage,
    performance_notes:
        "Mahasiswa menunjukkan perkembangan yang baik. Kemampuan di bidangnya solid, perlu ditingkatkan di sisi komunikasi dan dokumentasi kode.",
    performance_notes_by: "Budi Santoso, S.T.",
    performance_notes_date: DateTime(2024, 12, 10),
    recent_logbooks: [
      LogbookEntryEntity(
        id: 28,
        day_number: 28,
        title: "Hari ke-28: Finalisasi Laporan",
        description:
            "Menyusun draft akhir laporan dan melakukan review bersama tim developer senior sebelum presentasi internal.",
        category: "Laporan",
        date: DateTime(2024, 12, 28),
        comment_by_lecturer: null,
        has_comment: false,
        comment_status: "Belum dikomen",
      ),
      LogbookEntryEntity(
        id: 14,
        day_number: 14,
        title: "Hari ke-14: Code Review",
        description:
            "Melakukan code review bersama tim untuk memastikan kualitas kode sesuai standar perusahaan.",
        category: "Pengembangan",
        date: DateTime(2024, 12, 14),
        comment_by_lecturer:
            "Bagus! Kemampuan debugging berkembang. Dokumentasikan temuan bug untuk referensi tim.",
        has_comment: true,
        comment_status: "Sudah dikomen",
      ),
    ],
    assessment_scores: IndustryScoreEntity(
      average_score: 84,
      score_quality: "Sangat Baik",
      categories: [
        ScoreCategoryEntity(
          category_name: "Kinerja & Sikap Kerja",
          items: [
            ScoreItemEntity(item_name: "Kedisiplinan", score: 88),
            ScoreItemEntity(item_name: "Tanggung Jawab", score: 85),
            ScoreItemEntity(item_name: "Inisiatif", score: 80),
            ScoreItemEntity(item_name: "Kerjasama Tim", score: 90),
          ],
        ),
        ScoreCategoryEntity(
          category_name: "Kompetensi Teknis",
          items: [
            ScoreItemEntity(item_name: "Kualitas Pekerjaan", score: 87),
            ScoreItemEntity(item_name: "Penguasaan Tools", score: 83),
            ScoreItemEntity(item_name: "Problem Solving", score: 78),
          ],
        ),
      ],
    ),
  );
}

// ──── ALL LOGBOOKS ────
List<LogbookEntryEntity> getStaticIndustryLogbooksData(int studentId) {
  return [
    LogbookEntryEntity(
      id: 28,
      day_number: 28,
      title: "Hari ke-28: Finalisasi Laporan dan Presentasi",
      description:
          "Menyusun draft akhir laporan proyek berdasarkan hasil analisis data. Melakukan revisi berdasarkan saran supervisor.",
      category: "Laporan",
      date: DateTime(2024, 12, 28),
      comment_by_lecturer: null,
      has_comment: false,
      comment_status: "Belum dikomen",
    ),
    LogbookEntryEntity(
      id: 14,
      day_number: 14,
      title: "Hari ke-14: Code Review & Testing",
      description:
          "Melakukan code review modul notifikasi bersama tim senior dan memperbaiki 3 bug yang ditemukan.",
      category: "Pengembangan",
      date: DateTime(2024, 12, 14),
      comment_by_lecturer:
          "Bagus! Kemampuan debugging berkembang. Dokumentasikan temuan bug untuk referensi tim.",
      has_comment: true,
      comment_status: "Sudah dikomen",
    ),
    LogbookEntryEntity(
      id: 7,
      day_number: 7,
      title: "Hari ke-7: Rapat Evaluasi Mingguan",
      description:
          "Mempresentasikan progres pekan pertama kepada tim dan menerima arahan untuk minggu berikutnya.",
      category: "Rapat",
      date: DateTime(2024, 11, 7),
      comment_by_lecturer:
          "Presentasi bagus untuk evaluasi pertama. Tingkatkan rasa percaya diri saat menyampaikan hasil kerja.",
      has_comment: true,
      comment_status: "Sudah dikomen",
    ),
    LogbookEntryEntity(
      id: 1,
      day_number: 1,
      title: "Hari ke-1: Orientasi dan Onboarding",
      description:
          "Mengikuti sesi orientasi, perkenalan tim, dan mendapatkan akses ke sistem internal perusahaan.",
      category: "Orientasi",
      date: DateTime(2024, 11, 1),
      comment_by_lecturer: null,
      has_comment: false,
      comment_status: "Belum dikomen",
    ),
  ];
}

// ──── PROFILE DATA ────
LecturerIndustryProfileEntity getStaticIndustryProfileData() {
  return LecturerIndustryProfileEntity(
    name: "Budi Santoso, S.T.",
    company_name: "PT. Telkom Indonesia",
    position: "Pembimbing Industri",
    division: "Divisi IT Infrastructure",
    photo_profile: null,
    total_students: 5,
    active_students: 3,
    evaluated_students: 1,
    average_score: 84.2,
  );
}
