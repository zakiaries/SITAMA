import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../industri/industri_list_tab.dart';
import '../shared/lecturer_profile_screen.dart';

class IndustriHome extends StatefulWidget {
  const IndustriHome({super.key});
  @override
  State<IndustriHome> createState() => _IndustriHomeState();
}

class _IndustriHomeState extends State<IndustriHome> {
  int _index = 0;

  final _tabs = const [
    IndustriListTab(),
    LecturerProfileScreen(basePath: '/dosen-industri', roleLabel: 'Pembimbing Industri'),
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
