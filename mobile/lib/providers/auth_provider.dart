import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../services/api_client.dart';

enum AuthStatus { unknown, authenticated, unauthenticated }

/// Menyimpan sesi login (token + data user) dan aksi auth.
class AuthProvider extends ChangeNotifier {
  static const _storage = FlutterSecureStorage();
  static const _tokenKey = 'sitama_token';

  AuthStatus status = AuthStatus.unknown;
  String? token;
  Map<String, dynamic>? user;

  String? get role => user?['role'] as String?;
  String get name => (user?['name'] ?? '') as String;

  /// Dipanggil saat app start: cek token tersimpan.
  Future<void> bootstrap() async {
    token = await _storage.read(key: _tokenKey);
    if (token == null) {
      status = AuthStatus.unauthenticated;
      notifyListeners();
      return;
    }
    try {
      final data = await ApiClient.get('/me', token: token);
      user = Map<String, dynamic>.from(data['user']);
      status = AuthStatus.authenticated;
    } catch (_) {
      await _storage.delete(key: _tokenKey);
      token = null;
      status = AuthStatus.unauthenticated;
    }
    notifyListeners();
  }

  /// Login → simpan token & user. Lempar ApiException bila gagal.
  Future<void> login(String username, String password) async {
    final data = await ApiClient.post('/login', body: {
      'username': username,
      'password': password,
    });
    token = data['token'] as String;
    user = Map<String, dynamic>.from(data['user']);
    await _storage.write(key: _tokenKey, value: token);
    status = AuthStatus.authenticated;
    notifyListeners();
  }

  /// Registrasi mahasiswa (akun pending). Lempar ApiException bila gagal.
  Future<String> register(Map<String, dynamic> payload) async {
    final data = await ApiClient.post('/register', body: payload);
    return (data['message'] ?? 'Pendaftaran berhasil.') as String;
  }

  Future<void> logout() async {
    try {
      if (token != null) await ApiClient.post('/logout', token: token);
    } catch (_) {}
    await _storage.delete(key: _tokenKey);
    token = null;
    user = null;
    status = AuthStatus.unauthenticated;
    notifyListeners();
  }
}
