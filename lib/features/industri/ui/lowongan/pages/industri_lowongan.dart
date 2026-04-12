import 'package:flutter/material.dart';
import 'buat_lowongan.dart';

class IndustriLowongan extends StatefulWidget {
  const IndustriLowongan({super.key});

  @override
  State<IndustriLowongan> createState() => _IndustriLowonganState();
}

class _IndustriLowonganState extends State<IndustriLowongan>
    with TickerProviderStateMixin {
  late TabController _tabController;

  final List<Map<String, dynamic>> _lowonganList = [
    {
      'title': 'Frontend Developer Intern',
      'company': 'PT. Telkom Indonesia',
      'postedDate': '1 Nov 2024',
      'status': 'Aktif',
      'applicants': 8,
      'skills': ['Flutter', 'Dart', 'UI/UX'],
    },
    {
      'title': 'Backend Engineer Intern',
      'company': 'PT. Telkom Indonesia - Semarang',
      'postedDate': '5 Nov 2024',
      'status': 'Aktif',
      'applicants': 4,
      'skills': ['Laravel', 'PHP', 'MySQL'],
    },
    {
      'title': 'Data Analyst Intern',
      'company': 'PT. Gojek',
      'postedDate': '15 Oct 2024',
      'status': 'Ditutup',
      'applicants': 3,
      'skills': ['Python', 'SQL', 'Tableau'],
    },
    {
      'title': 'UI/UX Designer Intern',
      'company': 'PT. Grab',
      'postedDate': '30 Sep 2024',
      'status': 'Ditutup',
      'applicants': 2,
      'skills': ['Figma', 'UI Design', 'Prototyping'],
    },
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
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
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const BuatLowonganBaru(),
            ),
          );
        },
        backgroundColor: const Color(0xFF388E3C),
        child: const Icon(Icons.add, color: Colors.white),
      ),
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
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 16),
                      const Text(
                        'Kelola Lowongan',
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
                            hintText: 'Cari lowongan...',
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
                      const SizedBox(height: 12),
                      // Filter Pills
                      SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            _buildFilterPill(0, 'Semua (3)'),
                            const SizedBox(width: 10),
                            _buildFilterPill(1, 'Aktif (3)'),
                            const SizedBox(width: 10),
                            _buildFilterPill(2, 'Ditutup (1)'),
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
        body: TabBarView(
          controller: _tabController,
          children: [
            _buildLowonganList(),
            _buildAktifList(),
            _buildDitutupList(),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterPill(int index, String label) {
    final isSelected = _tabController.index == index;
    return GestureDetector(
      onTap: () => _tabController.animateTo(index),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF388E3C) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: isSelected
              ? null
              : Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: isSelected ? Colors.white : const Color(0xFF8A9BC0),
          ),
        ),
      ),
    );
  }

  Widget _buildLowonganList() {
    return ListView.builder(
      padding: const EdgeInsets.all(14),
      itemCount: _lowonganList.length,
      itemBuilder: (context, index) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: _buildLowonganCard(_lowonganList[index]),
        );
      },
    );
  }

  Widget _buildAktifList() {
    final aktif = _lowonganList.where((l) => l['status'] == 'Aktif').toList();
    return ListView.builder(
      padding: const EdgeInsets.all(14),
      itemCount: aktif.length,
      itemBuilder: (context, index) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: _buildLowonganCard(aktif[index]),
        );
      },
    );
  }

  Widget _buildDitutupList() {
    final ditutup =
        _lowonganList.where((l) => l['status'] == 'Ditutup').toList();
    return ListView.builder(
      padding: const EdgeInsets.all(14),
      itemCount: ditutup.length,
      itemBuilder: (context, index) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: _buildLowonganCard(ditutup[index]),
        );
      },
    );
  }

  Widget _buildLowonganCard(Map<String, dynamic> lowongan) {
    final statusColor = lowongan['status'] == 'Aktif'
        ? const Color(0xFF388E3C)
        : const Color(0xFF999999);

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
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      lowongan['title'],
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A2050),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      lowongan['company'],
                      style: const TextStyle(
                        fontSize: 10,
                        color: Color(0xFF8A9BC0),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Dibuat ${lowongan['postedDate']}',
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
                  horizontal: 11,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  lowongan['status'],
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 9),
          Container(
            color: const Color(0xFFEEF0FA),
            height: 0.5,
          ),
          const SizedBox(height: 9),
          Row(
            children: [
              Expanded(
                child: Text(
                  '${lowongan['applicants']} pelamar',
                  style: const TextStyle(
                    fontSize: 10,
                    color: Color(0xFF8A9BC0),
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              Wrap(
                spacing: 5,
                children:
                    (lowongan['skills'] as List<String>).take(2).map((skill) {
                  return Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 3,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE8F0FE),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      skill,
                      style: const TextStyle(
                        fontSize: 9,
                        color: Color(0xFF1A3A8E),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  );
                }).toList(),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
