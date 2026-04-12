import 'package:flutter/material.dart';
import 'kaprodi_dosen_detail.dart';

class KaprodiDataDosen extends StatefulWidget {
  const KaprodiDataDosen({super.key});

  @override
  State<KaprodiDataDosen> createState() => _KaprodiDataDosenState();
}

class _KaprodiDataDosenState extends State<KaprodiDataDosen> {
  final List<Map<String, dynamic>> _dosenBurden = [
    {
      'name': 'Kuwat Santoso, M.Kom',
      'nip': '1981.01.15.001',
      'specialization': 'Komputer Jaringan',
      'count': 16,
      'maxLoad': 20,
      'initials': 'KS',
      'bgColor': '#3D5AF1',
      'students': [
        {
          'name': 'Muhammad Zaki Aries Putra',
          'nim': '3.34.23.2.15',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'MZ',
        },
        {
          'name': 'Budi Santoso',
          'nim': '3.34.23.2.02',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'BS',
        },
        {
          'name': 'Citra Dewi Kusuma',
          'nim': '3.34.23.2.03',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'CDK',
        },
        {
          'name': 'Doni Hermawan',
          'nim': '3.34.23.2.04',
          'className': 'IK-3C',
          'company': 'PT. XL Axiata',
          'status': 'Seminar',
          'initials': 'DH',
        },
        {
          'name': 'Erly Fitriansyah',
          'nim': '3.34.23.2.05',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Seminar',
          'initials': 'EF',
        },
        {
          'name': 'Feni Suryani',
          'nim': '3.34.23.2.06',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'FS',
        },
        {
          'name': 'Gatot Pardiman',
          'nim': '3.34.23.2.08',
          'className': 'IK-3C',
          'company': 'PT. Axyz',
          'status': 'Aktif',
          'initials': 'GP',
        },
        {
          'name': 'Heny Handayani',
          'nim': '3.34.23.2.09',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Selesai',
          'initials': 'HH',
        },
        {
          'name': 'Irwan Suryanto',
          'nim': '3.34.23.2.10',
          'className': 'IK-3C',
          'company': 'PT. Axiata',
          'status': 'Aktif',
          'initials': 'IS',
        },
        {
          'name': 'Joko Widodo',
          'nim': '3.34.23.2.11',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'JW',
        },
        {
          'name': 'Kiki Rahmawati',
          'nim': '3.34.23.2.12',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Seminar',
          'initials': 'KR',
        },
        {
          'name': 'Linda Sari',
          'nim': '3.34.23.2.13',
          'className': 'IK-3C',
          'company': 'PT. XL Axiata',
          'status': 'Aktif',
          'initials': 'LS',
        },
        {
          'name': 'Madi Hermanto',
          'nim': '3.34.23.2.14',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'MH',
        },
        {
          'name': 'Nuri Supriyatna',
          'nim': '3.34.23.2.16',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Seminar',
          'initials': 'NS',
        },
        {
          'name': 'Oka Pratama',
          'nim': '3.34.23.2.17',
          'className': 'IK-3C',
          'company': 'PT. Axyz',
          'status': 'Aktif',
          'initials': 'OP',
        },
        {
          'name': 'Putri Handini',
          'nim': '3.34.23.2.18',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'PH',
        },
      ],
    },
    {
      'name': 'Slamet Handoko, M.Kom',
      'nip': '1984.05.20.002',
      'specialization': 'Sistem Informasi',
      'count': 14,
      'maxLoad': 20,
      'initials': 'SH',
      'bgColor': '#7C3AED',
      'students': [
        {
          'name': 'Alif Rahman Maulana',
          'nim': '3.34.23.2.01',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Aktif',
          'initials': 'ARM',
        },
        {
          'name': 'Eka Pramudita',
          'nim': '3.34.23.2.07',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Seminar',
          'initials': 'EP',
        },
        {
          'name': 'Rahma Setianing P.A.',
          'nim': '3.34.23.2.19',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Selesai',
          'initials': 'RSP',
        },
        {
          'name': 'Qori Yusuf',
          'nim': '3.34.23.2.20',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Aktif',
          'initials': 'QY',
        },
        {
          'name': 'Rina Setiawati',
          'nim': '3.34.23.2.21',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Aktif',
          'initials': 'RS',
        },
        {
          'name': 'Sutrisno Hermawan',
          'nim': '3.34.23.2.22',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Seminar',
          'initials': 'SH',
        },
        {
          'name': 'Tia Suryawati',
          'nim': '3.34.23.2.23',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Aktif',
          'initials': 'TS',
        },
        {
          'name': 'Umar Farizan',
          'nim': '3.34.23.2.25',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Aktif',
          'initials': 'UF',
        },
        {
          'name': 'Vina Kusuma',
          'nim': '3.34.23.2.27',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Seminar',
          'initials': 'VK',
        },
        {
          'name': 'Willy Hermanto',
          'nim': '3.34.23.2.28',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Aktif',
          'initials': 'WH',
        },
        {
          'name': 'Xenia Supriyatna',
          'nim': '3.34.23.2.29',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Aktif',
          'initials': 'XS',
        },
        {
          'name': 'Yani Kusuma',
          'nim': '3.34.23.2.30',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Aktif',
          'initials': 'YK',
        },
        {
          'name': 'Zulkifli Rahman',
          'nim': '3.34.23.2.31',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Seminar',
          'initials': 'ZR',
        },
        {
          'name': 'Ana Wijaya',
          'nim': '3.34.23.2.32',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Aktif',
          'initials': 'AW',
        },
      ],
    },
    {
      'name': 'Sirli Fahriah, M.Kom',
      'nip': '1986.08.10.003',
      'specialization': 'Mobile Development',
      'count': 13,
      'maxLoad': 20,
      'initials': 'SF',
      'bgColor': '#E53E3E',
      'students': [
        {
          'name': 'Bambang Suryanto',
          'nim': '3.34.23.2.33',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'BS',
        },
        {
          'name': 'Cilla Kusuma',
          'nim': '3.34.23.2.34',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'CK',
        },
        {
          'name': 'Danang Hermawan',
          'nim': '3.34.23.2.35',
          'className': 'IK-3C',
          'company': 'PT. XL Axiata',
          'status': 'Seminar',
          'initials': 'DH',
        },
        {
          'name': 'Eka Setiawan',
          'nim': '3.34.23.2.36',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'ES',
        },
        {
          'name': 'Feti Kusuma',
          'nim': '3.34.23.2.37',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'FK',
        },
        {
          'name': 'Gede Karta',
          'nim': '3.34.23.2.38',
          'className': 'IK-3C',
          'company': 'PT. Axyz',
          'status': 'Seminar',
          'initials': 'GK',
        },
        {
          'name': 'Hendra Wijaya',
          'nim': '3.34.23.2.39',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'HW',
        },
        {
          'name': 'Isti Kusuma',
          'nim': '3.34.23.2.40',
          'className': 'IK-3C',
          'company': 'PT. Axiata',
          'status': 'Aktif',
          'initials': 'IK',
        },
        {
          'name': 'Jaka Wijaya',
          'nim': '3.34.23.2.41',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Seminar',
          'initials': 'JW',
        },
        {
          'name': 'Karina Dewi',
          'nim': '3.34.23.2.42',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'KD',
        },
        {
          'name': 'Lalita Kusuma',
          'nim': '3.34.23.2.43',
          'className': 'IK-3C',
          'company': 'PT. XL Axiata',
          'status': 'Aktif',
          'initials': 'LK',
        },
        {
          'name': 'Malik Hidayat',
          'nim': '3.34.23.2.44',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'MH',
        },
        {
          'name': 'Nita Setiawan',
          'nim': '3.34.23.2.45',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Selesai',
          'initials': 'NS',
        },
      ],
    },
    {
      'name': 'Puguh Santoso, M.Kom',
      'nip': '1982.03.25.004',
      'specialization': 'Web Development',
      'count': 15,
      'maxLoad': 20,
      'initials': 'PS',
      'bgColor': '#1A4BBB',
      'students': [
        {
          'name': 'Odi Kurniawan',
          'nim': '3.34.23.2.46',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Aktif',
          'initials': 'OK',
        },
        {
          'name': 'Petra Handini',
          'nim': '3.34.23.2.47',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Aktif',
          'initials': 'PH',
        },
        {
          'name': 'Quincy Wijaya',
          'nim': '3.34.23.2.48',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Seminar',
          'initials': 'QW',
        },
        {
          'name': 'Rini Kusuma',
          'nim': '3.34.23.2.49',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Aktif',
          'initials': 'RK',
        },
        {
          'name': 'Sandi Hermawan',
          'nim': '3.34.23.2.50',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Aktif',
          'initials': 'SH',
        },
        {
          'name': 'Tania Setiawan',
          'nim': '3.34.23.2.51',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Seminar',
          'initials': 'TS',
        },
        {
          'name': 'Usman Wijaya',
          'nim': '3.34.23.2.52',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Aktif',
          'initials': 'UW',
        },
        {
          'name': 'Vicky Kusuma',
          'nim': '3.34.23.2.53',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Aktif',
          'initials': 'VK',
        },
        {
          'name': 'Wawan Hermanto',
          'nim': '3.34.23.2.54',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Seminar',
          'initials': 'WH',
        },
        {
          'name': 'Xavier Wijaya',
          'nim': '3.34.23.2.55',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Aktif',
          'initials': 'XW',
        },
        {
          'name': 'Yenti Kusuma',
          'nim': '3.34.23.2.56',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Aktif',
          'initials': 'YK',
        },
        {
          'name': 'Zeni Setiawan',
          'nim': '3.34.23.2.57',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Aktif',
          'initials': 'ZS',
        },
        {
          'name': 'Amin Hermawan',
          'nim': '3.34.23.2.58',
          'className': 'IK-3C',
          'company': 'PT. Gojek',
          'status': 'Seminar',
          'initials': 'AH',
        },
        {
          'name': 'Berta Kusuma',
          'nim': '3.34.23.2.59',
          'className': 'IK-3C',
          'company': 'PT. Tokopedia',
          'status': 'Aktif',
          'initials': 'BK',
        },
        {
          'name': 'Cecilia Wijaya',
          'nim': '3.34.23.2.60',
          'className': 'IK-3C',
          'company': 'PT. Grab',
          'status': 'Aktif',
          'initials': 'CW',
        },
      ],
    },
    {
      'name': 'Toko Kristiono, M.Kom',
      'nip': '1988.11.08.005',
      'specialization': 'Data Science',
      'count': 12,
      'maxLoad': 20,
      'initials': 'TK',
      'bgColor': '#1E6E3E',
      'students': [
        {
          'name': 'Diana Kusuma',
          'nim': '3.34.23.2.61',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'DK',
        },
        {
          'name': 'Eddy Hermawan',
          'nim': '3.34.23.2.62',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'EH',
        },
        {
          'name': 'Fiona Kusuma',
          'nim': '3.34.23.2.63',
          'className': 'IK-3C',
          'company': 'PT. XL Axiata',
          'status': 'Seminar',
          'initials': 'FK',
        },
        {
          'name': 'Gunawan Wijaya',
          'nim': '3.34.23.2.64',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'GW',
        },
        {
          'name': 'Hani Kusuma',
          'nim': '3.34.23.2.65',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'HK',
        },
        {
          'name': 'Ivan Hermanto',
          'nim': '3.34.23.2.66',
          'className': 'IK-3C',
          'company': 'PT. Axyz',
          'status': 'Seminar',
          'initials': 'IH',
        },
        {
          'name': 'Jasmine Wijaya',
          'nim': '3.34.23.2.67',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'JW',
        },
        {
          'name': 'Kevin Kusuma',
          'nim': '3.34.23.2.68',
          'className': 'IK-3C',
          'company': 'PT. Axiata',
          'status': 'Aktif',
          'initials': 'KK',
        },
        {
          'name': 'Lia Setiawan',
          'nim': '3.34.23.2.69',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Seminar',
          'initials': 'LS',
        },
        {
          'name': 'Maximo Hermawan',
          'nim': '3.34.23.2.70',
          'className': 'IK-3C',
          'company': 'PT. Telkom Indonesia',
          'status': 'Aktif',
          'initials': 'MH',
        },
        {
          'name': 'Nenna Kusuma',
          'nim': '3.34.23.2.71',
          'className': 'IK-3C',
          'company': 'PT. XL Axiata',
          'status': 'Aktif',
          'initials': 'NK',
        },
        {
          'name': 'Orly Wijaya',
          'nim': '3.34.23.2.72',
          'className': 'IK-3C',
          'company': 'PT. Indosat',
          'status': 'Aktif',
          'initials': 'OW',
        },
      ],
    },
  ];

  @override
  void initState() {
    super.initState();
  }

  @override
  void dispose() {
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
                  padding: const EdgeInsets.fromLTRB(18, 12, 18, 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 16),
                      // Title
                      const Text(
                        'Data Dosen',
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
                          borderRadius: BorderRadius.circular(8),
                          color: Colors.white,
                          border: Border.all(
                            color: const Color(0xFFE5E7EB),
                            width: 1,
                          ),
                        ),
                        child: TextField(
                          decoration: InputDecoration(
                            hintText: 'Cari nama dosen...',
                            hintStyle: const TextStyle(
                              color: Color(0xFF9CA3AF),
                              fontSize: 13,
                            ),
                            prefixIcon: const Icon(
                              Icons.search,
                              size: 18,
                              color: Color(0xFF9CA3AF),
                            ),
                            contentPadding: const EdgeInsets.symmetric(
                              vertical: 0,
                              horizontal: 12,
                            ),
                            border: InputBorder.none,
                            isDense: true,
                          ),
                          style: const TextStyle(
                            color: Color(0xFF1F2937),
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
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(14),
          child: Column(
            children: [
              ..._dosenBurden.map((dosen) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 14),
                  child: GestureDetector(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => KaprodiDosenDetail(
                            dosenName: dosen['name'],
                            students: List<Map<String, dynamic>>.from(
                                dosen['students']),
                          ),
                        ),
                      );
                    },
                    child: _buildDosenCard(dosen),
                  ),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDosenCard(Map<String, dynamic> dosen) {
    final bgColor = _parseColor(dosen['bgColor'] as String);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Avatar
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: bgColor.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Center(
              child: Text(
                dosen['initials'] as String,
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  color: bgColor,
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          // Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  dosen['name'] as String,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1A2050),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'NIP: ${dosen['nip']} · ${dosen['specialization']}',
                  style: const TextStyle(
                    fontSize: 9,
                    color: Color(0xFF8A9BC0),
                    fontWeight: FontWeight.w500,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      '${dosen['count']}/${dosen['maxLoad']} Mahasiswa',
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF8A9BC0),
                      ),
                    ),
                    SizedBox(
                      width: 100,
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: LinearProgressIndicator(
                          value: (dosen['count'] as int) /
                              (dosen['maxLoad'] as int),
                          minHeight: 6,
                          backgroundColor: const Color(0xFFD0D6EB),
                          valueColor: AlwaysStoppedAnimation<Color>(bgColor),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 16),
          // Count Box
          Column(
            children: [
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: Text(
                    '${dosen['count']}',
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Mahasiswa',
                style: TextStyle(
                  fontSize: 9,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF8A9BC0),
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
