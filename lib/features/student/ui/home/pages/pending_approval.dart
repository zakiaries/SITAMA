import 'package:flutter/material.dart';
import 'package:sitama/features/auth/domain/usecases/log_out.dart';
import 'package:sitama/features/auth/ui/pages/welcome.dart';
import 'package:sitama/service_locator.dart';

class PendingApprovalPage extends StatelessWidget {
  final bool isRejected;
  const PendingApprovalPage({super.key, this.isRejected = false});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FF),
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 88,
                  height: 88,
                  decoration: BoxDecoration(
                    color: isRejected
                        ? const Color(0xFFFFEBEB)
                        : const Color(0xFFFFF8E1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    isRejected ? Icons.cancel_outlined : Icons.hourglass_top_rounded,
                    size: 44,
                    color: isRejected
                        ? const Color(0xFFC41E3A)
                        : const Color(0xFFF59E0B),
                  ),
                ),
                const SizedBox(height: 28),
                Text(
                  isRejected ? 'Pendaftaran Ditolak' : 'Menunggu Persetujuan',
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1A1A3E),
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 14),
                Text(
                  isRejected
                      ? 'Maaf, pendaftaran akun Anda ditolak oleh Kaprodi. Silakan hubungi Kaprodi untuk informasi lebih lanjut.'
                      : 'Akun Anda sudah terdaftar dan sedang menunggu verifikasi oleh Kaprodi. Setelah disetujui, Anda dapat menggunakan semua fitur SITAMA.',
                  style: const TextStyle(
                    fontSize: 14,
                    color: Color(0xFF6B7280),
                    height: 1.6,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 32),
                if (!isRejected) ...[
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEEF1FF),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFC7D2FF)),
                    ),
                    child: const Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Yang perlu Anda ketahui:',
                          style: TextStyle(
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1A1A3E),
                            fontSize: 13,
                          ),
                        ),
                        SizedBox(height: 8),
                        Text(
                          '• Proses verifikasi dilakukan oleh Kaprodi\n'
                          '• Setelah disetujui, semua fitur akan aktif\n'
                          '• Hubungi Kaprodi jika ada pertanyaan',
                          style: TextStyle(
                            fontSize: 13,
                            color: Color(0xFF374151),
                            height: 1.7,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 32),
                ],
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      await sl<LogoutUseCase>().call();
                      if (context.mounted) {
                        Navigator.pushAndRemoveUntil(
                          context,
                          MaterialPageRoute(builder: (_) => const WelcomePages()),
                          (_) => false,
                        );
                      }
                    },
                    icon: const Icon(Icons.logout, size: 18),
                    label: const Text('Keluar'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1A1A3E),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
