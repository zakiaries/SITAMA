abstract class AuthState {}

class AppInitialState extends AuthState {}
class AuthenticatedStudent extends AuthState {}
class AuthenticatedStudentPending extends AuthState {}
class AuthenticatedStudentRejected extends AuthState {}
class AuthenticatedLecturer extends AuthState {}
class AuthenticatedLecturerIndustry extends AuthState {}
class UnAuthenticated extends AuthState {}