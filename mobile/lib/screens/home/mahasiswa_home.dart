import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../mahasiswa/dashboard_tab.dart';
import '../mahasiswa/logbook_screen.dart';
import '../mahasiswa/bimbingan_screen.dart';
import '../mahasiswa/menu_tab.dart';
import '../mahasiswa/chatbot_screen.dart';

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

  void _openChatbot() =>
      Navigator.push(context, MaterialPageRoute(builder: (_) => const ChatbotScreen()));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _index, children: _tabs),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
      floatingActionButton: SizedBox(
        width: 62, height: 62,
        child: FloatingActionButton(
          onPressed: _openChatbot,
          backgroundColor: AppColors.primary,
          elevation: 3,
          shape: const CircleBorder(),
          tooltip: 'Chatbot',
          child: const Icon(Icons.smart_toy_outlined, color: Colors.white, size: 27),
        ),
      ),
      bottomNavigationBar: BottomAppBar(
        color: AppColors.bg,
        elevation: 10,
        shape: const CircularNotchedRectangle(),
        notchMargin: 8,
        height: 64,
        padding: EdgeInsets.zero,
        child: Row(
          children: [
            _navItem(0, Icons.home_outlined, Icons.home, 'Home'),
            _navItem(1, Icons.menu_book_outlined, Icons.menu_book, 'Bimbingan'),
            const SizedBox(width: 62), // ruang untuk tombol Chatbot di tengah
            _navItem(2, Icons.book_outlined, Icons.book, 'Log Book'),
            _navItem(3, Icons.grid_view_outlined, Icons.grid_view_rounded, 'Menu'),
          ],
        ),
      ),
    );
  }

  Widget _navItem(int index, IconData icon, IconData selectedIcon, String label) {
    final selected = _index == index;
    final color = selected ? AppColors.primary : AppColors.textMuted;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _index = index),
        customBorder: const StadiumBorder(),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 6),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(selected ? selectedIcon : icon, color: color, size: 23),
              const SizedBox(height: 3),
              Text(label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: color)),
            ],
          ),
        ),
      ),
    );
  }
}
