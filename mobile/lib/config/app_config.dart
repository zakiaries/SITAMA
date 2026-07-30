import 'package:flutter/foundation.dart';

/// Konfigurasi global aplikasi SIMAMA Mobile.
class AppConfig {
  /// Server PRODUKSI SIMAMA (hasil deploy). Dipakai untuk build APK yang
  /// dipasang di HP asli / pengujian pengguna.
  static const String productionApiUrl = 'https://simama.site/api';

  /// Setel ke `false` HANYA saat mengembangkan dengan backend lokal
  /// (`php artisan serve`) di emulator/desktop.
  static const bool useProduction = true;

  /// Base URL API backend Laravel.
  ///
  /// - useProduction = true  : https://simama.site/api  (server nyata)
  /// - useProduction = false : localhost sesuai platform  (dev)
  ///     • Emulator Android : http://10.0.2.2:8000/api
  ///     • Desktop / web    : http://127.0.0.1:8000/api
  static String get apiBaseUrl {
    if (useProduction) return productionApiUrl;

    if (kIsWeb) return 'http://127.0.0.1:8000/api';
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000/api';
    }
    // Windows / macOS / Linux / iOS simulator
    return 'http://127.0.0.1:8000/api';
  }

  /// Base URL untuk file publik (mis. /storage/...), yaitu [apiBaseUrl] tanpa '/api'.
  static String get filesBaseUrl => apiBaseUrl.replaceFirst(RegExp(r'/api/?$'), '');

  /// Ubah path file dari API (relatif atau absolut) menjadi URL lengkap.
  static String absoluteFileUrl(String path) {
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return '$filesBaseUrl${path.startsWith('/') ? '' : '/'}$path';
  }
}
