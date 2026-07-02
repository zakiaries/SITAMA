import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/features/student/ui/job_listing/pages/job_listing.dart';
import 'package:sitama/service_locator.dart';

class MagangSayaPage extends StatefulWidget {
  const MagangSayaPage({super.key});

  @override
  State<MagangSayaPage> createState() => _MagangSayaPageState();
}

class _MagangSayaPageState extends State<MagangSayaPage> {
  Map<String, dynamic>? _internship;
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
        ApiUrls.studentInternship,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      setState(() {
        _internship = res.data['data'];
        _loading = false;
      });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  String _formatDate(String? raw) {
    if (raw == null) return '-';
    try {
      return DateFormat('dd MMM yyyy').format(DateTime.parse(raw));
    } catch (_) { return raw; }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FF),
      appBar: AppBar(
        backgroundColor: _primary,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text('Magang Saya', style: TextStyle(fontWeight: FontWeight.w700)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _load,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildError()
              : _internship == null
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
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80, height: 80,
              decoration: BoxDecoration(color: _primary.withAlpha(20), shape: BoxShape.circle),
              child: Icon(Icons.work_outline, size: 40, color: _primary.withAlpha(128)),
            ),
            const SizedBox(height: 24),
            const Text('Belum ada data magang',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
            const SizedBox(height: 10),
            const Text(
              'Anda belum memiliki magang aktif. Ajukan magang melalui menu Ajukan Magang.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Color(0xFF6B7280), height: 1.6),
            ),
            const SizedBox(height: 28),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                icon: const Icon(Icons.work, size: 18),
                label: const Text('Lihat Lowongan Magang'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: _primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: () => Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const JobListingPage())),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    final i = _internship!;
    final isFinished = i['is_finished'] == true;

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status badge + company card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: isFinished
                      ? [const Color(0xFF16A34A), const Color(0xFF15803D)]
                      : [_primary, const Color(0xFF2D5AA8)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.business, color: Colors.white, size: 20),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          i['company'] ?? '-',
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    i['position'] ?? '-',
                    style: TextStyle(fontSize: 14, color: Colors.white.withAlpha(204)),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white.withAlpha(40),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(isFinished ? Icons.check_circle : Icons.circle,
                            size: 12, color: Colors.white),
                        const SizedBox(width: 4),
                        Text(isFinished ? 'Selesai' : 'Aktif',
                          style: const TextStyle(fontSize: 12, color: Colors.white, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Info detail
            _buildCard([
              _buildRow('Tanggal Mulai', _formatDate(i['start_date'])),
              _buildRow('Tanggal Selesai', i['is_finished'] == true && i['end_date'] != null
                  ? _formatDate(i['end_date']) : 'Belum selesai'),
              _buildRow('Dosen Pembimbing', i['lecturer'] ?? 'Belum ditugaskan'),
              _buildRow('Pembimbing Industri', i['lecturer_industry'] ?? 'Belum ditugaskan',
                  isLast: true),
            ], title: 'Detail Magang'),

            const SizedBox(height: 16),

            // Button ajukan magang
            if (!isFinished)
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  icon: const Icon(Icons.work, size: 18),
                  label: const Text('Lihat Lowongan Magang'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: _primary,
                    side: const BorderSide(color: Color(0xFF1E3A8A)),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () => Navigator.push(context,
                    MaterialPageRoute(builder: (_) => const JobListingPage())),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildCard(List<Widget> rows, {String? title}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(13), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (title != null)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 0),
              child: Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
            ),
          ...rows,
        ],
      ),
    );
  }

  Widget _buildRow(String label, String value, {bool isLast = false}) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            children: [
              Expanded(flex: 2,
                child: Text(label, style: const TextStyle(fontSize: 13, color: Color(0xFF6B7280)))),
              Expanded(flex: 3,
                child: Text(value,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF1A1A3E)),
                  textAlign: TextAlign.end)),
            ],
          ),
        ),
        if (!isLast) Divider(height: 1, color: Colors.grey[100]),
      ],
    );
  }
}
