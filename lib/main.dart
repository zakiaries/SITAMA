import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:provider/provider.dart';
import 'package:sitama/core/config/themes/app_theme.dart';
import 'package:sitama/core/config/themes/theme_provider.dart';
import 'package:sitama/core/shared/provider/app_providers.dart';
import 'package:sitama/features/auth/ui/pages/splash.dart';
import 'package:sitama/service_locator.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/foundation.dart' show kIsWeb;

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final prefs = await SharedPreferences.getInstance();
  setupServiceLocator(prefs);
  WidgetsFlutterBinding.ensureInitialized();

  // Firebase hanya untuk mobile dan desktop
  if (!kIsWeb) {
    try {
      await Firebase.initializeApp();
    } catch (e) {
      debugPrint('Firebase initialization error: $e');
    }
  }

  runApp(
    MultiProvider(
      providers: AppProviders.providers,
      child: MultiBlocProvider(
        providers: AppProviders.blocProviders,
        child: const MyApp(),
      ),
    ),
  );
}

class MyApp extends StatefulWidget {
  static final GlobalKey<NavigatorState> navigatorKey =
      GlobalKey<NavigatorState>();

  const MyApp({super.key});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  @override
  Widget build(BuildContext context) {
    return Consumer<ThemeProvider>(
      builder: (context, themeProvider, child) {
        return MaterialApp(
          navigatorKey: MyApp.navigatorKey,
          title: 'Sitama',
          theme: AppTheme.lightTheme,
          darkTheme: AppTheme.darkTheme,
          themeMode:
              themeProvider.isDarkMode ? ThemeMode.dark : ThemeMode.light,
          debugShowCheckedModeBanner: false,
          home: const SplashPage(),
        );
      },
    );
  }
}
