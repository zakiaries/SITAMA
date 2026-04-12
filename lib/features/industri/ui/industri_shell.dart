import 'package:flutter/material.dart';
import 'lowongan/pages/industri_lowongan.dart';
import 'edit_profil_perusahaan.dart';
import 'kelola_pembimbing_industri.dart';

class IndustriShell extends StatefulWidget {
  const IndustriShell({super.key});

  @override
  State<IndustriShell> createState() => _IndustriShellState();
}

class _IndustriShellState extends State<IndustriShell> {
  int _selectedIndex = 0;

  late List<Widget> _pages;

  @override
  void initState() {
    super.initState();
    _pages = [
      const IndustriHome(),
      const IndustriLowongan(),
      const IndustriPelamar(),
      const IndustriProfile(),
    ];
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _pages[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        onTap: (index) {
          setState(() => _selectedIndex = index);
        },
        type: BottomNavigationBarType.fixed,
        backgroundColor: Colors.white,
        selectedItemColor: const Color(0xFF388E3C),
        unselectedItemColor: const Color(0xFFB0BDD4),
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.work),
            label: 'Lowongan',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people),
            label: 'Pelamar',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
      ),
    );
  }
}

class IndustriHome extends StatelessWidget {
  const IndustriHome({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      body: NestedScrollView(
        headerSliverBuilder: (BuildContext context, bool innerBoxIsScrolled) {
          return [
            SliverToBoxAdapter(
              child: ClipRRect(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 4),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Mitra Industri',
                                style: TextStyle(
                                  color: Color(0xFFB0BDD4),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'PT. Telkom Indonesia',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 19,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 2),
                              const Text(
                                'Divisi IT Infrastructure · Semarang',
                                style: TextStyle(
                                  color: Color(0xFFB0BDD4),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                          Container(
                            width: 50,
                            height: 50,
                            decoration: BoxDecoration(
                              color: const Color(0xFF3D5AF1),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Center(
                              child: Text(
                                'TI',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 11,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE6F4EA),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: const Color(0xFF388E3C),
                            width: 0.5,
                          ),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              '●',
                              style: TextStyle(
                                fontSize: 10,
                                color: Color(0xFF388E3C),
                              ),
                            ),
                            SizedBox(width: 6),
                            Text(
                              'Akun terverifikasi oleh Kaprodi',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF388E3C),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ];
        },
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // STATS GRID
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.4,
                children: [
                  _buildStatCard(
                    '3',
                    'Lowongan Aktif',
                    '12 pelamar ›',
                    const Color(0xFF1A3A8E),
                  ),
                  _buildStatCard(
                    '5',
                    'Menunggu Review',
                    'Pelamar baru ›',
                    const Color(0xFFE6A400),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              // AKSI CEPAT TITLE
              const Text(
                'Aksi Cepat',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1A2050),
                ),
              ),
              const SizedBox(height: 12),
              // AKSI CEPAT GRID
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.3,
                children: [
                  _buildActionCard(
                    'Buat Lowongan',
                    'Posting baru',
                    Icons.add_circle_outline,
                    const Color(0xFF388E3C),
                  ),
                  _buildActionCard(
                    'Review Pelamar',
                    '5 menunggu',
                    Icons.rate_review_outlined,
                    const Color(0xFFE6A400),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              // LOWONGAN AKTIF SECTION
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Lowongan Aktif',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1A2050),
                    ),
                  ),
                  TextButton(
                    onPressed: () {},
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                    ),
                    child: const Text(
                      'Lihat semua',
                      style: TextStyle(
                        fontSize: 12,
                        color: Color(0xFF388E3C),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              // LOWONGAN CARDS
              _buildLowonganCard(
                'FE',
                const Color(0xFF388E3C),
                'Frontend Developer Intern',
                'PT. Telkom Indonesia · Semarang',
                ['Flutter', 'Dart', 'UI/UX'],
                '📍 Semarang',
                '8 pelamar',
              ),
              const SizedBox(height: 12),
              _buildLowonganCard(
                'BE',
                const Color(0xFF1A3A8E),
                'Backend Engineer Intern',
                'PT. Telkom Indonesia · Semarang',
                ['Laravel', 'PHP', 'MySQL'],
                '📍 Semarang',
                '4 pelamar',
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(
    String value,
    String label,
    String subtitle,
    Color color,
  ) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Text(
            value,
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w800,
              color: color,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: const TextStyle(
              fontSize: 11,
              color: Color(0xFF1A2050),
              fontWeight: FontWeight.w600,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            subtitle,
            style: TextStyle(
              fontSize: 10,
              color: color,
              fontWeight: FontWeight.w600,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildActionCard(
    String title,
    String subtitle,
    IconData icon,
    Color color,
  ) {
    return Container(
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: color.withValues(alpha: 0.3),
          width: 0.5,
        ),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color, size: 28),
          const SizedBox(height: 8),
          Text(
            title,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: color,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 3),
          Text(
            subtitle,
            style: TextStyle(
              fontSize: 10,
              color: color.withValues(alpha: 0.7),
              fontWeight: FontWeight.w500,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildLowonganCard(
    String initials,
    Color avatarColor,
    String title,
    String company,
    List<String> skills,
    String location,
    String applicants,
  ) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.all(13),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 45,
                height: 45,
                decoration: BoxDecoration(
                  color: avatarColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: Text(
                    initials,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      color: avatarColor,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 11),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A2050),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      company,
                      style: const TextStyle(
                        fontSize: 10,
                        color: Color(0xFF8A9BC0),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: avatarColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  'Aktif',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: avatarColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: skills.map((skill) {
              return Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 8,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: const Color(0xFFEEF0FA),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  skill,
                  style: const TextStyle(
                    fontSize: 9,
                    color: Color(0xFF8A9BC0),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                location,
                style: const TextStyle(
                  fontSize: 10,
                  color: Color(0xFF8A9BC0),
                  fontWeight: FontWeight.w500,
                ),
              ),
              Text(
                applicants,
                style: const TextStyle(
                  fontSize: 10,
                  color: Color(0xFF8A9BC0),
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class IndustriPelamar extends StatefulWidget {
  const IndustriPelamar({super.key});

  @override
  State<IndustriPelamar> createState() => _IndustriPelamarState();
}

class _IndustriPelamarState extends State<IndustriPelamar>
    with TickerProviderStateMixin {
  late TabController _tabController;
  int _selectedTabIndex = 0;

  final List<Map<String, dynamic>> _pelamarData = [
    {
      'lowongan': 'Frontend Developer Intern',
      'pelamar': [
        {
          'initials': 'MZ',
          'name': 'Muhammad Zaki Aries Putra',
          'nim': '3.34.23.2.15',
          'class': 'IK-3C',
          'major': 'Teknik Informatika',
          'date': '5 Nov 2024',
          'status': 'Menunggu',
          'skills': ['Flutter', 'Dart', 'Firebase'],
          'avatarColor': const Color(0xFF388E3C),
        },
        {
          'initials': 'AR',
          'name': 'Alif Rahman Maulana',
          'nim': '3.34.23.2.01',
          'class': 'IK-3C',
          'major': 'Teknik Informatika',
          'date': '6 Nov 2024',
          'status': 'Menunggu',
          'skills': ['Flutter', 'React', 'Figma'],
          'avatarColor': const Color(0xFF1A3A8E),
        },
      ],
    },
    {
      'lowongan': 'Backend Engineer Intern',
      'pelamar': [
        {
          'initials': 'VC',
          'name': 'Vincencius Kurnia Putra',
          'nim': '3.34.23.2.24',
          'class': 'IK-3C',
          'major': 'Teknik Informatika',
          'date': '7 Nov 2024',
          'status': 'Menunggu',
          'skills': ['Laravel', 'PHP', 'MySQL', 'Docker'],
          'avatarColor': const Color(0xFFE53E3E),
        },
      ],
    },
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(() {
      setState(() => _selectedTabIndex = _tabController.index);
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      body: NestedScrollView(
        headerSliverBuilder: (BuildContext context, bool innerBoxIsScrolled) {
          return [
            SliverToBoxAdapter(
              child: ClipRRect(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 16),
                      const Text(
                        'Review Pelamar',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      // Search Bar
                      Container(
                        height: 40,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(12),
                          color: Colors.white,
                        ),
                        child: TextField(
                          decoration: InputDecoration(
                            hintText: 'Cari nama atau NIM...',
                            hintStyle: const TextStyle(
                              color: Color(0xFF8A9BC0),
                              fontSize: 13,
                            ),
                            prefixIcon: const Icon(
                              Icons.search,
                              size: 18,
                              color: Color(0xFF8A9BC0),
                            ),
                            contentPadding: const EdgeInsets.symmetric(
                              vertical: 0,
                              horizontal: 12,
                            ),
                            border: InputBorder.none,
                            isDense: true,
                          ),
                          style: const TextStyle(
                            color: Color(0xFF1A1A3E),
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ];
        },
        body: Column(
          children: [
            // TAB PILLS
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: [
                  _buildTabPill(0, 'Menunggu (5)'),
                  const SizedBox(width: 8),
                  _buildTabPill(1, 'Diterima (3)'),
                  const SizedBox(width: 8),
                  _buildTabPill(2, 'Ditolak (4)'),
                ],
              ),
            ),
            // BANNER
            if (_selectedTabIndex == 0)
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF8E1),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: const Color(0xFFE6A400),
                    width: 0.5,
                  ),
                ),
                child: Row(
                  children: [
                    const Text(
                      '●',
                      style: TextStyle(
                        fontSize: 12,
                        color: Color(0xFFE6A400),
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        '5 pelamar menunggu keputusan penerimaan',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFFE6A400),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            // LIST PELAMAR
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: _buildPelamarList(_selectedTabIndex),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTabPill(int index, String label) {
    bool isActive = _selectedTabIndex == index;
    return GestureDetector(
      onTap: () {
        _tabController.animateTo(index);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isActive ? const Color(0xFF1A1A3E) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: isActive
              ? null
              : Border.all(
                  color: const Color(0xFFD0D6EB),
                  width: 0.5,
                ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: isActive ? Colors.white : const Color(0xFF8A9BC0),
          ),
        ),
      ),
    );
  }

  List<Widget> _buildPelamarList(int tabIndex) {
    List<Widget> result = [];

    for (var group in _pelamarData) {
      result.add(
        Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: Text(
            group['lowongan'] as String,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: Color(0xFF1A2050),
            ),
          ),
        ),
      );

      for (var pelamar in group['pelamar'] as List) {
        // Filter berdasarkan status
        if (tabIndex == 0 && pelamar['status'] != 'Menunggu') continue;
        if (tabIndex == 1 && pelamar['status'] != 'Diterima') continue;
        if (tabIndex == 2 && pelamar['status'] != 'Ditolak') continue;

        result.add(
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: _buildPelamarCard(pelamar),
          ),
        );
      }

      result.add(const SizedBox(height: 8));
    }

    return result;
  }

  Widget _buildPelamarCard(Map<String, dynamic> pelamar) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.all(13),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color:
                      (pelamar['avatarColor'] as Color).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: Text(
                    pelamar['initials'] as String,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: pelamar['avatarColor'] as Color,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 11),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      pelamar['name'] as String,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A2050),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${pelamar['nim']} · ${pelamar['class']} · ${pelamar['major']}',
                      style: const TextStyle(
                        fontSize: 9,
                        color: Color(0xFF8A9BC0),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Daftar: ${pelamar['date'] as String}',
                      style: const TextStyle(
                        fontSize: 9,
                        color: Color(0xFFB0BDD4),
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF8E1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  pelamar['status'] as String,
                  style: const TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFFE6A400),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 5,
            children: (pelamar['skills'] as List<String>).map((skill) {
              return Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 8,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: const Color(0xFFEEF0FA),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  skill,
                  style: const TextStyle(
                    fontSize: 9,
                    color: Color(0xFF8A9BC0),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              ElevatedButton(
                onPressed: () {},
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFE6F4EA),
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 6,
                  ),
                ),
                child: const Text(
                  'Terima',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF388E3C),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton(
                onPressed: () {},
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFFFE6F0),
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 6,
                  ),
                ),
                child: const Text(
                  'Tolak',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFFC41E3A),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class IndustriProfile extends StatelessWidget {
  const IndustriProfile({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      body: NestedScrollView(
        headerSliverBuilder: (BuildContext context, bool innerBoxIsScrolled) {
          return [
            SliverToBoxAdapter(
              child: ClipRRect(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 4),
                      // Avatar dengan edit badge
                      Stack(
                        children: [
                          Container(
                            width: 80,
                            height: 80,
                            decoration: BoxDecoration(
                              color: const Color(0xFF3D5AF1),
                              shape: BoxShape.circle,
                            ),
                            child: const Center(
                              child: Text(
                                'TI',
                                style: TextStyle(
                                  fontSize: 26,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              width: 28,
                              height: 28,
                              decoration: BoxDecoration(
                                color: const Color(0xFFE6A400),
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: const Color(0xFF1A1A3E),
                                  width: 2,
                                ),
                              ),
                              child: const Center(
                                child: Icon(
                                  Icons.edit,
                                  size: 14,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'PT. Telkom Indonesia',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Divisi IT Infrastructure · Semarang',
                        style: TextStyle(
                          color: Color(0xFFB0BDD4),
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 2),
                      const Text(
                        'PIC: Budi Santoso, S.T.',
                        style: TextStyle(
                          color: Color(0xFFB0BDD4),
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 11,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE6F4EA),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: const Color(0xFF388E3C),
                            width: 0.5,
                          ),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              '✓',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF388E3C),
                              ),
                            ),
                            SizedBox(width: 6),
                            Text(
                              'Akun Terverifikasi',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF388E3C),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ];
        },
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // STAT GRID
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.6,
                children: [
                  _buildStatCard(
                      '3 lowongan', 'Lowongan Aktif', const Color(0xFF1A3A8E)),
                  _buildStatCard(
                      '4 aktif', 'Mahasiswa Magang', const Color(0xFF1A3A8E)),
                  _buildStatCard(
                      '6 orang', 'Total Diterima', const Color(0xFF8A9BC0)),
                  _buildStatCard(
                      '86.5', 'Rata-rata Nilai', const Color(0xFF8A9BC0)),
                ],
              ),
              const SizedBox(height: 24),
              // INFORMASI PERUSAHAAN
              const Text(
                'INFORMASI PERUSAHAAN',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFFB0BDD4),
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 12),
              _buildMenuCard(
                'Edit Profil Perusahaan',
                Icons.business,
                const Color(0xFF085041),
                const Color(0xFFE1F5EE),
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const EditProfilPerusahaan(),
                    ),
                  );
                },
              ),
              const SizedBox(height: 10),
              _buildMenuCard(
                'Kelola Pembimbing Industri',
                Icons.people_outline,
                const Color(0xFF085041),
                const Color(0xFFE1F5EE),
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const KelolaPembimbingIndustri(),
                    ),
                  );
                },
              ),
              const SizedBox(height: 24),
              // AKUN
              const Text(
                'AKUN',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFFB0BDD4),
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 12),
              _buildMenuCard(
                'Ganti Password',
                Icons.lock_outline,
                const Color(0xFF1A3A8E),
                const Color(0xFFE8F0FE),
              ),
              const SizedBox(height: 10),
              _buildMenuCard(
                'Notifikasi',
                Icons.notifications_outlined,
                const Color(0xFF1A3A8E),
                const Color(0xFFE8F0FE),
              ),
              const SizedBox(height: 10),
              _buildMenuCard(
                'Logout',
                Icons.logout,
                const Color(0xFFC41E3A),
                const Color(0xFFFFE6F0),
                isLogout: true,
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(String value, String label, Color color) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            value,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w800,
              color: color,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            label,
            style: const TextStyle(
              fontSize: 11,
              color: Color(0xFF8A9BC0),
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuCard(
    String title,
    IconData icon,
    Color iconColor,
    Color backgroundColor, {
    bool isLogout = false,
    VoidCallback? onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(13),
          border: Border.all(
            color: const Color(0xFFD0D6EB),
            width: 0.5,
          ),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 13),
        child: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: backgroundColor,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Center(
                child: Icon(
                  icon,
                  size: 18,
                  color: iconColor,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                title,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: isLogout
                      ? const Color(0xFFC41E3A)
                      : const Color(0xFF1A2050),
                ),
              ),
            ),
            Icon(
              Icons.arrow_forward_ios,
              size: 14,
              color: const Color(0xFFD0D6EB),
            ),
          ],
        ),
      ),
    );
  }
}
