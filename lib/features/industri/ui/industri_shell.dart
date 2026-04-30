import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/industri/ui/bloc/industri_cubit.dart';
import 'package:sitama/service_locator.dart';
import 'lowongan/pages/industri_lowongan.dart';
import 'edit_profil_perusahaan.dart';
import 'kelola_pembimbing_industri.dart';

class IndustriShell extends StatefulWidget {
  const IndustriShell({super.key});

  @override
  State<IndustriShell> createState() => _IndustriShellState();
}

class _IndustriShellState extends State<IndustriShell> {
  int _selectedIndex = 0;

  late final List<Widget> _pages;

  @override
  void initState() {
    super.initState();
    _pages = const [
      IndustriHome(),
      IndustriLowongan(),
      IndustriPelamar(),
      IndustriProfile(),
    ];
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider.value(
      value: sl<IndustriCubit>(),
      child: Scaffold(
        body: _pages[_selectedIndex],
        bottomNavigationBar: BottomNavigationBar(
          currentIndex: _selectedIndex,
          onTap: (i) => setState(() => _selectedIndex = i),
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: const Color(0xFF388E3C),
          unselectedItemColor: const Color(0xFFB0BDD4),
          items: const [
            BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
            BottomNavigationBarItem(icon: Icon(Icons.work), label: 'Lowongan'),
            BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Pelamar'),
            BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profil'),
          ],
        ),
      ),
    );
  }
}

// ─── HOME ────────────────────────────────────────────────────────────────────

class IndustriHome extends StatefulWidget {
  const IndustriHome({super.key});

  @override
  State<IndustriHome> createState() => _IndustriHomeState();
}

class _IndustriHomeState extends State<IndustriHome> {
  @override
  void initState() {
    super.initState();
    sl<IndustriCubit>().loadProfile();
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<IndustriCubit, IndustriState>(
      builder: (context, state) {
        final profile   = state is IndustriProfileLoaded ? state.profile : <String, dynamic>{};
        final isLoading = state is IndustriLoading;

        final name       = profile['name']      as String? ?? 'Perusahaan';
        final address    = profile['address']   as String? ?? '-';
        final verified   = (profile['verification_status'] as String?) == 'verified';
        final picName    = profile['pic_name']  as String? ?? '-';
        final activeLow  = profile['active_lowongan']    as int? ?? 0;
        final activeInt  = profile['active_internships'] as int? ?? 0;

        final initials = name.trim().split(' ').take(2)
            .map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();

        return Scaffold(
          backgroundColor: const Color(0xFFF5F6FA),
          body: isLoading
              ? const Center(child: CircularProgressIndicator())
              : NestedScrollView(
                  headerSliverBuilder: (context, _) => [
                    SliverToBoxAdapter(
                      child: ClipRRect(
                        borderRadius: const BorderRadius.only(
                          bottomLeft: Radius.circular(28),
                          bottomRight: Radius.circular(28),
                        ),
                        child: Container(
                          color: const Color(0xFF1A1A3E),
                          padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              SizedBox(height: MediaQuery.of(context).padding.top + 4),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text('Mitra Industri',
                                          style: TextStyle(color: Color(0xFFB0BDD4), fontSize: 11, fontWeight: FontWeight.w500)),
                                      const SizedBox(height: 4),
                                      Text(name, style: const TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.w700)),
                                      const SizedBox(height: 2),
                                      Text(address,
                                          style: const TextStyle(color: Color(0xFFB0BDD4), fontSize: 11, fontWeight: FontWeight.w500)),
                                    ],
                                  ),
                                  Container(
                                    width: 50, height: 50,
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF3D5AF1),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Center(
                                      child: Text(initials,
                                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Colors.white)),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
                                decoration: BoxDecoration(
                                  color: verified ? const Color(0xFFE6F4EA) : const Color(0xFFFFF8E1),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(
                                    color: verified ? const Color(0xFF388E3C) : const Color(0xFFE6A400),
                                    width: 0.5,
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text('●', style: TextStyle(fontSize: 10,
                                        color: verified ? const Color(0xFF388E3C) : const Color(0xFFE6A400))),
                                    const SizedBox(width: 6),
                                    Text(
                                      verified ? 'Akun terverifikasi oleh Kaprodi' : 'Menunggu verifikasi',
                                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
                                          color: verified ? const Color(0xFF388E3C) : const Color(0xFFE6A400)),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                  body: SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        GridView.count(
                          crossAxisCount: 2, shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          mainAxisSpacing: 12, crossAxisSpacing: 12, childAspectRatio: 1.4,
                          children: [
                            _statCard('$activeLow', 'Lowongan Aktif', 'Aktif saat ini ›', const Color(0xFF1A3A8E)),
                            _statCard('$activeInt', 'Mahasiswa Magang', 'Magang aktif ›', const Color(0xFFE6A400)),
                          ],
                        ),
                        const SizedBox(height: 20),
                        const Text('Aksi Cepat', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF1A2050))),
                        const SizedBox(height: 12),
                        GridView.count(
                          crossAxisCount: 2, shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          mainAxisSpacing: 12, crossAxisSpacing: 12, childAspectRatio: 1.3,
                          children: [
                            _actionCard('Buat Lowongan', 'Posting baru', Icons.add_circle_outline, const Color(0xFF388E3C)),
                            _actionCard('Review Pelamar', 'Cek pelamar baru', Icons.rate_review_outlined, const Color(0xFFE6A400)),
                          ],
                        ),
                        const SizedBox(height: 20),
                        const Text('Info', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF1A2050))),
                        const SizedBox(height: 8),
                        Text('PIC: $picName', style: const TextStyle(fontSize: 12, color: Color(0xFF8A9BC0))),
                      ],
                    ),
                  ),
                ),
        );
      },
    );
  }

  Widget _statCard(String value, String label, String subtitle, Color color) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: color), textAlign: TextAlign.center),
          const SizedBox(height: 4),
          Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF1A2050), fontWeight: FontWeight.w600), textAlign: TextAlign.center),
          const SizedBox(height: 6),
          Text(subtitle, style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.w600), textAlign: TextAlign.center),
        ],
      ),
    );
  }

  Widget _actionCard(String title, String subtitle, IconData icon, Color color) {
    return Container(
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08), borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.3), width: 0.5),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color, size: 28),
          const SizedBox(height: 8),
          Text(title, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: color), textAlign: TextAlign.center),
          const SizedBox(height: 3),
          Text(subtitle, style: TextStyle(fontSize: 10, color: color.withValues(alpha: 0.7), fontWeight: FontWeight.w500), textAlign: TextAlign.center),
        ],
      ),
    );
  }
}

// ─── PELAMAR ─────────────────────────────────────────────────────────────────

class IndustriPelamar extends StatefulWidget {
  const IndustriPelamar({super.key});

  @override
  State<IndustriPelamar> createState() => _IndustriPelamarState();
}

class _IndustriPelamarState extends State<IndustriPelamar>
    with TickerProviderStateMixin {
  late TabController _tabController;
  int _tabIndex = 0;

  static const _statusKeys = ['pending', 'accepted', 'rejected'];
  static const _tabLabels  = ['Menunggu', 'Diterima', 'Ditolak'];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() => _tabIndex = _tabController.index);
        sl<IndustriCubit>().loadPelamar(status: _statusKeys[_tabController.index]);
      }
    });
    sl<IndustriCubit>().loadPelamar(status: 'pending');
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      body: NestedScrollView(
        headerSliverBuilder: (context, _) => [
          SliverToBoxAdapter(
            child: ClipRRect(
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(28), bottomRight: Radius.circular(28),
              ),
              child: Container(
                color: const Color(0xFF1A1A3E),
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    SizedBox(height: MediaQuery.of(context).padding.top + 16),
                    const Text('Review Pelamar',
                        style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 16),
                    Container(
                      height: 40,
                      decoration: BoxDecoration(borderRadius: BorderRadius.circular(12), color: Colors.white),
                      child: TextField(
                        onSubmitted: (q) => sl<IndustriCubit>().loadPelamar(
                            status: _statusKeys[_tabIndex], search: q),
                        decoration: const InputDecoration(
                          hintText: 'Cari nama atau NIM...',
                          hintStyle: TextStyle(color: Color(0xFF8A9BC0), fontSize: 13),
                          prefixIcon: Icon(Icons.search, size: 18, color: Color(0xFF8A9BC0)),
                          contentPadding: EdgeInsets.symmetric(vertical: 0, horizontal: 12),
                          border: InputBorder.none, isDense: true,
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
              child: Row(
                children: List.generate(3, (i) => Padding(
                  padding: EdgeInsets.only(right: i < 2 ? 8 : 0),
                  child: _tabPill(i),
                )),
              ),
            ),
            BlocBuilder<IndustriCubit, IndustriState>(
              builder: (context, state) {
                if (state is IndustriLoading) {
                  return const Expanded(child: Center(child: CircularProgressIndicator()));
                }
                final pelamar = state is IndustriPelamarLoaded ? state.pelamar : [];

                if (_tabIndex == 0 && pelamar.isNotEmpty) {
                  return Container(
                    margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFFF8E1), borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFE6A400), width: 0.5),
                    ),
                    child: Row(
                      children: [
                        const Text('●', style: TextStyle(fontSize: 12, color: Color(0xFFE6A400))),
                        const SizedBox(width: 8),
                        Text('${pelamar.length} pelamar menunggu keputusan penerimaan',
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFFE6A400))),
                      ],
                    ),
                  );
                }
                return const SizedBox.shrink();
              },
            ),
            Expanded(
              child: BlocBuilder<IndustriCubit, IndustriState>(
                builder: (context, state) {
                  if (state is IndustriLoading) return const Center(child: CircularProgressIndicator());
                  final pelamar = state is IndustriPelamarLoaded ? state.pelamar : [];
                  if (pelamar.isEmpty) {
                    return const Center(
                        child: Text('Tidak ada pelamar', style: TextStyle(fontSize: 14, color: Color(0xFF8A9BC0))));
                  }
                  return ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: pelamar.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (_, i) => _pelamarCard(pelamar[i] as Map<String, dynamic>),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _pelamarCard(Map<String, dynamic> p) {
    final student  = p['student'] as Map<String, dynamic>? ?? {};
    final name     = student['name'] as String? ?? '';
    final nim      = student['nim']  as String? ?? '';
    final cls      = student['class'] as String? ?? '';
    final major    = student['major'] as String? ?? '';
    final jobTitle = p['job_title'] as String? ?? '';
    final appliedAt = p['applied_at'] as String? ?? '';
    final status   = p['status']   as String? ?? 'pending';
    final id       = p['id'] as int;
    final initials = name.trim().split(' ').take(2)
        .map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();

    const colors = [Color(0xFF388E3C), Color(0xFF1A3A8E), Color(0xFFE53E3E)];
    final clr = colors[_tabIndex % colors.length];

    return Container(
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: BorderRadius.circular(14),
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
                decoration: BoxDecoration(
                  color: clr.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(10),
                ),
                child: Center(child: Text(initials,
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: clr))),
              ),
              const SizedBox(width: 11),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF1A2050))),
                    const SizedBox(height: 2),
                    Text('$nim · $cls · $major',
                        style: const TextStyle(fontSize: 9, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w500)),
                    const SizedBox(height: 2),
                    Text('Daftar: $appliedAt',
                        style: const TextStyle(fontSize: 9, color: Color(0xFFB0BDD4))),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                decoration: BoxDecoration(color: clr.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)),
                child: Text(_tabLabels[_tabIndex],
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: clr)),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(jobTitle, style: const TextStyle(fontSize: 10, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w500)),
          if (status == 'pending') ...[
            const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                ElevatedButton(
                  onPressed: () => sl<IndustriCubit>().acceptPelamar(id, 'pending'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE6F4EA), elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                  ),
                  child: const Text('Terima',
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF388E3C))),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: () => sl<IndustriCubit>().rejectPelamar(id, 'pending'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFFFE6F0), elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                  ),
                  child: const Text('Tolak',
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFFC41E3A))),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _tabPill(int index) {
    final active = _tabIndex == index;
    return GestureDetector(
      onTap: () {
        _tabController.animateTo(index);
        setState(() => _tabIndex = index);
        sl<IndustriCubit>().loadPelamar(status: _statusKeys[index]);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF1A1A3E) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: active ? null : Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
        ),
        child: Text(_tabLabels[index],
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
                color: active ? Colors.white : const Color(0xFF8A9BC0))),
      ),
    );
  }
}

// ─── PROFILE ─────────────────────────────────────────────────────────────────

class IndustriProfile extends StatefulWidget {
  const IndustriProfile({super.key});

  @override
  State<IndustriProfile> createState() => _IndustriProfileState();
}

class _IndustriProfileState extends State<IndustriProfile> {
  @override
  void initState() {
    super.initState();
    sl<IndustriCubit>().loadProfile();
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<IndustriCubit, IndustriState>(
      builder: (context, state) {
        final profile = state is IndustriProfileLoaded ? state.profile : <String, dynamic>{};

        final name       = profile['name']      as String? ?? 'Perusahaan';
        final address    = profile['address']   as String? ?? '-';
        final picName    = profile['pic_name']  as String? ?? '-';
        final verified   = (profile['verification_status'] as String?) == 'verified';
        final activeLow  = profile['active_lowongan']    as int? ?? 0;
        final activeInt  = profile['active_internships'] as int? ?? 0;
        final totalAcc   = profile['total_accepted']     as int? ?? 0;

        final initials = name.trim().split(' ').take(2)
            .map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join();

        return Scaffold(
          backgroundColor: const Color(0xFFF5F6FA),
          body: NestedScrollView(
            headerSliverBuilder: (context, _) => [
              SliverToBoxAdapter(
                child: ClipRRect(
                  borderRadius: const BorderRadius.only(
                    bottomLeft: Radius.circular(28), bottomRight: Radius.circular(28),
                  ),
                  child: Container(
                    color: const Color(0xFF1A1A3E),
                    padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        SizedBox(height: MediaQuery.of(context).padding.top + 4),
                        Container(
                          width: 80, height: 80,
                          decoration: const BoxDecoration(color: Color(0xFF3D5AF1), shape: BoxShape.circle),
                          child: Center(child: Text(initials,
                              style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: Colors.white))),
                        ),
                        const SizedBox(height: 12),
                        Text(name, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 4),
                        Text(address, style: const TextStyle(color: Color(0xFFB0BDD4), fontSize: 11, fontWeight: FontWeight.w500)),
                        const SizedBox(height: 2),
                        Text('PIC: $picName', style: const TextStyle(color: Color(0xFFB0BDD4), fontSize: 11, fontWeight: FontWeight.w500)),
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
                          decoration: BoxDecoration(
                            color: verified ? const Color(0xFFE6F4EA) : const Color(0xFFFFF8E1),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: verified ? const Color(0xFF388E3C) : const Color(0xFFE6A400), width: 0.5),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(verified ? '✓' : '●',
                                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700,
                                      color: verified ? const Color(0xFF388E3C) : const Color(0xFFE6A400))),
                              const SizedBox(width: 6),
                              Text(verified ? 'Akun Terverifikasi' : 'Menunggu Verifikasi',
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
                                      color: verified ? const Color(0xFF388E3C) : const Color(0xFFE6A400))),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
            body: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  GridView.count(
                    crossAxisCount: 2, shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    mainAxisSpacing: 12, crossAxisSpacing: 12, childAspectRatio: 1.6,
                    children: [
                      _statCard('$activeLow lowongan', 'Lowongan Aktif', const Color(0xFF1A3A8E)),
                      _statCard('$activeInt aktif',    'Mahasiswa Magang', const Color(0xFF1A3A8E)),
                      _statCard('$totalAcc orang',     'Total Diterima', const Color(0xFF8A9BC0)),
                    ],
                  ),
                  const SizedBox(height: 24),
                  const Text('INFORMASI PERUSAHAAN',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFFB0BDD4), letterSpacing: 0.5)),
                  const SizedBox(height: 12),
                  _menuCard('Edit Profil Perusahaan', Icons.business, const Color(0xFF085041), const Color(0xFFE1F5EE),
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const EditProfilPerusahaan()))),
                  const SizedBox(height: 10),
                  _menuCard('Kelola Pembimbing Industri', Icons.people_outline, const Color(0xFF085041), const Color(0xFFE1F5EE),
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KelolaPembimbingIndustri()))),
                  const SizedBox(height: 24),
                  const Text('AKUN',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFFB0BDD4), letterSpacing: 0.5)),
                  const SizedBox(height: 12),
                  _menuCard('Ganti Password', Icons.lock_outline, const Color(0xFF1A3A8E), const Color(0xFFE8F0FE)),
                  const SizedBox(height: 10),
                  _menuCard('Notifikasi', Icons.notifications_outlined, const Color(0xFF1A3A8E), const Color(0xFFE8F0FE)),
                  const SizedBox(height: 10),
                  _menuCard('Logout', Icons.logout, const Color(0xFFC41E3A), const Color(0xFFFFE6F0), isLogout: true),
                  const SizedBox(height: 20),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _statCard(String value, String label, Color color) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white, borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(value, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: color)),
          const SizedBox(height: 6),
          Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF8A9BC0), fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }

  Widget _menuCard(String title, IconData icon, Color iconColor, Color bgColor,
      {bool isLogout = false, VoidCallback? onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white, borderRadius: BorderRadius.circular(13),
          border: Border.all(color: const Color(0xFFD0D6EB), width: 0.5),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 13),
        child: Row(
          children: [
            Container(
              width: 34, height: 34,
              decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(10)),
              child: Center(child: Icon(icon, size: 18, color: iconColor)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(title,
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600,
                      color: isLogout ? const Color(0xFFC41E3A) : const Color(0xFF1A2050))),
            ),
            const Icon(Icons.arrow_forward_ios, size: 14, color: Color(0xFFD0D6EB)),
          ],
        ),
      ),
    );
  }
}
