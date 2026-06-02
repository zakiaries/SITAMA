import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/kaprodi/ui/bloc/kaprodi_cubit.dart';
import 'package:sitama/service_locator.dart';

class KaprodiDataMahasiswa extends StatefulWidget {
  const KaprodiDataMahasiswa({super.key});

  @override
  State<KaprodiDataMahasiswa> createState() => _KaprodiDataMahasiswaState();
}

class _KaprodiDataMahasiswaState extends State<KaprodiDataMahasiswa>
    with TickerProviderStateMixin {
  late TabController _tabController;

  static const _statusMap = ['all', 'aktif', 'belum_dospem', 'belum_magang', 'all', 'selesai'];
  int _activeTab = 0;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 6, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() => _activeTab = _tabController.index);
        _loadTab(_tabController.index);
      }
    });
    sl<KaprodiCubit>().loadMahasiswa();
  }

  void _loadTab(int index) {
    sl<KaprodiCubit>().loadMahasiswa(status: _statusMap[index]);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider.value(
      value: sl<KaprodiCubit>(),
      child: BlocListener<KaprodiCubit, KaprodiState>(
        listener: (context, state) {
          if (state is KaprodiAssignSuccess) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Dosen pembimbing berhasil ditugaskan'),
                backgroundColor: Color(0xFF1E6E3E),
              ),
            );
          } else if (state is KaprodiAssignError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Gagal: ${state.message}'),
                backgroundColor: Colors.red,
              ),
            );
          }
        },
        child: Scaffold(
        backgroundColor: const Color(0xFFF5F6FA),
        body: NestedScrollView(
          headerSliverBuilder: (context, innerBoxIsScrolled) => [
            SliverAppBar(
              backgroundColor: Colors.transparent,
              pinned: true,
              elevation: 0,
              shadowColor: Colors.transparent,
              surfaceTintColor: Colors.transparent,
              automaticallyImplyLeading: false,
              expandedHeight: 0,
              toolbarHeight: 140,
              flexibleSpace: ClipRRect(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 16),
                      const Text(
                        'Data Mahasiswa',
                        style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 16),
                      Container(
                        height: 40,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          color: Colors.white,
                          border: Border.all(color: const Color(0xFFE5E7EB)),
                        ),
                        child: TextField(
                          onSubmitted: (q) => sl<KaprodiCubit>().loadMahasiswa(
                            status: _statusMap[_tabController.index], search: q),
                          decoration: const InputDecoration(
                            hintText: 'Cari NIM atau nama mahasiswa...',
                            hintStyle: TextStyle(color: Color(0xFF9CA3AF), fontSize: 13),
                            prefixIcon: Icon(Icons.search, size: 18, color: Color(0xFF9CA3AF)),
                            contentPadding: EdgeInsets.symmetric(vertical: 0, horizontal: 12),
                            border: InputBorder.none,
                            isDense: true,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
          body: Column(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                child: SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterPill(0, 'Semua'),
                      const SizedBox(width: 10),
                      _buildFilterPill(1, 'Aktif'),
                      const SizedBox(width: 10),
                      _buildFilterPill(2, 'Belum Dospem'),
                      const SizedBox(width: 10),
                      _buildFilterPill(3, 'Belum Magang'),
                      const SizedBox(width: 10),
                      _buildFilterPill(4, 'Seminar'),
                      const SizedBox(width: 10),
                      _buildFilterPill(5, 'Selesai'),
                    ],
                  ),
                ),
              ),
              Expanded(
                child: BlocBuilder<KaprodiCubit, KaprodiState>(
                  builder: (context, state) {
                    if (state is KaprodiLoading) {
                      return const Center(child: CircularProgressIndicator());
                    }
                    if (state is KaprodiError) {
                      return Center(child: Text(state.message, style: const TextStyle(color: Colors.red)));
                    }
                    final students = state is KaprodiMahasiswaLoaded ? state.mahasiswa : [];
                    if (students.isEmpty) {
                      return const Center(
                        child: Text('Tidak ada data mahasiswa',
                            style: TextStyle(fontSize: 14, color: Color(0xFF8A9BC0))),
                      );
                    }
                    return ListView.separated(
                      padding: const EdgeInsets.all(14),
                      itemCount: students.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 10),
                      itemBuilder: (_, i) => _buildStudentCard(students[i] as Map<String, dynamic>),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    ),
  );
  }

  Future<void> _showAssignDosenDialog(BuildContext pageContext, int studentId, String studentName) async {
    int? selectedLecturerId;
    List<Map<String, dynamic>> dosenList = [];
    bool isLoading = true;
    String? errorMsg;

    await showDialog(
      context: pageContext,
      barrierDismissible: false,
      builder: (dialogContext) {
        return StatefulBuilder(
          builder: (_, setDialogState) {
            if (isLoading && dosenList.isEmpty && errorMsg == null) {
              sl<KaprodiCubit>().fetchDosenList().then((data) {
                setDialogState(() {
                  isLoading = false;
                  dosenList = List<Map<String, dynamic>>.from(data as List);
                });
              }).catchError((e) {
                setDialogState(() {
                  isLoading = false;
                  errorMsg = e.toString();
                });
              });
            }

            return AlertDialog(
              title: const Text('Tugaskan Dosen',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Mahasiswa: $studentName',
                      style: const TextStyle(fontSize: 13, color: Color(0xFF8A9BC0))),
                  const SizedBox(height: 16),
                  if (isLoading)
                    const Center(child: CircularProgressIndicator())
                  else if (errorMsg != null)
                    Text('Gagal memuat dosen: $errorMsg',
                        style: const TextStyle(color: Colors.red, fontSize: 12))
                  else
                    InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Pilih Dosen Pembimbing',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                      ),
                      child: DropdownButton<int>(
                        value: selectedLecturerId,
                        isExpanded: true,
                        underline: const SizedBox(),
                        hint: const Text('Pilih dosen...', style: TextStyle(fontSize: 13)),
                        items: dosenList.map((d) => DropdownMenuItem<int>(
                          value: d['id'] as int,
                          child: Text(d['name'] as String? ?? '',
                              style: const TextStyle(fontSize: 13)),
                        )).toList(),
                        onChanged: (val) => setDialogState(() => selectedLecturerId = val),
                      ),
                    ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(dialogContext),
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  onPressed: selectedLecturerId == null ? null : () {
                    Navigator.pop(dialogContext);
                    sl<KaprodiCubit>().assignLecturer(
                        studentId, selectedLecturerId!, _statusMap[_activeTab]);
                  },
                  style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1A1A3E)),
                  child: const Text('Tugaskan',
                      style: TextStyle(color: Colors.white)),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Widget _buildStudentCard(Map<String, dynamic> s) {
    final id       = s['id'] as int? ?? 0;
    final name     = s['name'] as String? ?? '';
    final nim      = s['nim']  as String? ?? '';
    final cls      = s['class'] as String? ?? '';
    final company  = s['company'] as String? ?? '-';
    final lecturer = s['lecturer'] as String? ?? '';
    final status   = s['status'] as String? ?? 'Aktif';
    final initials = name.trim().split(' ').take(2)
        .map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();

    Color statusBg, statusText;
    switch (status) {
      case 'Selesai':
        statusBg = const Color(0xFFE6F4EA); statusText = const Color(0xFF1E6E3E);
      case 'Belum Magang':
        statusBg = const Color(0xFFFFF8E1); statusText = const Color(0xFFF59E0B);
      default:
        statusBg = const Color(0xFFE8F0FE); statusText = const Color(0xFF1A3A8E);
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
      ),
      padding: const EdgeInsets.all(13),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 42, height: 42,
                decoration: BoxDecoration(color: statusBg, shape: BoxShape.circle),
                child: Center(
                  child: Text(initials,
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: statusText)),
                ),
              ),
              const SizedBox(width: 11),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF1A2050))),
                    const SizedBox(height: 2),
                    Text('$nim · $cls · $company',
                        style: const TextStyle(fontSize: 10, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w500)),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 3),
                decoration: BoxDecoration(color: statusBg, borderRadius: BorderRadius.circular(20)),
                child: Text(status, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: statusText)),
              ),
            ],
          ),
          const SizedBox(height: 9),
          Container(color: const Color(0xFFEEF0FA), height: 0.5),
          const SizedBox(height: 9),
          if (lecturer.isNotEmpty)
            Row(
              children: [
                const Text('Dospem: ', style: TextStyle(fontSize: 10, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w500)),
                Expanded(child: Text(lecturer, style: const TextStyle(fontSize: 10, color: Color(0xFF1A2050), fontWeight: FontWeight.w700))),
              ],
            )
          else
            Row(
              children: [
                const Expanded(
                  child: Text('Belum ada dosen pembimbing',
                      style: TextStyle(fontSize: 10, color: Color(0xFFE53E3E), fontStyle: FontStyle.italic)),
                ),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: () => _showAssignDosenDialog(context, id, name),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(color: const Color(0xFF1A1A3E), borderRadius: BorderRadius.circular(6)),
                    child: const Text('Tugaskan Dosen',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Colors.white)),
                  ),
                ),
              ],
            ),
        ],
      ),
    );
  }

  Widget _buildFilterPill(int index, String label) {
    return GestureDetector(
      onTap: () {
        setState(() => _tabController.animateTo(index));
        _loadTab(index);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: _activeTab == index ? const Color(0xFF1A1A3E) : Colors.white,
          border: Border.all(
            color: _activeTab == index ? const Color(0xFF1A1A3E) : const Color(0xFFC5CDE2),
            width: 1.2,
          ),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11, fontWeight: FontWeight.w700,
            color: _activeTab == index ? Colors.white : const Color(0xFF1A2050),
          ),
        ),
      ),
    );
  }
}
