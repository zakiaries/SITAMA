import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

/// Layar notifikasi generik. [basePath] mis. '/mahasiswa', '/dosen', '/dosen-industri'.
class NotifikasiScreen extends StatefulWidget {
  final String basePath;
  const NotifikasiScreen({super.key, required this.basePath});
  @override
  State<NotifikasiScreen> createState() => _NotifikasiScreenState();
}

class _NotifikasiScreenState extends State<NotifikasiScreen> {
  late Future<Map<String, dynamic>> _future;
  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final data = await ApiClient.get('${widget.basePath}/notifikasi', token: _token);
    return Map<String, dynamic>.from(data);
  }

  void _reload() => setState(() => _future = _load());

  Future<void> _markAll() async {
    try {
      await ApiClient.post('${widget.basePath}/notifikasi/read-all', token: _token);
      _reload();
    } on ApiException catch (e) {
      if (mounted) showMessage(context, e.message, error: true);
    }
  }

  Future<void> _markOne(int id) async {
    try {
      await ApiClient.post('${widget.basePath}/notifikasi/$id/read', token: _token);
      _reload();
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifikasi'),
        actions: [TextButton(onPressed: _markAll, child: const Text('Tandai semua'))],
      ),
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
            final paginator = Map<String, dynamic>.from(snap.data!['notifications'] ?? {});
            final items = List<Map<String, dynamic>>.from(paginator['data'] ?? []);
            if (items.isEmpty) {
              return ListView(children: const [EmptyState('Belum ada notifikasi.', icon: Icons.notifications_none)]);
            }
            return ListView(
              padding: const EdgeInsets.all(16),
              children: items.map((n) {
                final unread = n['is_read'] != true;
                return Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  decoration: unread
                      ? const BoxDecoration(
                          border: Border(left: BorderSide(color: AppColors.primary, width: 3)),
                        )
                      : null,
                  child: AppCard(
                  onTap: unread ? () => _markOne(n['id']) : null,
                  child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Container(
                      width: 38, height: 38,
                      decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(9)),
                      child: const Icon(Icons.notifications_outlined, color: AppColors.primary, size: 20),
                    ),
                    const SizedBox(width: 12),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(n['message'] ?? '', style: TextStyle(fontWeight: unread ? FontWeight.w700 : FontWeight.w500)),
                      const SizedBox(height: 2),
                      Text('${n['category'] ?? ''} · ${n['date'] ?? ''}', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                    ])),
                    if (unread) Container(width: 9, height: 9, margin: const EdgeInsets.only(top: 4), decoration: const BoxDecoration(color: AppColors.primary, shape: BoxShape.circle)),
                  ]),
                  ),
                );
              }).toList(),
            );
          },
        ),
      ),
    );
  }
}
