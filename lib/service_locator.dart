import 'package:get_it/get_it.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/features/auth/data/repositories/auth.dart';
import 'package:sitama/features/auth/data/sources/auth_api_service.dart';
import 'package:sitama/features/auth/data/sources/auth_local_service.dart';
import 'package:sitama/features/auth/domain/repositories/auth.dart';
import 'package:sitama/features/auth/domain/usecases/is_logged_in.dart';
import 'package:sitama/features/auth/domain/usecases/log_out.dart';
import 'package:sitama/features/auth/domain/usecases/signin.dart';
import 'package:sitama/features/auth/domain/usecases/signin_google.dart';
import 'package:sitama/features/lecturer/data/repositories/lecturer.dart';
import 'package:sitama/features/lecturer/data/sources/lecturer_api_service.dart';
import 'package:sitama/features/lecturer/domain/repositories/lecturer.dart';
import 'package:sitama/features/lecturer/domain/usecases/get_assessmet.dart';
import 'package:sitama/features/lecturer/domain/usecases/get_detail_student.dart';
import 'package:sitama/features/lecturer/domain/usecases/get_home_lecturer.dart';
import 'package:sitama/features/lecturer/domain/usecases/get_profile_lecturer.dart';
import 'package:sitama/features/lecturer/domain/usecases/update_status_guidance.dart';
import 'package:sitama/features/lecturer/domain/usecases/update_status_logbook.dart';
import 'package:sitama/features/lecturer/ui/home/bloc/selection/selection_bloc.dart';
import 'package:sitama/features/shared/domain/usecases/reset_password.dart';
import 'package:sitama/features/shared/domain/usecases/update_photo_profile.dart';
import 'package:sitama/features/student/data/repositories/job_listing.dart';
import 'package:sitama/features/student/data/repositories/student.dart';
import 'package:sitama/features/student/data/sources/job_listing_api_service.dart';
import 'package:sitama/features/student/data/sources/student_api_service.dart';
import 'package:sitama/features/student/domain/repositories/job_listing.dart';
import 'package:sitama/features/student/domain/repositories/student.dart';
import 'package:sitama/features/student/domain/usecases/general/get_home_student.dart';
import 'package:sitama/features/student/domain/usecases/general/get_profile_student.dart';
import 'package:sitama/features/student/domain/usecases/guidances/add_guidance_student.dart';
import 'package:sitama/features/student/domain/usecases/guidances/delete_guidance_student.dart';
import 'package:sitama/features/student/domain/usecases/guidances/edit_guidance_student.dart';
import 'package:sitama/features/student/domain/usecases/guidances/get_guidances_student.dart';
import 'package:sitama/features/student/domain/usecases/job_listing/get_ai_recommendation.dart';
import 'package:sitama/features/student/domain/usecases/job_listing/get_job_listings.dart';
import 'package:sitama/features/student/domain/usecases/job_listing/get_job_listings_by_category.dart';
import 'package:sitama/features/student/domain/usecases/job_listing/search_job_listings.dart';
import 'package:sitama/features/student/domain/usecases/logbook/add_log_book_student.dart';
import 'package:sitama/features/student/domain/usecases/logbook/delete_log_book_student.dart';
import 'package:sitama/features/student/domain/usecases/logbook/edit_log_book_student.dart';
import 'package:sitama/features/student/domain/usecases/logbook/get_log_book_student.dart';
import 'package:sitama/features/student/domain/usecases/notification/add_notification.dart';
import 'package:sitama/features/student/domain/usecases/notification/get_notification.dart';
import 'package:sitama/features/student/domain/usecases/notification/mark_all_notifications.dart';
import 'package:sitama/features/student/ui/guidance/bloc/guidance_student_cubit.dart';
import 'package:sitama/features/student/ui/home/bloc/student_display_cubit.dart';
import 'package:sitama/features/student/ui/job_listing/bloc/job_listing_cubit.dart';
import 'package:sitama/features/student/ui/logbook/bloc/log_book_student_cubit.dart';
import 'package:sitama/features/student/ui/seminar/bloc/seminar_cubit.dart';

final sl = GetIt.instance;

void setupServiceLocator(SharedPreferences prefs) {
  sl.registerSingleton<SharedPreferences>(prefs);
  sl.registerSingleton<DioClient>(DioClient());

  // Services
  sl.registerSingleton<AuthApiService>(AuthApiServiceImpl());
  sl.registerSingleton<AuthLocalService>(AuthLocalServiceImpl());
  sl.registerSingleton<StudentApiService>(StudentApiServiceImpl());
  sl.registerSingleton<LecturerApiService>(LecturerApiServiceImpl());
  sl.registerSingleton<JobListingApiService>(JobListingApiServiceImpl());

  // Repositories
  sl.registerSingleton<AuthRepostory>(AuthRepostoryImpl());
  sl.registerSingleton<StudentRepository>(StudentRepositoryImpl());
  sl.registerSingleton<LecturerRepository>(LecturerRepositoryImpl());
  sl.registerSingleton<JobListingRepository>(JobListingRepositoryImpl());

  // Use cases — Auth
  sl.registerSingleton<SigninUseCase>(SigninUseCase());
  sl.registerSingleton<SigninGoogleUseCase>(SigninGoogleUseCase());
  sl.registerSingleton<IsLoggedInUseCase>(IsLoggedInUseCase());
  sl.registerSingleton<LogoutUseCase>(LogoutUseCase());

  // Use cases — Student
  sl.registerSingleton<GetHomeStudentUseCase>(GetHomeStudentUseCase());
  sl.registerSingleton<GetProfileStudentUseCase>(GetProfileStudentUseCase());
  sl.registerSingleton<GetGuidancesStudentUseCase>(GetGuidancesStudentUseCase());
  sl.registerSingleton<AddGuidanceUseCase>(AddGuidanceUseCase());
  sl.registerSingleton<EditGuidanceUseCase>(EditGuidanceUseCase());
  sl.registerSingleton<DeleteGuidanceUseCase>(DeleteGuidanceUseCase());
  sl.registerSingleton<GetLogBookStudentUseCase>(GetLogBookStudentUseCase());
  sl.registerSingleton<AddLogBookUseCase>(AddLogBookUseCase());
  sl.registerSingleton<EditLogBookUseCase>(EditLogBookUseCase());
  sl.registerSingleton<DeleteLogBookUseCase>(DeleteLogBookUseCase());
  sl.registerSingleton<GetNotificationsUseCase>(GetNotificationsUseCase());
  sl.registerSingleton<MarkAllNotificationsReadUseCase>(MarkAllNotificationsReadUseCase());
  sl.registerSingleton<AddNotificationsUseCase>(AddNotificationsUseCase());

  // Use cases — Lecturer
  sl.registerSingleton<GetHomeLecturerUseCase>(GetHomeLecturerUseCase());
  sl.registerSingleton<GetProfileLecturerUseCase>(GetProfileLecturerUseCase());
  sl.registerSingleton<GetDetailStudentUseCase>(GetDetailStudentUseCase());
  sl.registerSingleton<UpdateLogBookNoteUseCase>(UpdateLogBookNoteUseCase());
  sl.registerSingleton<UpdateStatusGuidanceUseCase>(UpdateStatusGuidanceUseCase());
  sl.registerSingleton<GetAssessments>(GetAssessments());

  // Use cases — Job Listing
  sl.registerSingleton<GetJobListingsUseCase>(GetJobListingsUseCase());
  sl.registerSingleton<SearchJobListingsUseCase>(SearchJobListingsUseCase());
  sl.registerSingleton<GetJobListingsByCategoryUseCase>(GetJobListingsByCategoryUseCase());
  sl.registerSingleton<GetAiRecommendationUseCase>(GetAiRecommendationUseCase());

  // Use cases — Shared
  sl.registerSingleton<UpdatePhotoProfileUseCase>(UpdatePhotoProfileUseCase());
  sl.registerSingleton<ResetPasswordUseCase>(ResetPasswordUseCase());

  // BLoCs / Cubits
  sl.registerSingleton<SelectionBloc>(SelectionBloc());

  sl.registerLazySingleton<StudentDisplayCubit>(() => StudentDisplayCubit(
        getHomeStudentUseCase: sl<GetHomeStudentUseCase>(),
        getNotificationsUseCase: sl<GetNotificationsUseCase>(),
      ));

  sl.registerLazySingleton<GuidanceStudentCubit>(() => GuidanceStudentCubit(
        getGuidancesUseCase: sl<GetGuidancesStudentUseCase>(),
      ));

  sl.registerLazySingleton<LogBookStudentCubit>(() => LogBookStudentCubit(
        getLogBookUseCase: sl<GetLogBookStudentUseCase>(),
      ));

  sl.registerLazySingleton<JobListingCubit>(() => JobListingCubit());
  sl.registerLazySingleton<SeminarCubit>(() => SeminarCubit());
}
