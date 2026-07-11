import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';

/// Error API dengan pesan yang siap ditampilkan (diambil dari JSON backend).
class ApiException implements Exception {
  final String message;
  final int statusCode;
  final Map<String, dynamic>? errors;
  ApiException(this.message, this.statusCode, [this.errors]);
  @override
  String toString() => message;
}

/// Klien HTTP tipis untuk memanggil API Laravel (JSON + Bearer token).
class ApiClient {
  static Uri _uri(String path) => Uri.parse('${AppConfig.apiBaseUrl}$path');

  static Map<String, String> _headers(String? token) => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Cache-Control': 'no-cache, no-store',
        'Pragma': 'no-cache',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  static Future<dynamic> get(String path, {String? token}) async {
    // Cache-buster: di Flutter Web, browser bisa meng-cache respons GET sehingga
    // data setelah aksi (approve/komentar/dll) tampak belum ter-update sampai
    // navigasi ulang. Query unik memaksa fetch baru tiap kali. Aman untuk backend
    // (parameter tak dikenal diabaikan) & tak berpengaruh di Android/iOS.
    final sep = path.contains('?') ? '&' : '?';
    final bustedPath = '$path${sep}_ts=${DateTime.now().millisecondsSinceEpoch}';
    final res = await http.get(_uri(bustedPath), headers: _headers(token));
    return _decode(res);
  }

  static Future<dynamic> post(String path, {Map<String, dynamic>? body, String? token}) async {
    final res = await http.post(_uri(path), headers: _headers(token), body: jsonEncode(body ?? {}));
    return _decode(res);
  }

  static Future<dynamic> put(String path, {Map<String, dynamic>? body, String? token}) async {
    final res = await http.put(_uri(path), headers: _headers(token), body: jsonEncode(body ?? {}));
    return _decode(res);
  }

  static Future<dynamic> delete(String path, {String? token}) async {
    final res = await http.delete(_uri(path), headers: _headers(token));
    return _decode(res);
  }

  /// Kirim data + 1 file (multipart/form-data). [fileField] = nama field file di backend.
  static Future<dynamic> upload(
    String path, {
    required String fileField,
    required String filePath,
    Map<String, String>? fields,
    String? token,
    String method = 'POST',
  }) async {
    final req = http.MultipartRequest(method, _uri(path));
    req.headers['Accept'] = 'application/json';
    if (token != null) req.headers['Authorization'] = 'Bearer $token';
    fields?.forEach((k, v) => req.fields[k] = v);
    req.files.add(await http.MultipartFile.fromPath(fileField, filePath));
    final streamed = await req.send();
    final res = await http.Response.fromStream(streamed);
    return _decode(res);
  }

  static dynamic _decode(http.Response res) {
    final bodyText = res.body.isEmpty ? '{}' : res.body;
    dynamic data;
    try {
      data = jsonDecode(bodyText);
    } catch (_) {
      data = {'message': 'Respon tidak valid dari server.'};
    }

    if (res.statusCode >= 200 && res.statusCode < 300) {
      return data;
    }

    final message = (data is Map && data['message'] != null)
        ? data['message'].toString()
        : 'Terjadi kesalahan (${res.statusCode}).';
    final errors = (data is Map && data['errors'] is Map)
        ? Map<String, dynamic>.from(data['errors'])
        : null;
    throw ApiException(message, res.statusCode, errors);
  }
}
