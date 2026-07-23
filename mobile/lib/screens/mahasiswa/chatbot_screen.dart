import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';
import '../widgets/ui.dart';

/// Pesan dalam percakapan chatbot.
class _Msg {
  final String text;
  final bool fromUser;
  final List<String> suggestions;
  final List<Map<String, dynamic>> recommendations;
  _Msg(this.text, {this.fromUser = false, this.suggestions = const [], this.recommendations = const []});
}

class ChatbotScreen extends StatefulWidget {
  const ChatbotScreen({super.key});
  @override
  State<ChatbotScreen> createState() => _ChatbotScreenState();
}

class _ChatbotScreenState extends State<ChatbotScreen> {
  final _input = TextEditingController();
  final _scroll = ScrollController();
  final List<_Msg> _messages = [];
  List<String> _popular = [];
  bool _loading = true;
  bool _sending = false;
  String? _error;

  String get _token => context.read<AuthProvider>().token ?? '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _input.dispose();
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final data = await ApiClient.get('/mahasiswa/chatbot', token: _token);
      final history = List<Map<String, dynamic>>.from(data['history'] ?? []);
      final popular = List<String>.from((data['popular_questions'] ?? []).map((e) => '$e'));
      _messages.clear();
      for (final h in history) {
        _messages.add(_Msg('${h['message'] ?? ''}', fromUser: true));
        _messages.add(_Msg('${h['answer'] ?? ''}'));
      }
      if (!mounted) return;
      setState(() { _popular = popular; _loading = false; });
      _scrollToEnd();
    } catch (e) {
      if (!mounted) return;
      setState(() { _error = '$e'; _loading = false; });
    }
  }

  Future<void> _send(String raw) async {
    final text = raw.trim();
    if (text.isEmpty || _sending) return;
    _input.clear();
    setState(() {
      _messages.add(_Msg(text, fromUser: true));
      _sending = true;
    });
    _scrollToEnd();
    try {
      final data = await ApiClient.post('/mahasiswa/chatbot', token: _token, body: {'message': text});
      final answer = '${data['answer'] ?? ''}';
      final rawSug = List<Map<String, dynamic>>.from(data['suggestions'] ?? []);
      final suggestions = rawSug
          .map((s) => '${s['question'] ?? ''}')
          .where((s) => s.isNotEmpty)
          .toList();
      final recommendations = List<Map<String, dynamic>>.from(data['recommendations'] ?? []);
      if (!mounted) return;
      setState(() => _messages.add(_Msg(answer, suggestions: suggestions, recommendations: recommendations)));
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _messages.add(_Msg(e.message)));
    } catch (_) {
      if (!mounted) return;
      setState(() => _messages.add(_Msg('Tidak dapat terhubung ke server. Coba lagi.')));
    } finally {
      if (mounted) setState(() => _sending = false);
      _scrollToEnd();
    }
  }

  void _scrollToEnd() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scroll.hasClients) {
        _scroll.animateTo(_scroll.position.maxScrollExtent,
            duration: const Duration(milliseconds: 250), curve: Curves.easeOut);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.warm,
      body: Column(children: [
        const DetailHeader(title: 'Chatbot SITAMA', subtitle: 'Tanya seputar magang, seminar & laporan'),
        Expanded(child: _body()),
        _inputBar(),
      ]),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) return ErrorRetry(message: _error!, onRetry: _load);

    return ListView(
      controller: _scroll,
      padding: const EdgeInsets.fromLTRB(14, 16, 14, 16),
      children: [
        if (_messages.isEmpty) _welcome(),
        ..._messages.map(_bubble),
        if (_sending) _typing(),
      ],
    );
  }

  Widget _welcome() => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Column(children: [
              Container(
                width: 64, height: 64,
                decoration: const BoxDecoration(color: AppColors.blueTint, shape: BoxShape.circle),
                child: const Icon(Icons.smart_toy_outlined, color: AppColors.primary, size: 32),
              ),
              const SizedBox(height: 10),
              const Text('Halo! Ada yang bisa dibantu?',
                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
              const SizedBox(height: 4),
              const Text('Ketik pertanyaan atau pilih salah satu di bawah.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: AppColors.textMuted, fontSize: 12.5)),
            ]),
          ),
          const SizedBox(height: 16),
          if (_popular.isNotEmpty) ...[
            const Text('PERTANYAAN POPULER',
                style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.textMuted, letterSpacing: .5)),
            const SizedBox(height: 8),
            Wrap(spacing: 8, runSpacing: 8, children: _popular.map(_chip).toList()),
          ],
        ],
      );

  Widget _chip(String q) => InkWell(
        onTap: _sending ? null : () => _send(q),
        borderRadius: BorderRadius.circular(999),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: AppColors.bg,
            borderRadius: BorderRadius.circular(999),
            border: Border.all(color: AppColors.border),
          ),
          child: Text(q, style: const TextStyle(fontSize: 12.5, color: AppColors.primary, fontWeight: FontWeight.w600)),
        ),
      );

  Widget _bubble(_Msg m) {
    if (m.fromUser) {
      return Align(
        alignment: Alignment.centerRight,
        child: Container(
          margin: const EdgeInsets.only(bottom: 10, left: 40),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: const BoxDecoration(
            color: AppColors.primary,
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(16), topRight: Radius.circular(16), bottomLeft: Radius.circular(16),
            ),
          ),
          child: Text(m.text, style: const TextStyle(color: Colors.white, fontSize: 13.5, height: 1.4)),
        ),
      );
    }
    return Align(
      alignment: Alignment.centerLeft,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.only(bottom: 8, right: 40),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.bg,
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(16), topRight: Radius.circular(16), bottomRight: Radius.circular(16),
              ),
              border: Border.all(color: AppColors.borderSubtle),
              boxShadow: kSoftShadow,
            ),
            child: Text(m.text, style: const TextStyle(fontSize: 13.5, height: 1.5)),
          ),
          if (m.recommendations.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(bottom: 10, right: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: m.recommendations.map(_recoCard).toList(),
              ),
            ),
          if (m.suggestions.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(bottom: 12, right: 20),
              child: Wrap(spacing: 8, runSpacing: 8, children: m.suggestions.map(_chip).toList()),
            ),
        ],
      ),
    );
  }

  /// Kartu rekomendasi tempat magang (menyamai kartu di web).
  Widget _recoCard(Map<String, dynamic> r) {
    final company = '${r['company'] ?? '-'}';
    final title = '${r['title'] ?? ''}';
    final bidang = '${r['bidang'] ?? ''}';
    final location = '${r['location'] ?? ''}';
    final contact = '${r['contact'] ?? ''}';
    final pct = (((r['score'] ?? 0) as num) * 100).round();
    return Container(
      margin: const EdgeInsets.only(top: 8),
      padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 11),
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Expanded(child: Text(company, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800))),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 1),
            decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(20)),
            child: Text('$pct%', style: const TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.w700)),
          ),
        ]),
        if (title.isNotEmpty) ...[
          const SizedBox(height: 2),
          Text(title, style: const TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
        ],
        if (bidang.isNotEmpty || location.isNotEmpty) ...[
          const SizedBox(height: 7),
          Wrap(spacing: 8, runSpacing: 4, crossAxisAlignment: WrapCrossAlignment.center, children: [
            if (bidang.isNotEmpty)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 2),
                decoration: BoxDecoration(color: AppColors.blueTint, borderRadius: BorderRadius.circular(20)),
                child: Text(bidang, style: const TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.w600)),
              ),
            if (location.isNotEmpty)
              Text('📍 $location', style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
          ]),
        ],
        if (contact.isNotEmpty) ...[
          const SizedBox(height: 6),
          Text(contact, style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
        ],
      ]),
    );
  }

  Widget _typing() => Align(
        alignment: Alignment.centerLeft,
        child: Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: AppColors.bg,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.borderSubtle),
          ),
          child: const SizedBox(
            width: 34,
            child: Text('•••', style: TextStyle(color: AppColors.textMuted, fontWeight: FontWeight.w900, letterSpacing: 2)),
          ),
        ),
      );

  Widget _inputBar() {
    return SafeArea(
      top: false,
      child: Container(
        padding: const EdgeInsets.fromLTRB(14, 8, 14, 8),
        decoration: const BoxDecoration(
          color: AppColors.bg,
          border: Border(top: BorderSide(color: AppColors.borderSubtle)),
        ),
        child: Row(children: [
          Expanded(
            child: TextField(
              controller: _input,
              textInputAction: TextInputAction.send,
              onSubmitted: _send,
              minLines: 1,
              maxLines: 4,
              decoration: InputDecoration(
                hintText: 'Tulis pertanyaan...',
                filled: true,
                fillColor: AppColors.warm,
                isDense: true,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: const BorderSide(color: AppColors.primary, width: 1.4)),
              ),
            ),
          ),
          const SizedBox(width: 8),
          Material(
            color: AppColors.primary,
            shape: const CircleBorder(),
            child: InkWell(
              customBorder: const CircleBorder(),
              onTap: _sending ? null : () => _send(_input.text),
              child: const SizedBox(
                width: 46, height: 46,
                child: Icon(Icons.send_rounded, color: Colors.white, size: 20),
              ),
            ),
          ),
        ]),
      ),
    );
  }
}
