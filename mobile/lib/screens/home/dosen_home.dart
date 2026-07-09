import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../dosen/dosen_list_tab.dart';
import '../shared/lecturer_profile_screen.dart';

class DosenHome extends StatefulWidget {
  const DosenHome({super.key});
  @override
  State<DosenHome> createState() => _DosenHomeState();
}

class _DosenHomeState extends State<DosenHome> {
  int _index = 0;

  final _tabs = const [
    DosenListTab(),
    LecturerProfileScreen(basePath: '/dosen', roleLabel: 'Dosen Pembimbing'),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _index, children: _tabs),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) => setState(() => _index = i),
        indicatorColor: AppColors.blueTint,
        destinations: const [
          NavigationDestination(icon: Icon(Icons.people_outline), selectedIcon: Icon(Icons.people), label: 'Mahasiswa'),
          NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }
}
