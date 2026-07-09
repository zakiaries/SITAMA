import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/file_helper.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class AjukanMagangScreen extends StatefulWidget {
  const AjukanMagangScreen({super.key});
  @override
  State<AjukanMagangScreen> createState() => _AjukanMagangScreenState();
}

class _AjukanMagangScreenState extends State<AjukanMagangScreen> {
  late Future<Map<String, dynamic>> _future;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('/mahasiswa/ajukan-magang', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() => _future = _load());

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ajukan Magang')),
      body: RefreshIndicator(
        onRefresh: () async => _reload(),
        child: FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snap.hasError) {
              return ErrorRetry(message: '${snap.error}', onRetry: _reload);
            }
            final d = snap.data!;
            final requests = List<Map<String, dynamic>>.from(d['requests'] ?? []);
            final companies = List<Map<String, dynamic>>.from(d['companies'] ?? []);
            final blocked = d['has_active_internship'] == true || d['has_pending'] == true;

            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                if (blocked)
                  AppCard(child: Text(
                    d['has_active_internship'] == true
                        ? 'Kamu sudah punya magang aktif.'
                        : 'Ada pengajuan yang sedang menunggu review Kaprodi.',
                    style: const TextStyle(color: AppColors.warnText),
                  ))
                else
                  ElevatedButton.icon(
                    onPressed: () async {
                      final ok = await Navigator.push<bool>(context, MaterialPageRoute(
                        builder: (_) => _AjukanForm(token: _token, companies: companies),
                      ));
                      if (ok == true) _reload();
                    },
                    icon: const Icon(Icons.add),
                    label: const Text('Ajukan Magang Baru'),
                  ),
                const SectionTitle('Pengajuan Saya'),
                if (requests.isEmpty)
                  const AppCard(child: Text('Belum ada pengajuan.', style: TextStyle(color: AppColors.textMuted)))
                else
                  ...requests.map((r) => AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Row(children: [
                          Expanded(child: Text(r['company_name'] ?? '-', style: const TextStyle(fontWeight: FontWeight.w700))),
                          StatusChip(r['status'] ?? ''),
                        ]),
                        const SizedBox(height: 2),
                        Text('${r['position'] ?? '-'} · mulai ${r['start_date'] ?? '-'}', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                        if ((r['rejection_reason'] ?? '').toString().isNotEmpty)
                          Padding(padding: const EdgeInsets.only(top: 6), child: Text('Ditolak: ${r['rejection_reason']}', style: const TextStyle(color: AppColors.error, fontSize: 12))),
                      ]))),
              ],
            );
          },
        ),
      ),
    );
  }
}

class _AjukanForm extends StatefulWidget {
  final String token;
  final List<Map<String, dynamic>> companies;
  const _AjukanForm({required this.token, required this.companies});
  @override
  State<_AjukanForm> createState() => _AjukanFormState();
}

class _AjukanFormState extends State<_AjukanForm> {
  int? _companyId; // null = perusahaan baru
  final _companyName = TextEditingController();
  final _picName = TextEditingController();
  final _picEmail = TextEditingController();
  final _picPhone = TextEditingController();
  final _position = TextEditingController();
  DateTime? _startDate;
  String? _proofPath;
  bool _saving = false;
  String? _error;

  @override
  void dispose() {
    _companyName.dispose(); _picName.dispose(); _picEmail.dispose();
    _picPhone.dispose(); _position.dispose();
    super.dispose();
  }

  String? get _startStr => _startDate == null ? null
      : '${_startDate!.year}-${_startDate!.month.toString().padLeft(2, '0')}-${_startDate!.day.toString().padLeft(2, '0')}';

  Future<void> _save() async {
    if (_companyId == null && _companyName.text.trim().isEmpty) { setState(() => _error = 'Pilih perusahaan atau isi nama perusahaan baru.'); return; }
    if (_picName.text.trim().isEmpty) { setState(() => _error = 'Nama pembimbing industri wajib diisi.'); return; }
    if (_startStr == null) { setState(() => _error = 'Tanggal mulai wajib diisi.'); return; }
    if (_proofPath == null) { setState(() => _error = 'Bukti penerimaan magang wajib diunggah.'); return; }

    setState(() { _saving = true; _error = null; });
    final fields = <String, String>{
      'pic_name': _picName.text.trim(),
      'start_date': _startStr!,
      if (_companyId != null) 'company_id': '$_companyId' else 'company_name': _companyName.text.trim(),
      if (_picEmail.text.trim().isNotEmpty) 'pic_email': _picEmail.text.trim(),
      if (_picPhone.text.trim().isNotEmpty) 'pic_phone': _picPhone.text.trim(),
      if (_position.text.trim().isNotEmpty) 'position': _position.text.trim(),
    };
    try {
      await ApiClient.upload('/mahasiswa/ajukan-magang', fileField: 'proof_file', filePath: _proofPath!, fields: fields, token: widget.token);
      if (mounted) { showMessage(context, 'Pengajuan magang dikirim.'); Navigator.pop(context, true); }
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Form Ajukan Magang')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (_error != null) Padding(padding: const EdgeInsets.only(bottom: 12), child: Text(_error!, style: const TextStyle(color: AppColors.error))),
          DropdownButtonFormField<int?>(
            initialValue: _companyId,
            decoration: const InputDecoration(labelText: 'Perusahaan'),
            items: [
              const DropdownMenuItem<int?>(value: null, child: Text('— Perusahaan baru —')),
              ...widget.companies.map((c) => DropdownMenuItem<int?>(value: c['id'] as int, child: Text(c['name'] ?? ''))),
            ],
            onChanged: (v) => setState(() => _companyId = v),
          ),
          if (_companyId == null) ...[
            const SizedBox(height: 12),
            TextField(controller: _companyName, decoration: const InputDecoration(labelText: 'Nama Perusahaan (baru)')),
          ],
          const SizedBox(height: 12),
          TextField(controller: _picName, decoration: const InputDecoration(labelText: 'Nama Pembimbing Industri *')),
          const SizedBox(height: 12),
          TextField(controller: _picEmail, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email Pembimbing')),
          const SizedBox(height: 12),
          TextField(controller: _picPhone, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'No. HP Pembimbing')),
          const SizedBox(height: 12),
          TextField(controller: _position, decoration: const InputDecoration(labelText: 'Posisi / Divisi')),
          const SizedBox(height: 12),
          InkWell(
            onTap: () async {
              final picked = await showDatePicker(context: context, initialDate: DateTime.now(), firstDate: DateTime(2020), lastDate: DateTime(2100));
              if (picked != null) setState(() => _startDate = picked);
            },
            child: InputDecorator(decoration: const InputDecoration(labelText: 'Tanggal Mulai *'), child: Text(_startStr ?? 'Pilih tanggal')),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () async {
              final p = await pickFilePath(extensions: ['pdf', 'jpg', 'jpeg', 'png']);
              if (p != null) setState(() => _proofPath = p);
            },
            icon: const Icon(Icons.attach_file),
            label: Text(_proofPath == null ? 'Unggah Bukti (PDF/Gambar) *' : fileName(_proofPath!)),
          ),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                : const Text('Kirim Pengajuan'),
          ),
        ],
      ),
    );
  }
}
