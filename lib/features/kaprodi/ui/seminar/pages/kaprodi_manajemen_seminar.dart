import 'package:flutter/material.dart';

class KaprodiManajemenSeminar extends StatefulWidget {
  const KaprodiManajemenSeminar({super.key});

  @override
  State<KaprodiManajemenSeminar> createState() =>
      _KaprodiManajemenSeminarState();
}

class _KaprodiManajemenSeminarState extends State<KaprodiManajemenSeminar>
    with TickerProviderStateMixin {
  late TabController _tabController;

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
              toolbarHeight: 70,
              flexibleSpace: Container(
                color: const Color(0xFF1A1A3E),
                padding: const EdgeInsets.fromLTRB(18, 16, 18, 14),
                child: Row(
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
                    const SizedBox(width: 10),
                    const Text(
                      'Manajemen Seminar',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const Spacer(),
                    IconButton(
                      icon: const Icon(
                        Icons.add,
                        color: Color(0xFFE8EAF6),
                        size: 16,
                      ),
                      onPressed: () {},
                      padding: EdgeInsets.zero,
                      constraints:
                          const BoxConstraints(minWidth: 34, minHeight: 34),
                    ),
                  ],
                ),
              ),
              bottom: PreferredSize(
                preferredSize: const Size.fromHeight(40),
                child: Theme(
                  data: Theme.of(context).copyWith(
                    splashColor: Colors.transparent,
                    highlightColor: Colors.transparent,
                  ),
                  child: TabBar(
                    controller: _tabController,
                    labelColor: const Color(0xFF1A1A3E),
                    unselectedLabelColor: const Color(0xFF5A6E90),
                    indicator: BoxDecoration(
                      color: const Color(0xFF1A1A3E),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    unselectedLabelStyle: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                    labelStyle: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                    tabs: const [
                      Padding(
                        padding:
                            EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        child: Text('Akan Datang (2)'),
                      ),
                      Padding(
                        padding:
                            EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        child: Text('Belum Jadwal (1)'),
                      ),
                      Padding(
                        padding:
                            EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        child: Text('Selesai (3)'),
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
            _buildSeminarList(),
            _buildSeminarList(),
            _buildSeminarList(),
          ],
        ),
      ),
    );
  }

  Widget _buildSeminarList() {
    return ListView(
      padding: const EdgeInsets.all(14),
      children: [
        _buildSeminarCard(
          name: 'Muhammad Zaki Aries Putra',
          nim: '3.34.23.2.15',
          program: 'Teknik Informatika',
          university: 'Polines',
          date: '15 Jan 2025',
          time: '09:00 WIB',
          room: 'B.301',
          advisor: 'Kuwat Santoso, M.Kom',
          examiner: 'Slamet Handoko, M.Kom',
          status: 'Terjadwal',
          statusColor: const Color(0xFFE8F0FE),
          statusTextColor: const Color(0xFF1A3A8E),
        ),
        const SizedBox(height: 10),
        _buildSeminarCard(
          name: 'Alif Rahman Maulana',
          nim: '3.34.23.2.01',
          program: 'Teknik Informatika',
          university: 'Polines',
          date: '16 Jan 2025',
          time: '10:00 WIB',
          room: 'C.201',
          advisor: 'Slamet Handoko, M.Kom',
          examiner: 'Sirli Fahriah, M.Kom',
          status: 'Terjadwal',
          statusColor: const Color(0xFFE8F0FE),
          statusTextColor: const Color(0xFF1A3A8E),
        ),
      ],
    );
  }

  Widget _buildSeminarCard({
    required String name,
    required String nim,
    required String program,
    required String university,
    required String date,
    required String time,
    required String room,
    required String advisor,
    required String examiner,
    required String status,
    required Color statusColor,
    required Color statusTextColor,
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
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                name,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1A2050),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 11,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: statusColor,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  status,
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: statusTextColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            '$nim · $program · $university',
            style: const TextStyle(
              fontSize: 10,
              color: Color(0xFF8A9BC0),
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFF5F6FF),
                    borderRadius: BorderRadius.circular(9),
                  ),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 8,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Tanggal',
                        style: TextStyle(
                          fontSize: 10,
                          color: Color(0xFF8A9BC0),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        date,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1A2050),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFF5F6FF),
                    borderRadius: BorderRadius.circular(9),
                  ),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 8,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Waktu',
                        style: TextStyle(
                          fontSize: 10,
                          color: Color(0xFF8A9BC0),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        time,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1A2050),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFF5F6FF),
                    borderRadius: BorderRadius.circular(9),
                  ),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 8,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Ruang',
                        style: TextStyle(
                          fontSize: 10,
                          color: Color(0xFF8A9BC0),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        room,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1A2050),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 11),
          Text(
            'Pembimbing: $advisor\nPenguji: $examiner',
            style: const TextStyle(
              fontSize: 11,
              color: Color(0xFF3A4A6E),
              fontWeight: FontWeight.w500,
              height: 1.6,
            ),
          ),
          const SizedBox(height: 11),
          Row(
            children: [
              Expanded(
                child: TextButton(
                  onPressed: () {},
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 8,
                    ),
                    backgroundColor: const Color(0xFFE8F0FE),
                  ),
                  child: const Text(
                    'Edit Jadwal',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1A3A8E),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: TextButton(
                  onPressed: () {},
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 8,
                    ),
                    backgroundColor: const Color(0xFF1A1A3E),
                  ),
                  child: const Text(
                    'Lihat Detail',
                    style: TextStyle(
                      fontSize: 11,
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
}
