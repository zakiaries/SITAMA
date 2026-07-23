import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../services/file_helper.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

class AjukanMagangScreen extends StatefulWidget {
  /// Pre-fill opsional saat dibuka dari halaman detail Lowongan.
  final int? prefillCompanyId;
  final String? prefillPosition;
  final String? prefillBidang;
  const AjukanMagangScreen({
    super.key,
    this.prefillCompanyId,
    this.prefillPosition,
    this.prefillBidang,
  });
  @override
  State<AjukanMagangScreen> createState() => _AjukanMagangScreenState();
}

class _AjukanMagangScreenState extends State<AjukanMagangScreen> {
  late Future<Map<String, dynamic>> _future;
  bool _prefillOpened = false;
  String get _token => context.read<AuthProvider>().token ?? '';

  bool get _hasPrefill =>
      widget.prefillCompanyId != null ||
      (widget.prefillPosition ?? '').isNotEmpty ||
      (widget.prefillBidang ?? '').isNotEmpty;

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

  Future<void> _openForm(
    List<Map<String, dynamic>> companies,
    List<String> bidangOptions, {
    int? companyId,
    String? position,
    String? bidang,
  }) async {
    final ok = await Navigator.push<bool>(context, MaterialPageRoute(
      builder: (_) => _AjukanForm(
        token: _token,
        companies: companies,
        bidangOptions: bidangOptions,
        initialCompanyId: companyId,
        initialPosition: position,
        initialBidang: bidang,
      ),
    ));
    if (ok == true) _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Ajukan Magang', subtitle: 'Pengajuan tempat magang'),
        Expanded(
          child: RefreshIndicator(
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
                final bidangOptions = List<String>.from((d['bidang_options'] ?? []).map((e) => '$e'));
                final blocked = d['has_active_internship'] == true || d['has_pending'] == true;

                // Buka form otomatis dengan data terisi jika datang dari detail lowongan.
                if (_hasPrefill && !_prefillOpened && !blocked) {
                  _prefillOpened = true;
                  WidgetsBinding.instance.addPostFrameCallback((_) {
                    if (!mounted) return;
                    _openForm(
                      companies,
                      bidangOptions,
                      companyId: widget.prefillCompanyId,
                      position: widget.prefillPosition,
                      bidang: widget.prefillBidang,
                    );
                  });
                }

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
                        onPressed: () => _openForm(companies, bidangOptions),
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
                            Text(_reqSubtitle(r), style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                            if ((r['rejection_reason'] ?? '').toString().isNotEmpty)
                              Padding(padding: const EdgeInsets.only(top: 6), child: Text('Ditolak: ${r['rejection_reason']}', style: const TextStyle(color: AppColors.error, fontSize: 12))),
                          ]))),
                  ],
                );
              },
            ),
          ),
        ),
      ]),
    );
  }

  String _reqSubtitle(Map<String, dynamic> r) {
    final pos = '${r['position'] ?? ''}'.isNotEmpty ? '${r['position']}' : '${r['bidang'] ?? '-'}';
    return '$pos · mulai ${r['start_date'] ?? '-'}';
  }
}

class _AjukanForm extends StatefulWidget {
  final String token;
  final List<Map<String, dynamic>> companies;
  final List<String> bidangOptions;
  final int? initialCompanyId;
  final String? initialPosition;
  final String? initialBidang;
  const _AjukanForm({
    required this.token,
    required this.companies,
    required this.bidangOptions,
    this.initialCompanyId,
    this.initialPosition,
    this.initialBidang,
  });
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
  String? _bidang;
  DateTime? _startDate;
  String? _proofPath;
  bool _saving = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    // Pre-fill hanya jika company-nya benar-benar ada di daftar.
    if (widget.initialCompanyId != null &&
        widget.companies.any((c) => c['id'] == widget.initialCompanyId)) {
      _companyId = widget.initialCompanyId;
    }
    if ((widget.initialPosition ?? '').isNotEmpty) _position.text = widget.initialPosition!;
    if ((widget.initialBidang ?? '').isNotEmpty && widget.bidangOptions.contains(widget.initialBidang)) {
      _bidang = widget.initialBidang;
    }
  }

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
      if ((_bidang ?? '').isNotEmpty) 'bidang': _bidang!,
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
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Form Ajukan Magang'),
        Expanded(
          child: ListView(
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
              DropdownButtonFormField<String?>(
                initialValue: _bidang,
                isExpanded: true,
                decoration: const InputDecoration(labelText: 'Bidang'),
                hint: const Text('— Pilih bidang —'),
                items: [
                  const DropdownMenuItem<String?>(value: null, child: Text('— Pilih bidang —')),
                  ...widget.bidangOptions.map((b) => DropdownMenuItem<String?>(
                        value: b, child: Text(b, overflow: TextOverflow.ellipsis))),
                ],
                onChanged: (v) => setState(() => _bidang = v),
              ),
              const SizedBox(height: 4),
              const Text('Membantu perusahaanmu muncul di pencarian & rekomendasi untuk adik tingkat.',
                  style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
              const SizedBox(height: 12),
              TextField(controller: _position, decoration: const InputDecoration(labelText: 'Posisi (spesifik)')),
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
        ),
      ]),
    );
  }
}
