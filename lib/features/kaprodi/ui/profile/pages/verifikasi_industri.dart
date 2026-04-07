import 'package:flutter/material.dart';

class VerifikasiIndustriPage extends StatefulWidget {
  const VerifikasiIndustriPage({super.key});

  @override
  State<VerifikasiIndustriPage> createState() => _VerifikasiIndustriPageState();
}

class _VerifikasiIndustriPageState extends State<VerifikasiIndustriPage>
    with TickerProviderStateMixin {
  late TabController _tabController;

  final List<Map<String, String>> _industryList = [
    {
      'name': 'PT Telkom Indonesia',
      'division': 'Digital Solutions',
      'city': 'Jakarta',
      'pic': 'Budi Santoso',
      'email': 'budi@telkom.co.id',
      'description': '5 posisi tersedia',
      'avatar': 'PT',
      'color': '#3D5AF1',
    },
    {
      'name': 'PT BNI (Persero)',
      'division': 'Technology Innovation',
      'city': 'Semarang',
      'pic': 'Siti Rahayu',
      'email': 'siti@bni.co.id',
      'description': '3 posisi tersedia',
      'avatar': 'BN',
      'color': '#E53E3E',
    },
    {
      'name': 'PT Mitra Digital Nusantara',
      'division': 'Software Development',
      'city': 'Bandung',
      'pic': 'Ahmad Wijaya',
      'email': 'ahmad@mitrdig.com',
      'description': '7 posisi tersedia',
      'avatar': 'MD',
      'color': '#7C3AED',
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
                  padding: const EdgeInsets.fromLTRB(18, 12, 18, 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 4),
                      // Title
                      const Text(
                        'Verifikasi Industri',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 17,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 12),
                      // Search Bar
                      Container(
                        height: 40,
                        decoration: BoxDecoration(
                          color: const Color(0xFF2D2D5F).withValues(alpha: 0.7),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        child: Row(
                          children: [
                            Icon(
                              Icons.search,
                              color: Colors.white.withValues(alpha: 0.6),
                              size: 14,
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: TextField(
                                style: const TextStyle(
                                  color: Color(0xFFE8EAF6),
                                  fontSize: 12,
                                ),
                                decoration: InputDecoration(
                                  border: InputBorder.none,
                                  hintText: 'Cari nama perusahaan...',
                                  hintStyle: TextStyle(
                                    color: Colors.white.withValues(alpha: 0.6),
                                    fontSize: 12,
                                  ),
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
            // Tab Pills
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _buildTabPill(0, 'Menunggu (3)'),
                    const SizedBox(width: 10),
                    _buildTabPill(1, 'Terverifikasi (4)'),
                    const SizedBox(width: 10),
                    _buildTabPill(2, 'Ditolak (1)'),
                  ],
                ),
              ),
            ),
            // Content
            Expanded(
              child: TabBarView(
                controller: _tabController,
                children: [
                  _buildPendingIndustries(),
                  _buildVerifiedIndustries(),
                  _buildRejectedIndustries(),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTabPill(int index, String label) {
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

  Widget _buildPendingIndustries() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(14),
      child: Column(
        children: [
          // Purple Banner
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFFF3E8FF),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: const Color(0xFF7C3AED),
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
                const SizedBox(width: 10),
                const Expanded(
                  child: Text(
                    '3 perusahaan menunggu verifikasi akun',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF6D28D9),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          // Industry Cards
          ..._industryList.map((industry) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: _buildIndustryCard(industry),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildVerifiedIndustries() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(14),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFFE6F4EA),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E6E3E),
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
                const SizedBox(width: 10),
                const Expanded(
                  child: Text(
                    '4 perusahaan sudah terverifikasi',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF1E6E3E),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'Data akun terverifikasi akan ditampilkan di sini',
            style: TextStyle(
              fontSize: 11,
              color: Color(0xFF8A9BC0),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRejectedIndustries() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(14),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFFFCE8E6),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: const Color(0xFF9B2F2F),
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
                const SizedBox(width: 10),
                const Expanded(
                  child: Text(
                    '1 perusahaan ditolak',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF9B2F2F),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'Data akun ditolak akan ditampilkan di sini',
            style: TextStyle(
              fontSize: 11,
              color: Color(0xFF8A9BC0),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildIndustryCard(Map<String, String> industry) {
    final color = _parseColor(industry['color']!);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
      ),
      padding: const EdgeInsets.all(13),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Company Header
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: Text(
                    industry['avatar']!,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: color,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  industry['name']!,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1A2050),
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFF3E8FF),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  'Menunggu',
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF7C3AED),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          // Division and City
          Text(
            '${industry['division']} · ${industry['city']}',
            style: const TextStyle(
              fontSize: 10,
              color: Color(0xFF8A9BC0),
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 10),
          // Divider
          Container(
            color: const Color(0xFFEEF0FA),
            height: 0.5,
          ),
          const SizedBox(height: 10),
          // Company Info
          Row(
            children: [
              const Text(
                'PIC: ',
                style: TextStyle(
                  fontSize: 10,
                  color: Color(0xFF8A9BC0),
                  fontWeight: FontWeight.w500,
                ),
              ),
              Expanded(
                child: Text(
                  industry['pic']!,
                  style: const TextStyle(
                    fontSize: 10,
                    color: Color(0xFF1A2050),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              const Text(
                'Email: ',
                style: TextStyle(
                  fontSize: 10,
                  color: Color(0xFF8A9BC0),
                  fontWeight: FontWeight.w500,
                ),
              ),
              Expanded(
                child: Text(
                  industry['email']!,
                  style: const TextStyle(
                    fontSize: 10,
                    color: Color(0xFF1A2050),
                    fontWeight: FontWeight.w600,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              const Text(
                'Lowongan yang akan dipost: ',
                style: TextStyle(
                  fontSize: 10,
                  color: Color(0xFF8A9BC0),
                  fontWeight: FontWeight.w500,
                ),
              ),
              Expanded(
                child: Text(
                  industry['description']!,
                  style: const TextStyle(
                    fontSize: 10,
                    color: Color(0xFF1A2050),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          // Action Buttons
          Row(
            children: [
              // Reject Button
              Expanded(
                child: GestureDetector(
                  onTap: () {},
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFCE8E6),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: const Color(0xFFEA4335),
                        width: 1,
                      ),
                    ),
                    child: const Text(
                      'Tolak',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFFEA4335),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              // Verify Button
              Expanded(
                child: GestureDetector(
                  onTap: () {},
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1A1A3E),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text(
                      'Verifikasi Akun',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
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

  Color _parseColor(String colorHex) {
    final String hexColor = colorHex.replaceAll('#', '');
    return Color(int.parse('FF$hexColor', radix: 16));
  }
}
