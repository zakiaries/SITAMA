import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../mahasiswa/dashboard_tab.dart';
import '../mahasiswa/logbook_screen.dart';
import '../mahasiswa/bimbingan_screen.dart';
import '../mahasiswa/menu_tab.dart';

class MahasiswaHome extends StatefulWidget {
  const MahasiswaHome({super.key});
  @override
  State<MahasiswaHome> createState() => _MahasiswaHomeState();
}

class _MahasiswaHomeState extends State<MahasiswaHome> {
  int _index = 0;

  final _tabs = const [
    DashboardTab(),
    BimbinganScreen(),
    LogbookScreen(),
    MenuTab(),
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
          NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Home'),
          NavigationDestination(icon: Icon(Icons.menu_book_outlined), selectedIcon: Icon(Icons.menu_book), label: 'Bimbingan'),
          NavigationDestination(icon: Icon(Icons.book_outlined), selectedIcon: Icon(Icons.book), label: 'Log Book'),
          NavigationDestination(icon: Icon(Icons.grid_view_outlined), selectedIcon: Icon(Icons.grid_view_rounded), label: 'Menu'),
        ],
      ),
    );
  }
}
