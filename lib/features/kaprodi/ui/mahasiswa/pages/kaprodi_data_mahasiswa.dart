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
    _tabController = TabController(length: 6, vsync: this);
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
            SliverAppBar(
              backgroundColor: Colors.transparent,
              pinned: true,
              elevation: 0,
              shadowColor: Colors.transparent,
              surfaceTintColor: Colors.transparent,
              automaticallyImplyLeading: false,
              expandedHeight: 0,
              toolbarHeight: 110,
              flexibleSpace: ClipRRect(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header Title
                      Row(
                        children: [
                          Icon(
                            Icons.arrow_back,
                            color: Color(0xFFB0BDD4),
                            size: 20,
                          ),
                          SizedBox(width: 12),
                          Text(
                            'Data Mahasiswa',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: 12),
                      // Search Bar
                      Container(
                        height: 38,
                        padding: EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: Color(0xFF2D2D5F).withValues(alpha: 0.7),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              Icons.search,
                              color: Color(0xFFB0BDD4),
                              size: 16,
                            ),
                            SizedBox(width: 8),
                            Expanded(
                              child: TextField(
                                decoration: InputDecoration(
                                  hintText: 'Cari NIM atau nama mahasiswa...',
                                  hintStyle: TextStyle(
                                    color: Color(0xFFB0BDD4),
                                    fontSize: 12,
                                  ),
                                  border: InputBorder.none,
                                  isDense: true,
                                  contentPadding: EdgeInsets.zero,
                                ),
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                ),
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
        body: Column(
          children: [
            // Filter Pills
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _buildFilterPill(0, 'Semua'),
                    SizedBox(width: 10),
                    _buildFilterPill(1, 'Aktif'),
                    SizedBox(width: 10),
                    _buildFilterPill(2, 'Belum Dospem'),
                    SizedBox(width: 10),
                    _buildFilterPill(3, 'Belum Magang'),
                    SizedBox(width: 10),
                    _buildFilterPill(4, 'Seminar'),
                    SizedBox(width: 10),
                    _buildFilterPill(5, 'Selesai'),
                  ],
                ),
              ),
            ),
            // Content
            Expanded(
              child: TabBarView(
                controller: _tabController,
                children: [
                  _buildStudentListSemua(),
                  _buildStudentListAktif(),
                  _buildStudentListBelumDospem(),
                  _buildStudentListBelumMagang(),
                  _buildStudentListSeminar(),
                  _buildStudentListSelesai(),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStudentListSemua() {
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
        const SizedBox(height: 10),
        _buildStudentCardWithoutAdvisor(
          initials: 'VK',
          name: 'Vincencius Kurnia Putra',
          nim: '3.34.23.2.24',
          className: 'IK-3C',
          company: 'PT. BNI',
          status: 'Belum Dospem',
          statusBg: const Color(0xFFFCE8E6),
          statusText: const Color(0xFF9B2F2F),
          initialsColor: const Color(0xFFFCE8E6),
        ),
        const SizedBox(height: 10),
        _buildStudentCardWithSeminar(
          initials: 'EP',
          name: 'Eka Pramudita',
          nim: '3.34.23.2.07',
          className: 'IK-3C',
          company: 'PT. Gojek',
          status: 'Seminar',
          statusBg: const Color(0xFFE6F4EA),
          statusText: const Color(0xFF1E6E3E),
          initialsColor: const Color(0xFFE6F4EA),
          schedule: 'Jadwal: 15 Jan 2025 · 09:00 · B.301',
        ),
        const SizedBox(height: 10),
        _buildStudentCardWithAdvisor(
          initials: 'RP',
          name: 'Rahma Setianing P.A.',
          nim: '3.34.23.2.19',
          className: 'IK-3C',
          company: 'PT. Tokopedia',
          advisor: 'Slamet Handoko, M.Kom',
          status: 'Selesai',
          statusBg: const Color(0xFFE6F4EA),
          statusText: const Color(0xFF1E6E3E),
          initialsColor: const Color(0xFFE6F4EA),
          hasAction: false,
        ),
      ],
    );
  }

  Widget _buildStudentListAktif() {
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

  Widget _buildStudentListBelumDospem() {
    return ListView(
      padding: const EdgeInsets.all(14),
      children: [
        _buildStudentCardWithoutAdvisor(
          initials: 'VK',
          name: 'Vincencius Kurnia Putra',
          nim: '3.34.23.2.24',
          className: 'IK-3C',
          company: 'PT. BNI',
          status: 'Belum',
          statusBg: const Color(0xFFFCE8E6),
          statusText: const Color(0xFF9B2F2F),
          initialsColor: const Color(0xFFFCE8E6),
        ),
        const SizedBox(height: 10),
        _buildStudentCardWithoutAdvisor(
          initials: 'YK',
          name: 'Yohannes Kevin G.P.',
          nim: '3.34.23.2.26',
          className: 'IK-3C',
          company: 'PT. Grab',
          status: 'Belum',
          statusBg: const Color(0xFFFCE8E6),
          statusText: const Color(0xFF9B2F2F),
          initialsColor: const Color(0xFFFCE8E6),
        ),
        const SizedBox(height: 10),
        _buildStudentCardWithoutAdvisor(
          initials: 'AP',
          name: 'Alvina Putri Aulia',
          nim: '3.34.23.2.02',
          className: 'IK-3C',
          company: 'PT. Shopee',
          status: 'Belum',
          statusBg: const Color(0xFFFCE8E6),
          statusText: const Color(0xFF9B2F2F),
          initialsColor: const Color(0xFFFCE8E6),
        ),
      ],
    );
  }

  Widget _buildStudentListBelumMagang() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(14),
      child: Center(
        child: Text(
          'Tidak ada mahasiswa belum magang',
          style: TextStyle(
            fontSize: 14,
            color: const Color(0xFF8A9BC0),
            fontWeight: FontWeight.w500,
          ),
        ),
      ),
    );
  }

  Widget _buildStudentListSelesai() {
    return ListView(
      padding: const EdgeInsets.all(14),
      children: [
        _buildStudentCardWithAdvisor(
          initials: 'RP',
          name: 'Rahma Setianing P.A.',
          nim: '3.34.23.2.19',
          className: 'IK-3C',
          company: 'PT. Tokopedia',
          advisor: 'Slamet Handoko, M.Kom',
          status: 'Selesai',
          statusBg: const Color(0xFFE6F4EA),
          statusText: const Color(0xFF1E6E3E),
          initialsColor: const Color(0xFFE6F4EA),
          hasAction: false,
        ),
      ],
    );
  }

  Widget _buildStudentListSeminar() {
    return ListView(
      padding: const EdgeInsets.all(14),
      children: [
        _buildStudentCardWithSeminar(
          initials: 'EP',
          name: 'Eka Pramudita',
          nim: '3.34.23.2.07',
          className: 'IK-3C',
          company: 'PT. Gojek',
          status: 'Seminar',
          statusBg: const Color(0xFFE6F4EA),
          statusText: const Color(0xFF1E6E3E),
          initialsColor: const Color(0xFFE6F4EA),
          schedule: 'Jadwal: 15 Jan 2025 · 09:00 · B.301',
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
                'Dospem: ',
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

  Widget _buildStudentCardWithoutAdvisor({
    required String initials,
    required String name,
    required String nim,
    required String className,
    required String company,
    required String status,
    required Color statusBg,
    required Color statusText,
    required Color initialsColor,
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
              Expanded(
                child: Text(
                  'Belum ada dosen pembimbing',
                  style: const TextStyle(
                    fontSize: 10,
                    color: Color(0xFFE53E3E),
                    fontWeight: FontWeight.w500,
                    fontStyle: FontStyle.italic,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: () {},
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1A1A3E),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Text(
                    'Tugaskan Dosen',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStudentCardWithSeminar({
    required String initials,
    required String name,
    required String nim,
    required String className,
    required String company,
    required String status,
    required Color statusBg,
    required Color statusText,
    required Color initialsColor,
    required String schedule,
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
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  schedule,
                  style: const TextStyle(
                    fontSize: 10,
                    color: Color(0xFF8A9BC0),
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              TextButton(
                onPressed: () {},
                style: TextButton.styleFrom(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 5,
                  ),
                  backgroundColor: const Color(0xFF1A1A3E),
                ),
                child: const Text(
                  'Detail',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
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
                : const Color(0xFFC5CDE2),
            width: 1.2,
          ),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: _tabController.index == index
                ? Colors.white
                : const Color(0xFF1A2050),
          ),
        ),
      ),
    );
  }
}
