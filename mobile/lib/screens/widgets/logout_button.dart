import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';

class LogoutButton extends StatelessWidget {
  const LogoutButton({super.key});
  @override
  Widget build(BuildContext context) {
    return IconButton(
      tooltip: 'Keluar',
      icon: const Icon(Icons.logout),
      onPressed: () async {
        final ok = await showDialog<bool>(
          context: context,
          builder: (c) => AlertDialog(
            title: const Text('Keluar?'),
            content: const Text('Anda yakin ingin keluar dari akun ini?'),
            actions: [
              TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
              TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Keluar')),
            ],
          ),
        );
        if (ok == true && context.mounted) {
          await context.read<AuthProvider>().logout();
        }
      },
    );
  }
}
