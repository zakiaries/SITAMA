import 'package:flutter/material.dart';

class KaprodiDataDosen extends StatefulWidget {
  const KaprodiDataDosen({super.key});

  @override
  State<KaprodiDataDosen> createState() => _KaprodiDataDosenState();
}

class _KaprodiDataDosenState extends State<KaprodiDataDosen> {
  int _selectedTabIndex = 0;

  final List<String> _tabs = ['Semua (18)', 'Aktif (16)', 'Cuti (2)'];

  final List<Map<String, String>> _dosenList = [
    {
      'name': 'Kuwat Santoso, M.Kom',
      'nip': '1981.01.15.001 · Komputer Jaringan',
      'avatar': 'K',
      'color': '#3D5AF1',
      'burden': '16 Mahasiswa'
    },
    {
      'name': 'Slamet Handoko, M.Kom',
      'nip': '1984.05.20.002 · Sistem Informasi',
      'avatar': 'SH',
      'color': '#7C3AED',
      'burden': '14 Mahasiswa'
    },
    {
      'name': 'Sirli Fahriah, M.Kom',
      'nip': '1986.08.10.003 · Mobile Development',
      'avatar': 'SF',
      'color': '#1A4BBB',
      'burden': '13 Mahasiswa'
    },
    {
      'name': 'Puguh Santoso, M.Kom',
      'nip': '1982.03.25.004 · Web Development',
      'avatar': 'PS',
      'color': '#E53E3E',
      'burden': '15 Mahasiswa'
    },
    {
      'name': 'Toko Kristiono, M.Kom',
      'nip': '1988.11.08.005 · Data Science',
      'avatar': 'TK',
      'color': '#1E6E3E',
      'burden': '12 Mahasiswa'
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F5FA),
      body: Column(
        children: [
          // Header
          Container(
            decoration: const BoxDecoration(
              color: Color(0xFF1A1A3E),
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(28),
                bottomRight: Radius.circular(28),
              ),
            ),
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(height: MediaQuery.of(context).padding.top + 8),
                Padding(
                  padding: const EdgeInsets.only(bottom: 14),
                  child: Text(
                    'Data Dosen',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 19,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                // Search Bar
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  child: Row(
                    children: [
                      Icon(
                        Icons.search,
                        color: Colors.white.withOpacity(0.6),
                        size: 15,
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
                            hintText: 'Cari nama atau NIP dosen...',
                            hintStyle: TextStyle(
                              color: Colors.white.withOpacity(0.6),
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
          // Tabs
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: List.generate(
                  _tabs.length,
                  (index) => Padding(
                    padding:
                        const EdgeInsets.symmetric(vertical: 12, horizontal: 4),
                    child: GestureDetector(
                      onTap: () {
                        setState(() {
                          _selectedTabIndex = index;
                        });
                      },
                      child: Column(
                        children: [
                          Text(
                            _tabs[index],
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: _selectedTabIndex == index
                                  ? const Color(0xFF1A1A3E)
                                  : const Color(0xFF8A9BC0),
                            ),
                          ),
                          if (_selectedTabIndex == index)
                            Padding(
                              padding: const EdgeInsets.only(top: 6),
                              child: Container(
                                width: 16,
                                height: 3,
                                decoration: BoxDecoration(
                                  color: const Color(0xFF1A1A3E),
                                  borderRadius: BorderRadius.circular(1.5),
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
          // Dosen List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
              itemCount: _dosenList.length,
              itemBuilder: (context, index) {
                final dosen = _dosenList[index];
                return _buildDosenCard(dosen);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDosenCard(Map<String, String> dosen) {
    final color = _parseColor(dosen['color']!);

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(13),
        border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(10),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Center(
                    child: Text(
                      dosen['avatar']!,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: color,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        dosen['name']!,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1A2050),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        dosen['nip']!,
                        style: const TextStyle(
                          fontSize: 9,
                          color: Color(0xFF8A9BC0),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Divider(
            height: 1,
            color: const Color(0xFFD0D6EB),
            thickness: 0.5,
          ),
          Padding(
            padding: const EdgeInsets.all(10),
            child: Row(
              children: [
                Text(
                  'Beban:',
                  style: const TextStyle(
                    fontSize: 9,
                    color: Color(0xFF8A9BC0),
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(width: 6),
                Text(
                  dosen['burden']!,
                  style: const TextStyle(
                    fontSize: 10,
                    color: Color(0xFF1A1A3E),
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const Spacer(),
                GestureDetector(
                  onTap: () {},
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: color.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      'Ubah',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: color,
                      ),
                    ),
                  ),
                ),
              ],
            ),
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
