import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';
import 'ajukan_magang_screen.dart';

/// Daftar Lowongan / Tempat Magang (LIHAT SAJA) — menyamai web.
///
/// Hanya menampilkan lowongan dari perusahaan berafiliasi Polines. Untuk
/// mendaftar, mahasiswa memakai menu "Ajukan Magang" (alur lama tidak berubah).
class LowonganScreen extends StatefulWidget {
  const LowonganScreen({super.key});
  @override
  State<LowonganScreen> createState() => _LowonganScreenState();
}

class _LowonganScreenState extends State<LowonganScreen> {
  final _search = TextEditingController();
  List<Map<String, dynamic>> _all = [];
  List<String> _bidangOptions = [];
  List<String> _locationOptions = [];
  String _q = '';
  String? _bidang;
  String? _location;
  bool _loading = true;
  String? _error;

  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final data = await ApiClient.get('/mahasiswa/lowongan', token: _token);
      if (!mounted) return;
      setState(() {
        _all = List<Map<String, dynamic>>.from(data['listings'] ?? []);
        _bidangOptions = List<String>.from((data['bidang_options'] ?? []).map((e) => '$e'));
        _locationOptions = List<String>.from((data['location_options'] ?? []).map((e) => '$e'));
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() { _error = '$e'; _loading = false; });
    }
  }

  List<Map<String, dynamic>> get _filtered {
    final q = _q.trim().toLowerCase();
    return _all.where((l) {
      if (_bidang != null && '${l['bidang'] ?? ''}' != _bidang) return false;
      if (_location != null && '${l['location'] ?? ''}' != _location) return false;
      if (q.isEmpty) return true;
      final hay = [
        l['title'], l['company_name'], l['location'], l['division'], l['bidang'],
      ].map((e) => '${e ?? ''}'.toLowerCase()).join(' ');
      return hay.contains(q);
    }).toList();
  }

  bool get _hasFilter => _q.trim().isNotEmpty || _bidang != null || _location != null;

  void _resetFilter() {
    setState(() { _q = ''; _bidang = null; _location = null; _search.clear(); });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Lowongan Magang', subtitle: 'Perusahaan afiliasi Polines'),
        Expanded(child: _body()),
      ]),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) return ErrorRetry(message: _error!, onRetry: _load);

    final items = _filtered;
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
        children: [
          _filterCard(),
          const SizedBox(height: 14),
          if (items.isEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 30),
              child: EmptyState(
                _hasFilter ? 'Tidak ada lowongan yang cocok' : 'Belum ada lowongan',
                icon: Icons.work_outline,
                hint: _hasFilter
                    ? 'Coba ubah kata kunci atau filter.'
                    : 'Belum ada lowongan dari perusahaan afiliasi saat ini.',
              ),
            )
          else
            ...items.map(_card),
        ],
      ),
    );
  }

  Widget _filterCard() {
    return AppCard(
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const Text('Lowongan / Tempat Magang',
            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14.5)),
        const SizedBox(height: 4),
        const Text(
          'Daftar lowongan dari perusahaan berafiliasi Polines. Untuk mendaftar, gunakan menu Ajukan Magang. Perusahaan di luar daftar ini tetap bisa kamu ajukan sendiri.',
          style: TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.4),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _search,
          onChanged: (v) => setState(() => _q = v),
          decoration: InputDecoration(
            hintText: 'Cari posisi, perusahaan…',
            prefixIcon: const Icon(Icons.search, size: 20),
            isDense: true,
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
          ),
        ),
        const SizedBox(height: 10),
        Row(children: [
          Expanded(child: _dropdown(
            hint: 'Semua bidang',
            value: _bidang,
            options: _bidangOptions,
            onChanged: (v) => setState(() => _bidang = v),
          )),
          const SizedBox(width: 10),
          Expanded(child: _dropdown(
            hint: 'Semua wilayah',
            value: _location,
            options: _locationOptions,
            onChanged: (v) => setState(() => _location = v),
          )),
        ]),
        if (_hasFilter) ...[
          const SizedBox(height: 8),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton.icon(
              onPressed: _resetFilter,
              icon: const Icon(Icons.close, size: 16),
              label: const Text('Reset filter'),
              style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: const Size(0, 32)),
            ),
          ),
        ],
      ]),
    );
  }

  Widget _dropdown({
    required String hint,
    required String? value,
    required List<String> options,
    required ValueChanged<String?> onChanged,
  }) {
    return DropdownButtonFormField<String?>(
      initialValue: value,
      isExpanded: true,
      decoration: InputDecoration(
        isDense: true,
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      ),
      hint: Text(hint, style: const TextStyle(fontSize: 12.5)),
      items: [
        DropdownMenuItem<String?>(value: null, child: Text(hint, style: const TextStyle(fontSize: 12.5))),
        ...options.map((o) => DropdownMenuItem<String?>(
              value: o,
              child: Text(o, style: const TextStyle(fontSize: 12.5), overflow: TextOverflow.ellipsis),
            )),
      ],
      onChanged: onChanged,
    );
  }

  Widget _card(Map<String, dynamic> l) {
    final bidang = '${l['bidang'] ?? ''}';
    final jobType = '${l['job_type'] ?? ''}';
    final location = '${l['location'] ?? ''}';
    final division = '${l['division'] ?? ''}';
    return AppCard(
      onTap: () => Navigator.push(context, MaterialPageRoute(
        builder: (_) => LowonganDetailScreen(id: l['id'] as int),
      )),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Expanded(child: Text('${l['title'] ?? '-'}',
              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14.5, height: 1.3))),
          if (jobType.isNotEmpty) ...[
            const SizedBox(width: 8),
            _pill(jobType),
          ],
        ]),
        const SizedBox(height: 3),
        Text('${l['company_name'] ?? ''}',
            maxLines: 1, overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.text)),
        if (bidang.isNotEmpty) ...[
          const SizedBox(height: 8),
          _pill(bidang),
        ],
        const SizedBox(height: 10),
        Wrap(spacing: 14, runSpacing: 4, children: [
          if (location.isNotEmpty) _meta('📍', location),
          if (division.isNotEmpty) _meta('🏷️', division),
          // Kuota penanda saja — lowongan penuh tetap boleh diajukan.
          if (l['quota_summary'] != null)
            Text(
              (l['quota_full'] == true) ? '👥 Kuota penuh' : '👥 ${l['quota_summary']}',
              style: TextStyle(
                fontSize: 11.5,
                color: (l['quota_full'] == true) ? AppColors.warnText : AppColors.textMuted,
                fontWeight: (l['quota_full'] == true) ? FontWeight.w700 : FontWeight.normal,
              ),
            ),
        ]),
        const SizedBox(height: 10),
        const Text('Lihat detail →',
            style: TextStyle(fontSize: 12, color: AppColors.primary, fontWeight: FontWeight.w700)),
      ]),
    );
  }

  Widget _pill(String text) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
        decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(999)),
        child: Text(text, style: const TextStyle(fontSize: 10.5, color: AppColors.primary, fontWeight: FontWeight.w700)),
      );

  Widget _meta(String icon, String text) => Text('$icon $text',
      style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted));
}

/// Detail satu lowongan + tombol "Ajukan Magang di sini" (pre-fill).
class LowonganDetailScreen extends StatefulWidget {
  final int id;
  const LowonganDetailScreen({super.key, required this.id});
  @override
  State<LowonganDetailScreen> createState() => _LowonganDetailScreenState();
}

class _LowonganDetailScreenState extends State<LowonganDetailScreen> {
  Map<String, dynamic>? _l;
  bool _loading = true;
  String? _error;

  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final data = await ApiClient.get('/mahasiswa/lowongan/${widget.id}', token: _token);
      if (!mounted) return;
      setState(() { _l = Map<String, dynamic>.from(data['listing'] ?? {}); _loading = false; });
    } catch (e) {
      if (!mounted) return;
      setState(() { _error = '$e'; _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Detail Lowongan'),
        Expanded(child: _body()),
      ]),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) return ErrorRetry(message: _error!, onRetry: _load);
    final l = _l!;
    final chips = <String>[
      if ('${l['division'] ?? ''}'.isNotEmpty) 'Divisi: ${l['division']}',
      if ('${l['location'] ?? ''}'.isNotEmpty) 'Lokasi: ${l['location']}',
      if ('${l['job_type'] ?? ''}'.isNotEmpty) 'Tipe: ${l['job_type']}',
      if ('${l['bidang'] ?? ''}'.isNotEmpty) 'Bidang: ${l['bidang']}',
      if (l['quota_summary'] != null)
        'Kuota: ${l['quota_summary']}${l['quota_full'] == true ? ' · Penuh' : ''}',
    ];
    final skills = List<String>.from((l['skills'] ?? []).map((e) => '$e')).where((s) => s.isNotEmpty).toList();
    final desc = '${l['description'] ?? ''}';

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('${l['title'] ?? '-'}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, height: 1.3)),
              const SizedBox(height: 2),
              Text('${l['company_name'] ?? ''}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
            ])),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(color: AppColors.successBg, borderRadius: BorderRadius.circular(999)),
              child: const Text('Afiliasi Polines',
                  style: TextStyle(fontSize: 10.5, color: AppColors.success, fontWeight: FontWeight.w700)),
            ),
          ]),
          if (chips.isNotEmpty) ...[
            const SizedBox(height: 14),
            Wrap(spacing: 8, runSpacing: 8, children: chips.map(_infoChip).toList()),
          ],
          // Kuota dihitung per PERUSAHAAN, bukan per lowongan — dijelaskan di
          // sini supaya angkanya tak terbaca sebagai kesalahan hitung ketika
          // satu perusahaan punya beberapa lowongan.
          if (l['quota_note'] != null) ...[
            const SizedBox(height: 12),
            Text('${l['quota_note']}',
                style: const TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.5)),
          ],
        ])),
        if (desc.isNotEmpty)
          AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('DESKRIPSI', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted, letterSpacing: .3)),
            const SizedBox(height: 8),
            Text(desc, style: const TextStyle(fontSize: 13.5, height: 1.6)),
          ])),
        if (skills.isNotEmpty)
          AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('KEAHLIAN / SKILL', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted, letterSpacing: .3)),
            const SizedBox(height: 10),
            Wrap(spacing: 6, runSpacing: 6, children: skills.map((s) => Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(999)),
              child: Text(s, style: const TextStyle(fontSize: 12, color: AppColors.primary, fontWeight: FontWeight.w600)),
            )).toList()),
          ])),
        Container(
          margin: const EdgeInsets.only(top: 4),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.bg,
            borderRadius: BorderRadius.circular(14),
            border: const Border(left: BorderSide(color: AppColors.primary, width: 4)),
            boxShadow: kSoftShadow,
          ),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('Tertarik magang di sini?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
            const SizedBox(height: 4),
            const Text(
              'Perusahaan & posisi otomatis terisi. Kamu tinggal melengkapi data pembimbing industri & bukti penerimaan, lalu tunggu review Kaprodi.',
              style: TextStyle(fontSize: 12.5, color: AppColors.textMuted, height: 1.4),
            ),
            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: () => Navigator.push(context, MaterialPageRoute(
                builder: (_) => AjukanMagangScreen(
                  prefillCompanyId: l['company_id'] as int?,
                  prefillPosition: '${l['title'] ?? ''}',
                  prefillBidang: '${l['bidang'] ?? ''}',
                ),
              )),
              icon: const Icon(Icons.send_rounded, size: 18),
              label: const Text('Ajukan Magang di Perusahaan Ini'),
            ),
          ]),
        ),
      ],
    );
  }

  Widget _infoChip(String text) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 5),
        decoration: BoxDecoration(
          color: AppColors.warm,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: AppColors.border),
        ),
        child: Text(text, style: const TextStyle(fontSize: 12, color: AppColors.text)),
      );
}
