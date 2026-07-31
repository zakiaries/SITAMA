import 'dart:io';
import 'package:flutter/material.dart';
import '../../config/app_config.dart';
import '../../services/api_client.dart';
import '../../services/file_helper.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

/// Bottom-sheet edit profil dipakai semua peran (mahasiswa/dosen/industri).
/// Mengubah nama, email, password, dan foto profil dalam satu tempat.
class ProfileEditSheet extends StatefulWidget {
  final String basePath; // mis. '/mahasiswa', '/dosen', '/dosen-industri'
  final String token;
  final Map<String, dynamic> user;
  const ProfileEditSheet({super.key, required this.basePath, required this.token, required this.user});
  @override
  State<ProfileEditSheet> createState() => _ProfileEditSheetState();
}

class _ProfileEditSheetState extends State<ProfileEditSheet> {
  late final TextEditingController _name = TextEditingController(text: widget.user['name'] ?? '');
  late final TextEditingController _email = TextEditingController(text: widget.user['email'] ?? '');
  final _password = TextEditingController();
  final _passwordConfirm = TextEditingController();
  String? _photoPath; // foto lokal yang baru dipilih (belum diunggah)
  bool _saving = false;
  String? _error;
  bool _obscurePassword = true;
  bool _obscureConfirm = true;

  String get _initials {
    final p = '${widget.user['name'] ?? ''}'.trim().split(RegExp(r'\s+'));
    return p.take(2).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
  }

  @override
  void dispose() {
    _name.dispose(); _email.dispose(); _password.dispose(); _passwordConfirm.dispose();
    super.dispose();
  }

  Future<void> _pickPhoto() async {
    final p = await pickFilePath(extensions: ['jpg', 'jpeg', 'png']);
    if (p != null) setState(() => _photoPath = p);
  }

  Future<void> _save() async {
    if (_name.text.trim().isEmpty || _email.text.trim().isEmpty) {
      setState(() => _error = 'Nama & email wajib diisi.');
      return;
    }
    if (_password.text.isNotEmpty && _password.text != _passwordConfirm.text) {
      setState(() => _error = 'Konfirmasi password tidak cocok.');
      return;
    }
    setState(() { _saving = true; _error = null; });
    try {
      // 1) Unggah foto (bila ada yang baru dipilih) via endpoint khusus multipart.
      if (_photoPath != null) {
        await ApiClient.upload('${widget.basePath}/profile/photo',
            fileField: 'photo', filePath: _photoPath!, token: widget.token);
      }
      // 2) Simpan data akun (nama/email/password).
      final body = {'name': _name.text.trim(), 'email': _email.text.trim()};
      if (_password.text.isNotEmpty) {
        body['password'] = _password.text;
        body['password_confirmation'] = _passwordConfirm.text;
      }
      await ApiClient.put('${widget.basePath}/profile', token: widget.token, body: body);
      if (mounted) { showMessage(context, 'Profil diperbarui.'); Navigator.pop(context, true); }
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } catch (_) {
      setState(() => _error = 'Tidak dapat terhubung ke server. Coba lagi.');
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final netUrl = '${widget.user['photo_url'] ?? ''}';
    ImageProvider? avatarImg;
    if (_photoPath != null) {
      avatarImg = FileImage(File(_photoPath!));
    } else if (netUrl.isNotEmpty) {
      avatarImg = NetworkImage(AppConfig.absoluteFileUrl(netUrl));
    }

    return Padding(
      padding: EdgeInsets.only(left: 20, right: 20, top: 10, bottom: MediaQuery.of(context).viewInsets.bottom + 24),
      child: SingleChildScrollView(
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Center(child: Container(width: 42, height: 4, margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(color: AppColors.borderSubtle, borderRadius: BorderRadius.circular(99)))),
          const Center(child: Text('Edit Profil', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800))),
          const SizedBox(height: 20),

          // ── Avatar + tombol kamera ──
          Center(
            child: Stack(children: [
              CircleAvatar(
                radius: 48,
                backgroundColor: AppColors.primary,
                backgroundImage: avatarImg,
                child: avatarImg == null
                    ? Text(_initials, style: const TextStyle(color: Colors.white, fontSize: 30, fontWeight: FontWeight.w800))
                    : null,
              ),
              Positioned(
                right: -2, bottom: -2,
                child: Material(
                  color: AppColors.primary,
                  shape: const CircleBorder(side: BorderSide(color: Colors.white, width: 2.5)),
                  child: InkWell(
                    customBorder: const CircleBorder(),
                    onTap: _saving ? null : _pickPhoto,
                    child: const Padding(padding: EdgeInsets.all(8), child: Icon(Icons.camera_alt, color: Colors.white, size: 18)),
                  ),
                ),
              ),
            ]),
          ),
          const SizedBox(height: 6),
          Center(child: TextButton(onPressed: _saving ? null : _pickPhoto,
              child: Text(_photoPath == null && netUrl.isEmpty ? 'Tambah Foto' : 'Ganti Foto'))),
          const SizedBox(height: 6),

          if (_error != null)
            Padding(padding: const EdgeInsets.only(bottom: 12),
                child: Text(_error!, style: const TextStyle(color: AppColors.error, fontSize: 13))),

          // ── Data akun ──
          _label('Nama'),
          TextField(controller: _name, decoration: _dec('Nama lengkap')),
          const SizedBox(height: 14),
          _label('Email'),
          TextField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: _dec('nama@email.com')),

          const SizedBox(height: 22),
          const Text('Ubah Password', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
          const Text('Kosongkan jika tidak ingin mengubah.', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
          const SizedBox(height: 12),
          TextField(
            controller: _password,
            obscureText: _obscurePassword,
            decoration: _dec('Password baru (min. 8 karakter)',
                suffix: _eye(_obscurePassword, () => setState(() => _obscurePassword = !_obscurePassword))),
          ),
          const SizedBox(height: 14),
          TextField(
            controller: _passwordConfirm,
            obscureText: _obscureConfirm,
            decoration: _dec('Konfirmasi password',
                suffix: _eye(_obscureConfirm, () => setState(() => _obscureConfirm = !_obscureConfirm))),
          ),

          const SizedBox(height: 26),
          ElevatedButton(
            style: ElevatedButton.styleFrom(minimumSize: const Size.fromHeight(50)),
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.4, color: Colors.white))
                : const Text('Simpan Perubahan'),
          ),
        ]),
      ),
    );
  }

  Widget _label(String t) => Padding(
        padding: const EdgeInsets.only(bottom: 6),
        child: Text(t, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
      );

  InputDecoration _dec(String hint, {Widget? suffix}) =>
      InputDecoration(hintText: hint, suffixIcon: suffix);

  /// Tombol mata show/hide untuk field password (paritas dengan web).
  Widget _eye(bool obscured, VoidCallback onTap) => IconButton(
        tooltip: 'Tampilkan / sembunyikan kata sandi',
        icon: Icon(obscured ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 20),
        onPressed: onTap,
      );
}

/// Avatar bulat: tampilkan foto profil (network) bila ada, jika tidak inisial nama.
/// [photoUrl] boleh relatif ('/storage/...') atau absolut; kosong = pakai inisial.
class ProfileAvatar extends StatelessWidget {
  final String name;
  final String photoUrl;
  final double radius;
  final double fontSize;
  const ProfileAvatar({
    super.key,
    required this.name,
    required this.photoUrl,
    this.radius = 24,
    this.fontSize = 16,
  });

  String get _initials {
    final p = name.trim().split(RegExp(r'\s+'));
    return p.take(2).map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();
  }

  @override
  Widget build(BuildContext context) {
    final has = photoUrl.trim().isNotEmpty;
    return CircleAvatar(
      radius: radius,
      backgroundColor: AppColors.primary,
      backgroundImage: has ? NetworkImage(AppConfig.absoluteFileUrl(photoUrl)) : null,
      child: has
          ? null
          : Text(_initials, style: TextStyle(color: Colors.white, fontSize: fontSize, fontWeight: FontWeight.w800)),
    );
  }
}
