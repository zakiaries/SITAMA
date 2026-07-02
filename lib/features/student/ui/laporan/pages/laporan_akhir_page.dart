import 'package:dio/dio.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/service_locator.dart';

class LaporanAkhirPage extends StatefulWidget {
  const LaporanAkhirPage({super.key});

  @override
  State<LaporanAkhirPage> createState() => _LaporanAkhirPageState();
}

class _LaporanAkhirPageState extends State<LaporanAkhirPage> {
  Map<String, dynamic>? _report;
  bool _loading = true;
  bool _uploading = false;
  String? _error;
  PlatformFile? _selectedFile;
  final _titleController = TextEditingController();

  static const _primary = Color(0xFF1E3A8A);

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _titleController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final res = await sl<DioClient>().get(
        ApiUrls.studentLaporan,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      setState(() { _report = res.data['data']; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'doc', 'docx'],
      withData: true,
    );
    if (result != null) {
      setState(() => _selectedFile = result.files.first);
    }
  }

  Future<void> _upload() async {
    if (_selectedFile == null || _selectedFile!.bytes == null) {
      _showSnack('Pilih file terlebih dahulu', isError: true);
      return;
    }
    setState(() => _uploading = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      final formData = FormData.fromMap({
        'file': MultipartFile.fromBytes(
          _selectedFile!.bytes!,
          filename: _selectedFile!.name,
        ),
        if (_titleController.text.trim().isNotEmpty)
          'title': _titleController.text.trim(),
      });

      await sl<DioClient>().post(
        ApiUrls.studentLaporan,
        data: formData,
        options: Options(headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'multipart/form-data',
        }),
      );

      _showSnack('Laporan berhasil diunggah');
      setState(() { _selectedFile = null; _titleController.clear(); });
      await _load();
    } catch (e) {
      _showSnack('Gagal mengunggah: $e', isError: true);
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  void _showSnack(String msg, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: isError ? Colors.red[700] : Colors.green[700],
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FF),
      appBar: AppBar(
        backgroundColor: _primary,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text('Laporan Akhir', style: TextStyle(fontWeight: FontWeight.w700)),
        actions: [IconButton(icon: const Icon(Icons.refresh), onPressed: _load)],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildError()
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

  Widget _buildContent() {
    final report = _report;
    final bool canUpload = report == null || report['status'] == 'rejected';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Status laporan jika ada
          if (report != null) ...[
            _buildReportCard(report),
            const SizedBox(height: 16),
          ],

          // Form upload
          if (canUpload) _buildUploadForm(report),
        ],
      ),
    );
  }

  Widget _buildReportCard(Map<String, dynamic> report) {
    final status = report['status'] as String? ?? 'pending';
    final statusConfig = _statusConfig(status);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(13), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(report['title'] ?? 'Laporan Akhir Magang',
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
                    const SizedBox(height: 2),
                    Text('Diunggah ${report['uploaded_at'] ?? '-'}',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                decoration: BoxDecoration(
                  color: statusConfig['bg'] as Color,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(statusConfig['label'] as String,
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600,
                      color: statusConfig['color'] as Color)),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // File link
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: _primary.withAlpha(15),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                Icon(Icons.picture_as_pdf, size: 16, color: _primary),
                const SizedBox(width: 8),
                const Text('Lihat File Laporan',
                  style: TextStyle(fontSize: 13, color: _primary, fontWeight: FontWeight.w600)),
              ],
            ),
          ),

          // Catatan dosen
          if (report['lecturer_note'] != null) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: status == 'rejected'
                    ? const Color(0xFFFFEBEB) : const Color(0xFFF0FDF4),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: status == 'rejected'
                      ? const Color(0xFFFCA5A5) : const Color(0xFFBBF7D0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Catatan Dosen',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF374151))),
                  const SizedBox(height: 4),
                  Text(report['lecturer_note'],
                    style: const TextStyle(fontSize: 13, color: Color(0xFF374151))),
                ],
              ),
            ),
          ],

          if (status == 'approved') ...[
            const SizedBox(height: 12),
            const Row(
              children: [
                Icon(Icons.check_circle, size: 16, color: Color(0xFF16A34A)),
                SizedBox(width: 6),
                Text('Laporan akhir Anda telah disetujui dosen pembimbing.',
                  style: TextStyle(fontSize: 13, color: Color(0xFF16A34A), fontWeight: FontWeight.w600)),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildUploadForm(Map<String, dynamic>? existing) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(13), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(existing != null ? 'Unggah Ulang Laporan (Revisi)' : 'Unggah Laporan Akhir',
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
          const SizedBox(height: 4),
          const Text('Format PDF atau Word (.doc/.docx), maksimal 10 MB.',
            style: TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
          const SizedBox(height: 16),

          // Judul
          TextField(
            controller: _titleController,
            decoration: InputDecoration(
              labelText: 'Judul Laporan (opsional)',
              hintText: 'Laporan Akhir Magang',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: const BorderSide(color: _primary, width: 2),
              ),
            ),
          ),
          const SizedBox(height: 12),

          // File picker
          GestureDetector(
            onTap: _pickFile,
            child: Container(
              height: 54,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                border: Border.all(
                  color: _selectedFile != null ? _primary : Colors.grey[400]!,
                  width: _selectedFile != null ? 2 : 1,
                ),
                borderRadius: BorderRadius.circular(10),
                color: _selectedFile != null ? _primary.withAlpha(10) : null,
              ),
              child: Row(
                children: [
                  Icon(Icons.upload_file,
                    color: _selectedFile != null ? _primary : Colors.grey[500]),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      _selectedFile != null ? _selectedFile!.name : 'Pilih file laporan...',
                      style: TextStyle(
                        color: _selectedFile != null ? _primary : Colors.grey[500],
                        fontSize: 13,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  if (_selectedFile != null)
                    GestureDetector(
                      onTap: () => setState(() => _selectedFile = null),
                      child: const Icon(Icons.close, size: 18, color: Colors.red),
                    ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _uploading ? null : _upload,
              style: ElevatedButton.styleFrom(
                backgroundColor: _primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: _uploading
                  ? const SizedBox(height: 20, width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(existing != null ? 'Kirim Ulang' : 'Unggah Laporan',
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            ),
          ),
        ],
      ),
    );
  }

  Map<String, dynamic> _statusConfig(String status) {
    switch (status) {
      case 'approved':
        return {'bg': const Color(0xFFDCFCE7), 'color': const Color(0xFF16A34A), 'label': 'Disetujui'};
      case 'rejected':
        return {'bg': const Color(0xFFFFEBEB), 'color': const Color(0xFFC41E3A), 'label': 'Perlu Revisi'};
      default:
        return {'bg': const Color(0xFFFFF8E1), 'color': const Color(0xFFF59E0B), 'label': 'Menunggu Persetujuan'};
    }
  }
}
