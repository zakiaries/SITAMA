import 'package:flutter/foundation.dart';

/// Konfigurasi global aplikasi SITAMA Mobile.
class AppConfig {
  /// Base URL API backend Laravel — otomatis menyesuaikan platform.
  ///
  /// - Web / Windows / desktop : http://127.0.0.1:8000/api
  /// - Emulator Android        : http://10.0.2.2:8000/api  (alias localhost emulator)
  /// - HP fisik (1 WiFi)       : ganti [physicalDeviceHost] ke IP laptop,
  ///                             dan jalankan `php artisan serve --host=0.0.0.0 --port=8000`.
  static String get apiBaseUrl {
    if (kIsWeb) return 'http://127.0.0.1:8000/api';
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000/api';
    }
    // Windows / macOS / Linux / iOS simulator
    return 'http://127.0.0.1:8000/api';
  }

  /// Jika pakai HP fisik, isi IP laptop di sini (mis. '192.168.1.10') lalu
  /// ganti nilai return di [apiBaseUrl] untuk Android menjadi host ini.
  static const String physicalDeviceHost = '';
}
