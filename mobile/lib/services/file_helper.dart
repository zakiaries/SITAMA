import 'package:file_picker/file_picker.dart';

/// Pilih 1 file dari perangkat. Kembalikan path, atau null bila batal.
Future<String?> pickFilePath({List<String>? extensions}) async {
  final res = await FilePicker.platform.pickFiles(
    type: (extensions == null || extensions.isEmpty) ? FileType.any : FileType.custom,
    allowedExtensions: extensions,
  );
  if (res == null || res.files.isEmpty) return null;
  return res.files.single.path;
}

String fileName(String path) => path.split(RegExp(r'[\\/]')).last;
