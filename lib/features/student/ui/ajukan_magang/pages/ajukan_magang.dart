import 'package:dio/dio.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sitama/core/constants/api_urls.dart';
import 'package:sitama/core/network/dio_client.dart';
import 'package:sitama/service_locator.dart';

class AjukanMagangPage extends StatefulWidget {
  const AjukanMagangPage({super.key});

  @override
  State<AjukanMagangPage> createState() => _AjukanMagangPageState();
}

class _AjukanMagangPageState extends State<AjukanMagangPage> {
  bool _loading = true;
  bool _uploading = false;
  bool _hasActiveInternship = false;
  bool _hasPending = false;
  List<dynamic> _applications = [];

  PlatformFile? _proofFile;
  final _companyController  = TextEditingController();
  final _picNameController  = TextEditingController();
  final _picPhoneController = TextEditingController();
  final _picEmailController = TextEditingController();
  final _positionController = TextEditingController();
  DateTime? _startDate;

  static const _primary = Color(0xFF1E3A8A);

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _companyController.dispose();
    _picNameController.dispose();
    _picPhoneController.dispose();
    _picEmailController.dispose();
    _positionController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final res = await sl<DioClient>().get(
        ApiUrls.studentAjukanMagang,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      setState(() {
        _hasActiveInternship = res.data['has_active_internship'] == true;
        _hasPending          = res.data['has_pending'] == true;
        _applications        = res.data['applications'] ?? [];
        _loading             = false;
      });
    } catch (e) {
      setState(() => _loading = false);
    }
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
      withData: true,
    );
    if (result != null) setState(() => _proofFile = result.files.first);
  }

  Future<void> _submit() async {
    if (_proofFile == null || _proofFile!.bytes == null) {
      _snack('Pilih bukti penerimaan terlebih dahulu', error: true); return;
    }
    if (_companyController.text.trim().isEmpty) {
      _snack('Nama perusahaan wajib diisi', error: true); return;
    }
    if (_picNameController.text.trim().isEmpty) {
      _snack('Nama pembimbing industri wajib diisi', error: true); return;
    }
    if (_startDate == null) {
      _snack('Tanggal mulai wajib diisi', error: true); return;
    }

    setState(() => _uploading = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      final formData = FormData.fromMap({
        'proof_file':   MultipartFile.fromBytes(_proofFile!.bytes!, filename: _proofFile!.name),
        'company_name': _companyController.text.trim(),
        'pic_name':     _picNameController.text.trim(),
        if (_picPhoneController.text.trim().isNotEmpty)
          'pic_phone':  _picPhoneController.text.trim(),
        if (_picEmailController.text.trim().isNotEmpty)
          'pic_email':  _picEmailController.text.trim(),
        if (_positionController.text.trim().isNotEmpty)
          'position':   _positionController.text.trim(),
        'start_date':   DateFormat('yyyy-MM-dd').format(_startDate!),
      });

      await sl<DioClient>().post(
        ApiUrls.studentAjukanMagang,
        data: formData,
        options: Options(headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'multipart/form-data',
        }),
      );

      _snack('Pengajuan berhasil dikirim');
      _companyController.clear();
      _picNameController.clear();
      _picPhoneController.clear();
      _picEmailController.clear();
      _positionController.clear();
      setState(() { _proofFile = null; _startDate = null; });
      await _load();
    } catch (e) {
      _snack('Gagal mengirim: $e', error: true);
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  void _snack(String msg, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: error ? Colors.red[700] : Colors.green[700],
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
        title: const Text('Ajukan Magang', style: TextStyle(fontWeight: FontWeight.w700)),
        actions: [IconButton(icon: const Icon(Icons.refresh), onPressed: _load)],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (_hasActiveInternship) _buildActiveBlocker(),
                  if (!_hasActiveInternship && _hasPending) _buildPendingBlocker(),
                  if (!_hasActiveInternship && !_hasPending) _buildForm(),
                  if (_applications.isNotEmpty) ...[
                    const SizedBox(height: 24),
                    const Text('Riwayat Pengajuan',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
                    const SizedBox(height: 12),
                    ..._applications.map(_buildApplicationCard),
                  ],
                  const SizedBox(height: 24),
                ],
              ),
            ),
    );
  }

  Widget _buildActiveBlocker() {
    return _infoCard(
      borderColor: const Color(0xFF16A34A),
      icon: Icons.check_circle_outline,
      iconColor: const Color(0xFF16A34A),
      title: 'Kamu sudah memiliki magang aktif',
      subtitle: 'Pengajuan magang baru tidak diperlukan. Pantau status magangmu di halaman Magang Saya.',
      titleColor: const Color(0xFF16A34A),
    );
  }

  Widget _buildPendingBlocker() {
    return _infoCard(
      borderColor: const Color(0xFFF59E0B),
      icon: Icons.hourglass_top_rounded,
      iconColor: const Color(0xFFF59E0B),
      title: 'Pengajuan sedang diproses',
      subtitle: 'Kaprodi sedang mereview pengajuanmu. Kamu tidak bisa mengajukan lagi sampai pengajuan sebelumnya selesai diproses.',
      titleColor: const Color(0xFFF59E0B),
    );
  }

  Widget _infoCard({
    required Color borderColor,
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
    required Color titleColor,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border(left: BorderSide(color: borderColor, width: 4)),
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(10), blurRadius: 6)],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: iconColor, size: 22),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: TextStyle(fontWeight: FontWeight.w700, color: titleColor, fontSize: 14)),
                const SizedBox(height: 4),
                Text(subtitle, style: const TextStyle(fontSize: 13, color: Color(0xFF6B7280), height: 1.5)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildForm() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(13), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Ajukan Magang',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
          const SizedBox(height: 4),
          const Text('Isi data magang dan upload bukti penerimaan. Kaprodi akan mereview dan menghubungkan ke sistem.',
            style: TextStyle(fontSize: 12, color: Color(0xFF6B7280), height: 1.5)),
          const SizedBox(height: 16),

          // Bukti penerimaan
          _label('Bukti Penerimaan Magang', required: true),
          const SizedBox(height: 6),
          GestureDetector(
            onTap: _pickFile,
            child: Container(
              height: 54,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                border: Border.all(
                  color: _proofFile != null ? _primary : Colors.grey[400]!,
                  width: _proofFile != null ? 2 : 1,
                ),
                borderRadius: BorderRadius.circular(10),
                color: _proofFile != null ? _primary.withAlpha(10) : null,
              ),
              child: Row(children: [
                Icon(Icons.upload_file,
                  color: _proofFile != null ? _primary : Colors.grey[500]),
                const SizedBox(width: 10),
                Expanded(child: Text(
                  _proofFile != null ? _proofFile!.name : 'Pilih file (PDF/JPG/PNG, maks 10 MB)',
                  style: TextStyle(
                    color: _proofFile != null ? _primary : Colors.grey[500], fontSize: 13),
                  overflow: TextOverflow.ellipsis,
                )),
                if (_proofFile != null)
                  GestureDetector(
                    onTap: () => setState(() => _proofFile = null),
                    child: const Icon(Icons.close, size: 18, color: Colors.red),
                  ),
              ]),
            ),
          ),
          const SizedBox(height: 14),

          // Perusahaan
          _label('Nama Perusahaan', required: true),
          const SizedBox(height: 6),
          _textField(_companyController, hint: 'Contoh: PT. Maju Bersama'),
          const SizedBox(height: 14),

          // Data pembimbing
          _label('Data Pembimbing Industri', required: true),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFF9FAFB),
              border: Border.all(color: Colors.grey[200]!),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Column(children: [
              _textField(_picNameController, hint: 'Nama lengkap pembimbing *'),
              const SizedBox(height: 8),
              _textField(_picPhoneController, hint: 'No. HP / WhatsApp (opsional)',
                keyboardType: TextInputType.phone),
              const SizedBox(height: 8),
              _textField(_picEmailController, hint: 'Email (opsional)',
                keyboardType: TextInputType.emailAddress),
            ]),
          ),
          const SizedBox(height: 14),

          // Posisi
          _label('Posisi / Bidang'),
          const SizedBox(height: 6),
          _textField(_positionController, hint: 'Contoh: Backend Developer'),
          const SizedBox(height: 14),

          // Tanggal mulai
          _label('Tanggal Mulai', required: true),
          const SizedBox(height: 6),
          GestureDetector(
            onTap: () async {
              final picked = await showDatePicker(
                context: context,
                initialDate: _startDate ?? DateTime.now(),
                firstDate: DateTime(2020),
                lastDate: DateTime(2030),
              );
              if (picked != null) setState(() => _startDate = picked);
            },
            child: Container(
              height: 54,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey[400]!),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(children: [
                Icon(Icons.calendar_today, color: _primary, size: 18),
                const SizedBox(width: 10),
                Text(
                  _startDate != null
                      ? DateFormat('dd MMMM yyyy').format(_startDate!)
                      : 'Pilih tanggal mulai',
                  style: TextStyle(
                    fontSize: 13,
                    color: _startDate != null ? const Color(0xFF1A1A3E) : Colors.grey[500],
                  ),
                ),
              ]),
            ),
          ),
          const SizedBox(height: 20),

          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _uploading ? null : _submit,
              style: ElevatedButton.styleFrom(
                backgroundColor: _primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: _uploading
                  ? const SizedBox(height: 20, width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Kirim Pengajuan',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildApplicationCard(dynamic app) {
    final status = app['status'] as String? ?? 'pending';
    final badge = _badgeConfig(status);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
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
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(app['company_name'] ?? '-',
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF1A1A3E))),
                    const SizedBox(height: 2),
                    Text('${app['position'] ?? '-'} · Mulai ${_formatDate(app['start_date'])}',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: (badge['bg'] as Color),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(badge['label'] as String,
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
                      color: badge['color'] as Color)),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text('Pembimbing: ${app['pic_name'] ?? '-'}${app['pic_phone'] != null ? ' · ${app['pic_phone']}' : ''}',
            style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
          if (status == 'rejected' && app['rejection_reason'] != null) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFFFEBEB),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFFCA5A5)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.info_outline, size: 14, color: Color(0xFFC41E3A)),
                  const SizedBox(width: 6),
                  Expanded(child: Text('Alasan: ${app['rejection_reason']}',
                    style: const TextStyle(fontSize: 12, color: Color(0xFFC41E3A)))),
                ],
              ),
            ),
          ],
          const SizedBox(height: 6),
          Text('Diajukan ${app['created_at'] ?? '-'}',
            style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
        ],
      ),
    );
  }

  Widget _label(String text, {bool required = false}) {
    return Row(
      children: [
        Text(text, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF374151))),
        if (required) const Text(' *', style: TextStyle(color: Colors.red, fontSize: 13)),
      ],
    );
  }

  Widget _textField(TextEditingController ctrl, {String? hint,
    TextInputType keyboardType = TextInputType.text}) {
    return TextField(
      controller: ctrl,
      keyboardType: keyboardType,
      style: const TextStyle(fontSize: 13),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: TextStyle(color: Colors.grey[400], fontSize: 13),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: Colors.grey[300]!)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: Colors.grey[300]!)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: _primary, width: 2)),
        filled: true, fillColor: Colors.white,
      ),
    );
  }

  String _formatDate(String? raw) {
    if (raw == null) return '-';
    try { return DateFormat('dd MMM yyyy').format(DateTime.parse(raw)); }
    catch (_) { return raw; }
  }

  Map<String, dynamic> _badgeConfig(String status) {
    switch (status) {
      case 'approved':
        return {'bg': const Color(0xFFDCFCE7), 'color': const Color(0xFF16A34A), 'label': 'Disetujui'};
      case 'rejected':
        return {'bg': const Color(0xFFFFEBEB), 'color': const Color(0xFFC41E3A), 'label': 'Ditolak'};
      default:
        return {'bg': const Color(0xFFFFF8E1), 'color': const Color(0xFFF59E0B), 'label': 'Menunggu'};
    }
  }
}
