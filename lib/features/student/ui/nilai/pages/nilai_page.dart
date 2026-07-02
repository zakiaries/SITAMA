import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/service_locator.dart';

class NilaiPage extends StatefulWidget {
  const NilaiPage({super.key});

  @override
  State<NilaiPage> createState() => _NilaiPageState();
}

class _NilaiPageState extends State<NilaiPage> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  String? _error;

  static const _primary = Color(0xFF1E3A8A);

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final res = await sl<DioClient>().get(
        ApiUrls.studentNilai,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      setState(() { _data = res.data['data']; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FF),
      appBar: AppBar(
        backgroundColor: _primary,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text('Nilai', style: TextStyle(fontWeight: FontWeight.w700)),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _load),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildError()
              : _data == null
                  ? _buildEmpty()
                  : _buildContent(),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 48, color: Colors.grey),
          const SizedBox(height: 12),
          Text('Gagal memuat data', style: TextStyle(color: Colors.grey[600])),
          const SizedBox(height: 16),
          ElevatedButton(onPressed: _load, child: const Text('Coba Lagi')),
        ],
      ),
    );
  }

  Widget _buildEmpty() {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.star_border_rounded, size: 56, color: Colors.grey),
            SizedBox(height: 16),
            Text('Belum ada data magang.',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
            SizedBox(height: 8),
            Text('Nilai akan tampil di sini setelah Anda memiliki magang aktif.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Color(0xFF6B7280), height: 1.6)),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    final overall = _data!['overall'];
    final internship = _data!['internship'] as Map<String, dynamic>?;
    final items = _data!['items'] as List<dynamic>? ?? [];

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Rata-rata keseluruhan
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 24),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF1E3A8A), Color(0xFF2D5AA8)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                children: [
                  const Text('Rata-rata Nilai Akhir',
                    style: TextStyle(fontSize: 12, color: Colors.white70,
                        letterSpacing: 0.4, fontWeight: FontWeight.w500)),
                  const SizedBox(height: 8),
                  Text(
                    overall != null ? overall.toString() : '-',
                    style: const TextStyle(fontSize: 48, fontWeight: FontWeight.w800, color: Colors.white),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    overall == null
                        ? 'Belum ada nilai dari pembimbing'
                        : 'Gabungan penilaian dosen pembimbing kampus & industri',
                    style: const TextStyle(fontSize: 12, color: Colors.white70),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Info magang singkat
            if (internship != null)
              Container(
                width: double.infinity,
                margin: const EdgeInsets.only(bottom: 16),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  boxShadow: [BoxShadow(color: Colors.black.withAlpha(13), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 40, height: 40,
                      decoration: BoxDecoration(
                        color: _primary.withAlpha(20),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.business, color: _primary, size: 20),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(internship['company'] ?? '-',
                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
                          const SizedBox(height: 2),
                          Text(internship['is_finished'] == true ? 'Status: Selesai' : 'Status: Aktif',
                            style: TextStyle(fontSize: 12, color: internship['is_finished'] == true
                                ? Colors.green[700] : _primary)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

            // Rincian per komponen
            if (items.isEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Center(
                  child: Text('Belum ada nilai per komponen',
                    style: TextStyle(color: Color(0xFF6B7280), fontSize: 13)),
                ),
              )
            else ...[
              const Text('Rincian Nilai per Komponen',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
              const SizedBox(height: 12),
              ...items.map((component) => _buildComponentCard(component)),
            ],

            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildComponentCard(Map<String, dynamic> component) {
    final subItems = (component['items'] as List<dynamic>? ?? []);
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(13), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
            child: Row(
              children: [
                Container(width: 4, height: 16,
                  decoration: BoxDecoration(color: _primary, borderRadius: BorderRadius.circular(2))),
                const SizedBox(width: 8),
                Text(component['component'] ?? '',
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
              ],
            ),
          ),
          ...subItems.asMap().entries.map((entry) {
            final isLast = entry.key == subItems.length - 1;
            final sub = entry.value as Map<String, dynamic>;
            final avg = sub['avg'];
            return Column(
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(sub['name'] ?? '',
                          style: const TextStyle(fontSize: 13, color: Color(0xFF374151))),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                        decoration: BoxDecoration(
                          color: avg != null ? _primary.withAlpha(20) : Colors.grey[100],
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          avg != null ? avg.toString() : 'Belum dinilai',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: avg != null ? _primary : Colors.grey[500],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                if (!isLast) Divider(height: 1, color: Colors.grey[100]),
              ],
            );
          }),
        ],
      ),
    );
  }
}
