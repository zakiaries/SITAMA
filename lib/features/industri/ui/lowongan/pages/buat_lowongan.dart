import 'package:flutter/material.dart';

class BuatLowonganBaru extends StatefulWidget {
  const BuatLowonganBaru({super.key});

  @override
  State<BuatLowonganBaru> createState() => _BuatLowonganBaruState();
}

class _BuatLowonganBaruState extends State<BuatLowonganBaru> {
  final _formKey = GlobalKey<FormState>();

  String? _selectedJobTitle;
  String? _selectedDivisi;
  String? _selectedJumlahPosisi;
  String? _selectedTipe;
  String? _selectedDurasi;

  List<String> jobTitles = [
    'Mobile Developer Intern',
    'Frontend Developer Intern',
    'Backend Engineer Intern',
    'Data Analyst Intern',
    'UI/UX Designer Intern',
  ];

  List<String> divisi = [
    'PT. Telkom Indonesia',
    'PT. Gojek',
    'PT. Grab',
    'PT. Tokopedia',
    'PT. Indosat',
  ];

  List<String> jumlahPosisi = ['1', '2', '3', '4', '5', '10'];
  List<String> tipePekerjaan = ['On-site', 'Work From Home', 'Hybrid'];
  List<String> durasi = ['1 Bulan', '2 Bulan', '3 Bulan', '6 Bulan'];

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
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 4),
                      Row(
                        children: [
                          GestureDetector(
                            onTap: () => Navigator.pop(context),
                            child: const Icon(
                              Icons.arrow_back,
                              color: Color(0xFFE8EAF6),
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Text(
                            'Buat Lowongan Baru',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
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
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Informasi Lowongan',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1A2050),
                  ),
                ),
                const SizedBox(height: 12),
                _buildFormField(
                  'Judul Posisi',
                  DropdownButtonFormField<String>(
                    initialValue: _selectedJobTitle,
                    items: jobTitles.map((title) {
                      return DropdownMenuItem(
                        value: title,
                        child: Text(title),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() => _selectedJobTitle = value);
                    },
                    decoration: _inputDecoration('Mobile Developer Intern'),
                  ),
                ),
                const SizedBox(height: 14),
                _buildFormField(
                  'Divisi Perusahaan',
                  DropdownButtonFormField<String>(
                    initialValue: _selectedDivisi,
                    items: divisi.map((div) {
                      return DropdownMenuItem(
                        value: div,
                        child: Text(div),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() => _selectedDivisi = value);
                    },
                    decoration: _inputDecoration('Pilih Divisi'),
                  ),
                ),
                const SizedBox(height: 14),
                _buildFormField(
                  'Jumlah Posisi',
                  DropdownButtonFormField<String>(
                    initialValue: _selectedJumlahPosisi,
                    items: jumlahPosisi.map((item) {
                      return DropdownMenuItem(
                        value: item,
                        child: Text(item),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() => _selectedJumlahPosisi = value);
                    },
                    decoration: _inputDecoration('2'),
                  ),
                ),
                const SizedBox(height: 14),
                _buildFormField(
                  'Tipe Pekerjaan',
                  DropdownButtonFormField<String>(
                    initialValue: _selectedTipe,
                    items: tipePekerjaan.map((tipe) {
                      return DropdownMenuItem(
                        value: tipe,
                        child: Text(tipe),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() => _selectedTipe = value);
                    },
                    decoration: _inputDecoration('On-site'),
                  ),
                ),
                const SizedBox(height: 14),
                _buildFormField(
                  'Deskripsi & Persyaratan',
                  TextFormField(
                    maxLines: 4,
                    decoration:
                        _inputDecoration('Deskripsi singkat tentang pekerjaan'),
                    style: const TextStyle(fontSize: 12),
                  ),
                ),
                const SizedBox(height: 14),
                _buildFormField(
                  'Kategori Skill',
                  TextFormField(
                    decoration:
                        _inputDecoration('Flutter, Dart, Firebase, REST API'),
                    style: const TextStyle(fontSize: 12),
                  ),
                ),
                const SizedBox(height: 14),
                _buildFormField(
                  'Durasi Magang',
                  DropdownButtonFormField<String>(
                    initialValue: _selectedDurasi,
                    items: durasi.map((dur) {
                      return DropdownMenuItem(
                        value: dur,
                        child: Text(dur),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() => _selectedDurasi = value);
                    },
                    decoration: _inputDecoration('3 Bulan'),
                  ),
                ),
                const SizedBox(height: 14),
                _buildFormField(
                  'Kontak PIC',
                  TextFormField(
                    decoration: _inputDecoration('budi.santos@telkom.co.id'),
                    style: const TextStyle(fontSize: 12),
                  ),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () => Navigator.pop(context),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFE8F0FE),
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                        child: const Text(
                          'Batal',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1A3A8E),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () {
                          // Save and navigate to review page
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const ReviewLowongan(),
                            ),
                          );
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF388E3C),
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                        child: const Text(
                          'Lanjutkan',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildFormField(String label, Widget field) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: Color(0xFF1A2050),
          ),
        ),
        const SizedBox(height: 6),
        field,
      ],
    );
  }

  InputDecoration _inputDecoration(String hint) {
    return InputDecoration(
      hintText: hint,
      hintStyle: const TextStyle(
        fontSize: 12,
        color: Color(0xFFB0BDD4),
      ),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(
          color: Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(
          color: Color(0xFFD0D6EB),
          width: 0.5,
        ),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(
          color: Color(0xFF1A1A3E),
          width: 1,
        ),
      ),
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(
        horizontal: 12,
        vertical: 12,
      ),
    );
  }
}

class ReviewLowongan extends StatelessWidget {
  const ReviewLowongan({super.key});

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
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 4),
                      Row(
                        children: [
                          GestureDetector(
                            onTap: () => Navigator.pop(context),
                            child: const Icon(
                              Icons.arrow_back,
                              color: Color(0xFFE8EAF6),
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Text(
                            'Review Lowongan',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
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
            children: [
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: const Color(0xFFD0D6EB),
                    width: 0.5,
                  ),
                ),
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 11,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF8E1),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Text(
                        '5 pelamar menunggu keputusan penerimaan',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFFE6A400),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Mobile Developer Intern',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A2050),
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Deskripsi: Membangun dan mengembangkan aplikasi mobile cross-platform',
                      style: TextStyle(
                        fontSize: 11,
                        color: Color(0xFF8A9BC0),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 5,
                      children: ['Flutter', 'Dart', 'Firebase', 'REST API']
                          .map((skill) {
                        return Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFE8F0FE),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            skill,
                            style: const TextStyle(
                              fontSize: 10,
                              color: Color(0xFF1A3A8E),
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Lowongan berhasil dibuat!'),
                      backgroundColor: Color(0xFF388E3C),
                    ),
                  );
                  Navigator.pop(context);
                  Navigator.pop(context);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF388E3C),
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  minimumSize: const Size(double.infinity, 50),
                ),
                child: const Text(
                  'Terbitkan Lowongan',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
