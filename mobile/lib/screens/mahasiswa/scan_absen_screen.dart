import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

/// Audiens memindai QR daftar hadir seminar langsung di dalam app.
/// QR berisi URL `.../seminar/hadir/{token}?rt={rt}`; kita ambil token & rt lalu
/// panggil POST /mahasiswa/seminar/attend (identitas terisi otomatis dari akun).
class ScanAbsenScreen extends StatefulWidget {
  const ScanAbsenScreen({super.key});
  @override
  State<ScanAbsenScreen> createState() => _ScanAbsenScreenState();
}

class _ScanAbsenScreenState extends State<ScanAbsenScreen> {
  final MobileScannerController _controller = MobileScannerController(detectionSpeed: DetectionSpeed.noDuplicates);
  bool _busy = false;

  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _onDetect(BarcodeCapture capture) async {
    if (_busy) return;
    final raw = capture.barcodes.isNotEmpty ? capture.barcodes.first.rawValue : null;
    if (raw == null) return;

    // Parse URL QR: .../seminar/hadir/{token}?rt={rt}
    String? tok, rt;
    final uri = Uri.tryParse(raw);
    if (uri != null) {
      final segs = uri.pathSegments;
      final i = segs.indexOf('hadir');
      if (i >= 0 && i + 1 < segs.length) tok = segs[i + 1];
      rt = uri.queryParameters['rt'];
    }

    if (tok == null || tok.isEmpty || rt == null || rt.isEmpty) {
      if (mounted) showMessage(context, 'QR tidak dikenali. Pastikan memindai QR daftar hadir seminar.', error: true);
      return; // biarkan terus memindai
    }

    setState(() => _busy = true);
    try {
      final res = await ApiClient.post('/mahasiswa/seminar/attend',
          token: _token, body: {'token': tok, 'rt': rt});
      if (!mounted) return;
      await _showResult(
        ok: true,
        title: res['already'] == true ? 'Sudah Terdaftar' : 'Berhasil Absen',
        message: '${res['message'] ?? 'Daftar hadir tercatat.'}'
            '${(res['seminar_title'] ?? '').toString().isNotEmpty ? '\n\nSeminar: ${res['seminar_title']}' : ''}',
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      await _showResult(ok: false, title: 'Gagal Absen', message: e.message);
    } catch (_) {
      if (!mounted) return;
      await _showResult(ok: false, title: 'Gagal Absen', message: 'Tidak dapat terhubung ke server. Coba lagi.');
    }
  }

  Future<void> _showResult({required bool ok, required String title, required String message}) async {
    await showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (c) => AlertDialog(
        icon: Icon(ok ? Icons.check_circle : Icons.error_outline,
            color: ok ? AppColors.success : AppColors.error, size: 40),
        title: Text(title, textAlign: TextAlign.center),
        content: Text(message, textAlign: TextAlign.center),
        actions: [
          if (!ok)
            TextButton(
              onPressed: () { Navigator.pop(c); setState(() => _busy = false); },
              child: const Text('Pindai Ulang'),
            ),
          ElevatedButton(
            onPressed: () { Navigator.pop(c); Navigator.pop(context); }, // tutup dialog + layar scan
            child: const Text('Selesai'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('Scan QR Daftar Hadir'),
        actions: [
          IconButton(onPressed: () => _controller.toggleTorch(), icon: const Icon(Icons.flash_on)),
          IconButton(onPressed: () => _controller.switchCamera(), icon: const Icon(Icons.cameraswitch)),
        ],
      ),
      body: Stack(
        alignment: Alignment.center,
        children: [
          MobileScanner(controller: _controller, onDetect: _onDetect),
          // Bingkai pemindai
          Container(
            width: 240, height: 240,
            decoration: BoxDecoration(
              border: Border.all(color: Colors.white, width: 3),
              borderRadius: BorderRadius.circular(18),
            ),
          ),
          if (_busy) const ColoredBox(color: Colors.black45, child: Center(child: CircularProgressIndicator(color: Colors.white))),
          Positioned(
            bottom: 40, left: 24, right: 24,
            child: Text(
              'Arahkan kamera ke QR daftar hadir yang ditampilkan di layar seminar.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.white.withAlpha(230), fontSize: 13),
            ),
          ),
        ],
      ),
    );
  }
}
