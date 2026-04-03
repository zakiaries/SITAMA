import 'package:flutter/material.dart';

class KaprodiDataMahasiswa extends StatefulWidget {
  const KaprodiDataMahasiswa({super.key});

  @override
  State<KaprodiDataMahasiswa> createState() => _KaprodiDataMahasiswaState();
}

class _KaprodiDataMahasiswaState extends State<KaprodiDataMahasiswa>
    with TickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 5, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFEEF1F8),
      body: NestedScrollView(
        headerSliverBuilder: (BuildContext context, bool innerBoxIsScrolled) {
          return [
            SliverAppBar(
              backgroundColor: const Color(0xFF1A1A3E),
              pinned: true,
              elevation: 0,
              automaticallyImplyLeading: false,
              expandedHeight: 0,
              toolbarHeight: 125,
              flexibleSpace: Container(
                color: const Color(0xFF1A1A3E),
                padding: const EdgeInsets.fromLTRB(16, 10, 16, 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        IconButton(
                          icon: const Icon(
                            Icons.arrow_back,
                            color: Color(0xFFE8EAF6),
                            size: 16,
                          ),
                          onPressed: () {},
                          padding: EdgeInsets.zero,
                          constraints:
                              const BoxConstraints(minWidth: 34, minHeight: 34),
                        ),
                        const SizedBox(width: 8),
                        const Text(
                          'Data Mahasiswa',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Container(
                      height: 38,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFF2D2D5F),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.search,
                            color: Color(0xFFB0BDD4),
                            size: 14,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: TextField(
                              decoration: InputDecoration(
                                hintText: 'Cari NIM atau nama mahasiswa...',
                                hintStyle: const TextStyle(
                                  color: Color(0xFFB0BDD4),
                                  fontSize: 11,
                                ),
                                border: InputBorder.none,
                                isDense: true,
                                contentPadding: EdgeInsets.zero,
                              ),
                              style: const TextStyle(
                                color: Color(0xFFE8EAF6),
                                fontSize: 11,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              bottom: PreferredSize(
                preferredSize: const Size.fromHeight(45),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _buildFilterPill(0, 'Semua (48)'),
                        const SizedBox(width: 8),
                        _buildFilterPill(1, 'Aktif (38)'),
                        const SizedBox(width: 8),
                        _buildFilterPill(2, 'Selesai (6)'),
                        const SizedBox(width: 8),
                        _buildFilterPill(3, 'Belum Dosen (5)'),
                        const SizedBox(width: 8),
                        _buildFilterPill(4, 'Verif. Surat (4)'),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ];
        },
        body: TabBarView(
          controller: _tabController,
          children: [
            // Semua
            _buildStudentList(),
            // Aktif
            _buildStudentList(),
            // Selesai
            _buildStudentList(),
            // Belum Dosen
            _buildStudentList(),
            // Verif. Surat
            _buildStudentList(),
          ],
        ),
      ),
    );
  }

  Widget _buildStudentList() {
    return ListView(
      padding: const EdgeInsets.all(14),
      children: [
        _buildStudentCardWithAdvisor(
          initials: 'MZ',
          name: 'Muhammad Zaki Aries Putra',
          nim: '3.34.23.2.15',
          className: 'IK-3C',
          company: 'PT. Telkom Indonesia',
          advisor: 'Kuwat Santoso, M.Kom',
          status: 'Aktif',
          statusBg: const Color(0xFFE8F0FE),
          statusText: const Color(0xFF1A3A8E),
          initialsColor: const Color(0xFFE8F0FE),
          hasAction: true,
        ),
        const SizedBox(height: 10),
        _buildStudentCardWithAdvisor(
          initials: 'AR',
          name: 'Alif Rahman Maulana',
          nim: '3.34.23.2.01',
          className: 'IK-3C',
          company: 'PT. Gojek',
          advisor: 'Slamet Handoko, M.Kom',
          status: 'Aktif',
          statusBg: const Color(0xFFE8F0FE),
          statusText: const Color(0xFF1A3A8E),
          initialsColor: const Color(0xFFE8F0FE),
          hasAction: true,
        ),
      ],
    );
  }

  Widget _buildStudentCardWithAdvisor({
    required String initials,
    required String name,
    required String nim,
    required String className,
    required String company,
    required String advisor,
    required String status,
    required Color statusBg,
    required Color statusText,
    required Color initialsColor,
    bool hasAction = false,
  }) {
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
                  color: initialsColor,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    initials,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1A3A8E),
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
                      name,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A2050),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '$nim · $className · $company',
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
                  horizontal: 11,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: statusBg,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  status,
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: statusText,
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
              const Text(
                'Pembimbing: ',
                style: TextStyle(
                  fontSize: 10,
                  color: Color(0xFF8A9BC0),
                  fontWeight: FontWeight.w500,
                ),
              ),
              Expanded(
                child: Text(
                  advisor,
                  style: const TextStyle(
                    fontSize: 10,
                    color: Color(0xFF1A2050),
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
              if (hasAction)
                TextButton(
                  onPressed: () {},
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 5,
                    ),
                    backgroundColor: const Color(0xFFE8F0FE),
                  ),
                  child: const Text(
                    'Ubah',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1A3A8E),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildFilterPill(int index, String label) {
    return GestureDetector(
      onTap: () {
        setState(() {
          _tabController.animateTo(index);
        });
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: _tabController.index == index
              ? const Color(0xFF1A1A3E)
              : Colors.white,
          border: Border.all(
            color: _tabController.index == index
                ? const Color(0xFF1A1A3E)
                : const Color(0xFF1A3A8E),
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(24),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: _tabController.index == index
                ? Colors.white
                : const Color(0xFF1A3A8E),
          ),
        ),
      ),
    );
  }
}
