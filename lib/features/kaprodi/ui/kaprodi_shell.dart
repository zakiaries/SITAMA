import 'package:flutter/material.dart';
import 'package:sitama/features/kaprodi/ui/home/pages/kaprodi_home.dart';
import 'package:sitama/features/kaprodi/ui/mahasiswa/pages/kaprodi_data_mahasiswa.dart';
import 'package:sitama/features/kaprodi/ui/dosen/pages/kaprodi_data_dosen.dart';
import 'package:sitama/features/kaprodi/ui/seminar/pages/kaprodi_manajemen_seminar.dart';
import 'package:sitama/features/kaprodi/ui/profile/pages/kaprodi_profile.dart';

class KaprodiShell extends StatefulWidget {
  const KaprodiShell({super.key});

  @override
  State<KaprodiShell> createState() => _KaprodiShellState();
}

class _KaprodiShellState extends State<KaprodiShell> {
  int _currentIndex = 0;

  late final List<Widget> _pages = [
    KaprodiHome(),
    KaprodiDataMahasiswa(),
    KaprodiDataDosen(),
    KaprodiManajemenSeminar(),
    KaprodiProfile(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _pages[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        type: BottomNavigationBarType.fixed,
        backgroundColor: Colors.white,
        selectedItemColor: const Color(0xFF1A1A3E),
        unselectedItemColor: const Color(0xFF8A9BC0),
        items: [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people),
            label: 'Mahasiswa',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_add),
            label: 'Dosen',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.calendar_today),
            label: 'Seminar',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
    );
  }
}
