import 'package:flutter/material.dart';

class ChatbotRecommendationPage extends StatefulWidget {
  const ChatbotRecommendationPage({Key? key}) : super(key: key);

  @override
  State<ChatbotRecommendationPage> createState() =>
      _ChatbotRecommendationPageState();
}

class _ChatbotRecommendationPageState extends State<ChatbotRecommendationPage> {
  final TextEditingController _messageController = TextEditingController();
  final List<ChatMessage> _messages = [
    ChatMessage(
      text:
          'Halo Zaki! Saya siap membantu menemukan lowongan magang yang sesuai. Ceritakan bidang atau skill apa yang kamu minati!',
      isBot: true,
    ),
  ];

  void _sendMessage(String message) {
    if (message.isEmpty) return;

    setState(() {
      _messages.add(ChatMessage(text: message, isBot: false));
      _messageController.clear();

      // Simulate bot response
      Future.delayed(Duration(milliseconds: 500), () {
        if (mounted) {
          setState(() {
            if (message.toLowerCase().contains('flutter')) {
              _messages.add(ChatMessage(
                text:
                    'Cocok! Berdasarkan minat mu di Flutter & mobile dev, berikut rekomendasi yang relevan:',
                isBot: true,
              ));
              _messages.add(ChatMessage(
                text: 'job_card',
                isBot: true,
                isJobCard: true,
                jobTitle: 'Frontend Developer Intern',
                company: 'PT. Telkom Indonesia - Semarang',
                skills: ['Flutter', 'Dart', 'Mobile'],
              ));
              _messages.add(ChatMessage(
                text: 'job_card',
                isBot: true,
                isJobCard: true,
                jobTitle: 'Mobile Developer Intern',
                company: 'Grab Indonesia - Jakarta (Remote)',
                skills: ['Flutter', 'Firebase', 'REST API'],
              ));
              _messages.add(ChatMessage(
                text: 'Ada yang di Semarang saja?',
                isBot: true,
                isActionCard: true,
                actions: ['Lowongan remote', 'Tambah skill', 'Daftar ke Telkom'],
              ));
            } else {
              _messages.add(ChatMessage(
                text:
                    'Untuk lokasi Semarang dengan skill Flutter, PT. Telkom Indonesia paling sesuai sesuai karena mencari Flutter developer dengan pengalaman UI. Mau saya bantu daftarkan?',
                isBot: true,
                isActionCard: true,
                actions: ['Lowongan remote', 'Tambah skill', 'Daftar ke Telkom'],
              ));
            }
          });
        }
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Color(0xFF3D568F),
        leading: IconButton(
          icon: Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Chatbot Rekomendasi',
              style: TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            SizedBox(height: 2),
            Row(
              children: [
                Container(
                  width: 6,
                  height: 6,
                  decoration: BoxDecoration(
                    color: Colors.greenAccent,
                    shape: BoxShape.circle,
                  ),
                ),
                SizedBox(width: 6),
                Text(
                  'SITAMA AI - Online',
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              padding: EdgeInsets.all(16),
              itemCount: _messages.length,
              reverse: false,
              itemBuilder: (context, index) {
                final message = _messages[index];
                return _buildChatBubble(message);
              },
            ),
          ),
          _buildInputArea(),
        ],
      ),
    );
  }

  Widget _buildChatBubble(ChatMessage message) {
    if (message.isJobCard) {
      return Padding(
        padding: EdgeInsets.only(bottom: 12),
        child: Align(
          alignment: Alignment.centerLeft,
          child: Container(
            margin: EdgeInsets.only(bottom: 8),
            padding: EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey[200]!),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  message.jobTitle,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF3D568F),
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  message.company,
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.grey[600],
                  ),
                ),
                SizedBox(height: 8),
                Wrap(
                  spacing: 6,
                  children: message.skills
                      .map((skill) => Container(
                            padding: EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: Color(0xFF3D568F).withOpacity(0.1),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              skill,
                              style: TextStyle(
                                fontSize: 10,
                                color: Color(0xFF3D568F),
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ))
                      .toList(),
                ),
              ],
            ),
          ),
        ),
      );
    }

    if (message.isActionCard) {
      return Padding(
        padding: EdgeInsets.only(bottom: 12),
        child: Align(
          alignment: Alignment.centerLeft,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: EdgeInsets.only(bottom: 8),
                child: Container(
                  constraints: BoxConstraints(maxWidth: 280),
                  padding: EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    message.text,
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.grey[800],
                    ),
                  ),
                ),
              ),
              Wrap(
                spacing: 6,
                runSpacing: 6,
                children: message.actions
                    .map((action) => Container(
                          padding: EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            border: Border.all(color: Colors.grey[300]!),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Text(
                            action,
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey[700],
                            ),
                          ),
                        ))
                    .toList(),
              ),
            ],
          ),
        ),
      );
    }

    return Padding(
      padding: EdgeInsets.only(bottom: 12),
      child: Align(
        alignment: message.isBot ? Alignment.centerLeft : Alignment.centerRight,
        child: Container(
          constraints: BoxConstraints(maxWidth: 280),
          padding: EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: message.isBot
                ? Colors.grey[100]
                : Color(0xFF3D568F).withOpacity(0.85),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Text(
            message.text,
            style: TextStyle(
              fontSize: 13,
              color: message.isBot ? Colors.grey[800] : Colors.white,
              height: 1.4,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Colors.grey[200]!)),
      ),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _messageController,
              decoration: InputDecoration(
                hintText: 'Ketik pesan...',
                hintStyle: TextStyle(color: Colors.grey[400]),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(24),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(24),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                contentPadding: EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
              ),
              textInputAction: TextInputAction.send,
              onSubmitted: (value) => _sendMessage(value),
            ),
          ),
          SizedBox(width: 8),
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: Color(0xFF3D568F),
              shape: BoxShape.circle,
            ),
            child: IconButton(
              icon: Icon(Icons.send, color: Colors.white, size: 20),
              onPressed: () => _sendMessage(_messageController.text),
            ),
          ),
        ],
      ),
    );
  }
}

class ChatMessage {
  final String text;
  final bool isBot;
  final bool isJobCard;
  final bool isActionCard;
  final String jobTitle;
  final String company;
  final List<String> skills;
  final List<String> actions;

  ChatMessage({
    required this.text,
    required this.isBot,
    this.isJobCard = false,
    this.isActionCard = false,
    this.jobTitle = '',
    this.company = '',
    this.skills = const [],
    this.actions = const [],
  });
}
