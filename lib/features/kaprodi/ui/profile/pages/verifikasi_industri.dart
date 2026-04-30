import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/kaprodi/ui/bloc/kaprodi_cubit.dart';
import 'package:sitama/service_locator.dart';

class VerifikasiIndustriPage extends StatefulWidget {
  const VerifikasiIndustriPage({super.key});

  @override
  State<VerifikasiIndustriPage> createState() => _VerifikasiIndustriPageState();
}

class _VerifikasiIndustriPageState extends State<VerifikasiIndustriPage>
    with TickerProviderStateMixin {
  late TabController _tabController;

  static const _statusKeys   = ['pending', 'verified', 'rejected'];
  static const _tabColors    = [Color(0xFF7C3AED), Color(0xFF1E6E3E), Color(0xFF9B2F2F)];
  static const _tabBgColors  = [Color(0xFFF3E8FF), Color(0xFFE6F4EA), Color(0xFFFCE8E6)];
  static const _tabLabels    = ['Menunggu', 'Terverifikasi', 'Ditolak'];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        sl<KaprodiCubit>().loadIndustri(status: _statusKeys[_tabController.index]);
      }
    });
    sl<KaprodiCubit>().loadIndustri(status: 'pending');
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
      child: Scaffold(
        backgroundColor: const Color(0xFFF5F6FA),
        body: NestedScrollView(
          headerSliverBuilder: (context, _) => [
            SliverToBoxAdapter(
              child: ClipRRect(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
                child: Container(
                  color: const Color(0xFF1A1A3E),
                  padding: const EdgeInsets.fromLTRB(18, 12, 18, 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      SizedBox(height: MediaQuery.of(context).padding.top + 16),
                      const Text('Verifikasi Industri',
                          style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 16),
                      Container(
                        height: 40,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          color: Colors.white,
                          border: Border.all(color: const Color(0xFFE5E7EB)),
                        ),
                        child: TextField(
                          onSubmitted: (q) => sl<KaprodiCubit>().loadIndustri(
                            status: _statusKeys[_tabController.index], search: q),
                          decoration: const InputDecoration(
                            hintText: 'Cari nama perusahaan...',
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
                    children: List.generate(3, (i) => Padding(
                      padding: EdgeInsets.only(right: i < 2 ? 10 : 0),
                      child: _buildTabPill(i, _tabLabels[i]),
                    )),
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
                      return Center(child: Text(state.message,
                          style: const TextStyle(color: Colors.red)));
                    }

                    final tabIndex = _tabController.index;
                    final industri = state is KaprodiIndustriLoaded ? state.industri : [];
                    final color    = _tabColors[tabIndex];
                    final bgColor  = _tabBgColors[tabIndex];

                    return SingleChildScrollView(
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        children: [
                          // Banner
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            decoration: BoxDecoration(
                              color: bgColor,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 8, height: 8,
                                  decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(4)),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    '${industri.length} perusahaan ${_tabLabels[tabIndex].toLowerCase()}',
                                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: color),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),
                          if (industri.isEmpty)
                            Text('Tidak ada data', style: TextStyle(fontSize: 11, color: color))
                          else
                            ...industri.map((c) => Padding(
                              padding: const EdgeInsets.only(bottom: 12),
                              child: _buildIndustryCard(c as Map<String, dynamic>, tabIndex),
                            )),
                        ],
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildIndustryCard(Map<String, dynamic> c, int tabIndex) {
    final name    = c['name']      as String? ?? '';
    final field   = c['field']     as String? ?? '-';
    final pic     = c['pic_name']  as String? ?? '-';
    final email   = c['pic_email'] as String? ?? '-';
    final id      = c['id']        as int;
    final initials = name.trim().split(' ').take(2)
        .map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();

    final clr   = _tabColors[tabIndex];
    final bgClr = _tabBgColors[tabIndex];

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
            children: [
              Container(
                width: 40, height: 40,
                decoration: BoxDecoration(
                  color: clr.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: Text(initials,
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: clr)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(name,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF1A2050))),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(color: bgClr, borderRadius: BorderRadius.circular(6)),
                child: Text(_tabLabels[tabIndex],
                    style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: clr)),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(field, style: const TextStyle(fontSize: 10, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w500)),
          const SizedBox(height: 10),
          Container(color: const Color(0xFFEEF0FA), height: 0.5),
          const SizedBox(height: 10),
          _infoRow('PIC', pic),
          const SizedBox(height: 4),
          _infoRow('Email', email),
          if (tabIndex == 0) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => sl<KaprodiCubit>().rejectIndustri(id, 'pending'),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFCE8E6),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFEA4335)),
                      ),
                      child: const Text('Tolak', textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFFEA4335))),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: GestureDetector(
                    onTap: () => sl<KaprodiCubit>().verifyIndustri(id, 'pending'),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1A1A3E),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text('Verifikasi Akun', textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white)),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Row(
      children: [
        Text('$label: ', style: const TextStyle(fontSize: 10, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w500)),
        Expanded(child: Text(value,
            style: const TextStyle(fontSize: 10, color: Color(0xFF1A2050), fontWeight: FontWeight.w600),
            overflow: TextOverflow.ellipsis)),
      ],
    );
  }

  Widget _buildTabPill(int index, String label) {
    final active = _tabController.index == index;
    return GestureDetector(
      onTap: () {
        setState(() => _tabController.animateTo(index));
        sl<KaprodiCubit>().loadIndustri(status: _statusKeys[index]);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF1A1A3E) : Colors.white,
          border: Border.all(
            color: active ? const Color(0xFF1A1A3E) : const Color(0xFFC5CDE2),
            width: 1.2,
          ),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(label,
            style: TextStyle(
              fontSize: 11, fontWeight: FontWeight.w700,
              color: active ? Colors.white : const Color(0xFF1A2050),
            )),
      ),
    );
  }
}
