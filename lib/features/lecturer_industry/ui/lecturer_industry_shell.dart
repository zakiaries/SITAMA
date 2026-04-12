import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/ui/home/bloc/lecturer_industry_display_cubit.dart';
import 'package:sitama/features/lecturer_industry/ui/detail_student/bloc/lecturer_industry_detail_cubit.dart';
import 'package:sitama/features/lecturer_industry/ui/profile/bloc/lecturer_industry_profile_cubit.dart';
import 'package:sitama/features/lecturer_industry/ui/home/pages/lecturer_industry_home.dart';
import 'package:sitama/features/lecturer_industry/ui/detail_student/pages/lecturer_industry_detail_student.dart';
import 'package:sitama/features/lecturer_industry/ui/profile/pages/lecturer_industry_profile.dart';

class LecturerIndustryShell extends StatefulWidget {
  const LecturerIndustryShell({super.key});

  @override
  State<LecturerIndustryShell> createState() => _LecturerIndustryShellState();
}

class _LecturerIndustryShellState extends State<LecturerIndustryShell> {
  int _currentIndex = 0;
  int? _selectedStudentId;

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async {
        if (_currentIndex != 0) {
          setState(() {
            _currentIndex = 0;
            _selectedStudentId = null;
          });
          return false;
        }
        return true;
      },
      child: Scaffold(
        backgroundColor: const Color(0xFFF2F5FB),
        body: _buildBody(),
        bottomNavigationBar: _selectedStudentId == null
            ? _buildBottomNav()
            : null, // Hide nav when viewing detail
      ),
    );
  }

  Widget _buildBody() {
    // If student is selected, show student-specific pages
    if (_selectedStudentId != null) {
      return _buildStudentDetailPages();
    }

    // Otherwise show main navigation pages
    switch (_currentIndex) {
      case 0:
        return MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => LecturerIndustryDisplayCubit(),
            ),
          ],
          child: LecturerIndustryHomePage(
            onStudentSelected: (studentId, studentName) {
              setState(() {
                _selectedStudentId = studentId;
              });
            },
          ),
        );
      case 1:
        return MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => LecturerIndustryProfileCubit(),
            ),
          ],
          child: const LecturerIndustryProfilePage(),
        );
      default:
        return const Center(child: Text('Home'));
    }
  }

  Widget _buildStudentDetailPages() {
    return _buildDetailPages(_currentIndex);
  }

  Widget _buildDetailPages(int index) {
    switch (index) {
      case 0:
      case 1:
        return MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => LecturerIndustryDetailCubit(),
            ),
          ],
          child: LecturerIndustryDetailStudentPage(
            studentId: _selectedStudentId!,
            onBack: () => setState(() {
              _selectedStudentId = null;
            }),
          ),
        );

      default:
        return MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => LecturerIndustryDetailCubit(),
            ),
          ],
          child: LecturerIndustryDetailStudentPage(
            studentId: _selectedStudentId!,
            onBack: () => setState(() {
              _selectedStudentId = null;
            }),
          ),
        );
    }
  }

  Widget _buildBottomNav() {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(
          top: BorderSide(color: Color(0xFFD0DDF5), width: 0.5),
        ),
      ),
      child: BottomNavigationBar(
        backgroundColor: Colors.white,
        elevation: 0,
        currentIndex: _currentIndex,
        type: BottomNavigationBarType.fixed,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        items: [
          BottomNavigationBarItem(
            icon: Icon(
              Icons.home,
              color: _currentIndex == 0
                  ? const Color(0xFF0D2B6E)
                  : const Color(0xFF8A9BBF),
              size: 20,
            ),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(
              Icons.person,
              color: _currentIndex == 1
                  ? const Color(0xFF0D2B6E)
                  : const Color(0xFF8A9BBF),
              size: 20,
            ),
            label: 'Profil',
          ),
        ],
        selectedItemColor: const Color(0xFF0D2B6E),
        unselectedItemColor: const Color(0xFF8A9BBF),
        selectedLabelStyle: const TextStyle(
          fontSize: 9,
          fontWeight: FontWeight.w600,
        ),
        unselectedLabelStyle: const TextStyle(
          fontSize: 9,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}
