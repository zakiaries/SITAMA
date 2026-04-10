import 'package:flutter/material.dart';

class EditProfilPerusahaan extends StatefulWidget {
  const EditProfilPerusahaan({Key? key}) : super(key: key);

  @override
  State<EditProfilPerusahaan> createState() => _EditProfilPerusahaanState();
}

class _EditProfilPerusahaanState extends State<EditProfilPerusahaan> {
  late TextEditingController _namaPerusahaanCtrl;
  late TextEditingController _divisiCtrl;
  late TextEditingController _kotaCtrl;
  late TextEditingController _namaPicCtrl;
  late TextEditingController _emailPicCtrl;
  late TextEditingController _teleponPicCtrl;
  late TextEditingController _deskripsiCtrl;

  @override
  void initState() {
    super.initState();
    _namaPerusahaanCtrl = TextEditingController(text: 'PT. Telkom Indonesia');
    _divisiCtrl = TextEditingController(text: 'Divisi IT Infrastructure');
    _kotaCtrl = TextEditingController(text: 'Semarang');
    _namaPicCtrl = TextEditingController(text: 'Budi Santoso, S.T.');
    _emailPicCtrl = TextEditingController(text: 'budi@telkom.co.id');
    _teleponPicCtrl = TextEditingController(text: '');
    _deskripsiCtrl = TextEditingController(text: '');
  }

  @override
  void dispose() {
    _namaPerusahaanCtrl.dispose();
    _divisiCtrl.dispose();
    _kotaCtrl.dispose();
    _namaPicCtrl.dispose();
    _emailPicCtrl.dispose();
    _teleponPicCtrl.dispose();
    _deskripsiCtrl.dispose();
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
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
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
                              color: Colors.white,
                              size: 24,
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Text(
                            'Edit Profil Perusahaan',
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
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Avatar
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
              const SizedBox(height: 24),
              // Form Fields
              _buildFormField('Nama Perusahaan', _namaPerusahaanCtrl),
              const SizedBox(height: 16),
              _buildFormField('Divisi', _divisiCtrl),
              const SizedBox(height: 16),
              _buildFormField('Kota', _kotaCtrl),
              const SizedBox(height: 16),
              _buildFormField('Nama PIC', _namaPicCtrl),
              const SizedBox(height: 16),
              _buildFormField('Email PIC', _emailPicCtrl),
              const SizedBox(height: 16),
              _buildFormField('No. Telepon PIC', _teleponPicCtrl),
              const SizedBox(height: 16),
              _buildFormField(
                'Deskripsi Perusahaan',
                _deskripsiCtrl,
                maxLines: 3,
              ),
              const SizedBox(height: 24),
              // Tombol Simpan
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Perubahan disimpan'),
                        duration: Duration(seconds: 2),
                      ),
                    );
                    Navigator.pop(context);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF085041),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: const Text(
                    'Simpan Perubahan',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFormField(
    String label,
    TextEditingController controller, {
    int maxLines = 1,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: Color(0xFFB0BDD4),
          ),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          maxLines: maxLines,
          decoration: InputDecoration(
            filled: true,
            fillColor: Colors.white,
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
                color: Color(0xFF085041),
                width: 1,
              ),
            ),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 12,
              vertical: 12,
            ),
          ),
        ),
      ],
    );
  }
}
