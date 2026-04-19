import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/lecturer_industry/ui/assessment/bloc/industry_score_cubit.dart';
import 'package:sitama/features/lecturer_industry/domain/entities/lecturer_industry_detail_student_entity.dart';

class IndustryAssessmentPage extends StatefulWidget {
  final int studentId;
  final String studentName;

  const IndustryAssessmentPage({
    super.key,
    required this.studentId,
    required this.studentName,
  });

  @override
  State<IndustryAssessmentPage> createState() => _IndustryAssessmentPageState();
}

class _IndustryAssessmentPageState extends State<IndustryAssessmentPage> {
  late IndustryScoreCubit _cubit;
  final TextEditingController _notesController = TextEditingController();
  final Map<String, TextEditingController> _scoreControllers = {};

  @override
  void initState() {
    super.initState();
    _cubit = context.read<IndustryScoreCubit>();
    _cubit.fetchScores(widget.studentId);
  }

  @override
  void dispose() {
    _notesController.dispose();
    for (var controller in _scoreControllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  void _initializeScoreControllers(IndustryScoreEntity scores) {
    if (_scoreControllers.isEmpty) {
      for (var category in scores.categories) {
        for (var item in category.items) {
          final key = '${category.category_name}_${item.item_name}';
          _scoreControllers[key] =
              TextEditingController(text: item.score.toString());
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF2F5FB),
      body: BlocListener<IndustryScoreCubit, IndustryScoreState>(
        listener: (context, state) {
          if (state is ScoreSaved) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.message),
                duration: const Duration(seconds: 2),
                backgroundColor: const Color(0xFF10B981),
              ),
            );
            Future.delayed(const Duration(milliseconds: 1500), () {
              if (context.mounted) Navigator.pop(context);
            });
          } else if (state is Failure) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Error: ${state.errorMessage}'),
                duration: const Duration(seconds: 2),
                backgroundColor: Colors.red,
              ),
            );
          }
        },
        child: BlocBuilder<IndustryScoreCubit, IndustryScoreState>(
          builder: (context, state) {
            if (state is Loaded) {
              _initializeScoreControllers(state.scores);
              return _buildContent(state.scores);
            } else if (state is Failure) {
              return Center(child: Text('Error: ${state.errorMessage}'));
            }
            return const Center(child: CircularProgressIndicator());
          },
        ),
      ),
    );
  }

  Widget _buildContent(IndustryScoreEntity scores) {
    return SafeArea(
      child: SingleChildScrollView(
        child: Column(
          children: [
            // Header
            Container(
              decoration: const BoxDecoration(
                color: Color(0xFF0D2B6E),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
              ),
              child: Padding(
                padding: const EdgeInsets.fromLTRB(18, 16, 18, 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        GestureDetector(
                          onTap: () => Navigator.pop(context),
                          child: Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withValues(alpha: 0.12),
                            ),
                            child: const Icon(Icons.arrow_back,
                                color: Color(0xFFE8F0FE), size: 16),
                          ),
                        ),
                        const SizedBox(width: 12),
                        const Text(
                          'Penilaian Akhir',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 13, vertical: 9),
                      child: Row(
                        children: [
                          Container(
                            width: 30,
                            height: 30,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withValues(alpha: 0.2),
                            ),
                            child: Center(
                              child: Text(
                                _getInitials(widget.studentName),
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  widget.studentName,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                const Text(
                                  'Frontend Developer Intern · PT. Telkom',
                                  style: TextStyle(
                                    color: Color(0xFF93B4F0),
                                    fontSize: 10,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            // Score Categories
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: scores.categories.map((category) {
                  return _buildCategoryCard(category);
                }).toList(),
              ),
            ),
            // Average Score
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border:
                      Border.all(color: const Color(0xFFD0DDF5), width: 0.5),
                ),
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Rata-rata Nilai Industri',
                      style: TextStyle(
                        color: Color(0xFF1E3A6E),
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        height: 1,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Text(
                          '${scores.average_score.toInt()}',
                          style: const TextStyle(
                            color: Color(0xFF0D2B6E),
                            fontSize: 40,
                            fontWeight: FontWeight.w800,
                            height: 1,
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              ClipRRect(
                                borderRadius: BorderRadius.circular(3),
                                child: LinearProgressIndicator(
                                  value: scores.average_score / 100,
                                  minHeight: 8,
                                  backgroundColor: const Color(0xFFD8E8F8),
                                  valueColor:
                                      const AlwaysStoppedAnimation<Color>(
                                    Color(0xFF1A4BBB),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                scores.score_quality,
                                style: const TextStyle(
                                  color: Color(0xFF1A4BBB),
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                  height: 1,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
            // Final Notes
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border:
                      Border.all(color: const Color(0xFFD0DDF5), width: 0.5),
                ),
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Catatan Akhir',
                      style: TextStyle(
                        color: Color(0xFF1E3A6E),
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        height: 1,
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: _notesController,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Catatan akhir untuk mahasiswa...',
                        filled: true,
                        fillColor: const Color(0xFFF7F9FF),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(
                            color: Color(0xFFC8D8F0),
                            width: 0.5,
                          ),
                        ),
                        contentPadding: const EdgeInsets.all(10),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 14),
            // Save Button
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    _cubit.saveScores(widget.studentId, _notesController.text);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0D2B6E),
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(13),
                    ),
                  ),
                  child: const Text(
                    'Simpan & Kirim Penilaian',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildCategoryCard(ScoreCategoryEntity category) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD0DDF5), width: 0.5),
      ),
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            category.category_name,
            style: const TextStyle(
              color: Color(0xFF1E3A6E),
              fontSize: 13,
              fontWeight: FontWeight.w700,
              height: 1,
            ),
          ),
          const SizedBox(height: 12),
          ...category.items.asMap().entries.map((entry) {
            final index = entry.key;
            final item = entry.value;
            final key = '${category.category_name}_${item.item_name}';
            final controller = _scoreControllers[key];

            return Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          item.item_name,
                          style: const TextStyle(
                            color: Color(0xFF3A5A7A),
                            fontSize: 11,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                      SizedBox(
                        width: 70,
                        child: TextField(
                          controller: controller,
                          keyboardType: const TextInputType.numberWithOptions(
                              decimal: false),
                          textAlign: TextAlign.center,
                          decoration: InputDecoration(
                            filled: true,
                            fillColor: const Color(0xFFF7F9FF),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: Color(0xFFC8D8F0),
                                width: 0.5,
                              ),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: Color(0xFFC8D8F0),
                                width: 0.5,
                              ),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: Color(0xFF1A4BBB),
                                width: 1,
                              ),
                            ),
                            contentPadding:
                                const EdgeInsets.symmetric(vertical: 8),
                            suffixText: '/${item.max_score}',
                            suffixStyle: const TextStyle(
                              color: Color(0xFF8A9BBF),
                              fontSize: 11,
                            ),
                          ),
                          onChanged: (value) {
                            setState(() {});
                          },
                        ),
                      ),
                    ],
                  ),
                  if (index < category.items.length - 1)
                    const SizedBox(height: 0),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  String _getInitials(String name) {
    final names = name.split(' ');
    if (names.length >= 2) {
      return (names[0][0] + names[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  }
}
