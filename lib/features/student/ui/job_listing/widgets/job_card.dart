import 'package:flutter/material.dart';
import 'package:sitama/features/student/domain/entities/job_listing_entity.dart';

class Member {
  final String nim;
  final String name;
  final String role; // 'leader' or 'member'

  Member({
    required this.nim,
    required this.name,
    required this.role,
  });
}

class JobCard extends StatefulWidget {
  final JobListingEntity job;
  final VoidCallback onApply;

  const JobCard({
    super.key,
    required this.job,
    required this.onApply,
  });

  @override
  State<JobCard> createState() => _JobCardState();
}

class _JobCardState extends State<JobCard> {
  // State variables
  bool isRegistered = false;
  bool showModePanel = false;
  String? selectedMode; // 'solo' | 'grup' | null
  bool soloConfirmed = false;
  List<Member> members = [];
  bool expandedSkills = false;

  // Controllers
  final TextEditingController _nimController = TextEditingController();
  bool _showInviteError = false;
  String _inviteErrorMessage = '';

  // User data (TODO: Replace with actual auth provider)
  final String currentUserNim = '220211084';
  final String currentUserName = 'Nama Mahasiswa';

  @override
  void initState() {
    super.initState();
    // Initialize members with current user as leader
    members = [
      Member(nim: currentUserNim, name: currentUserName, role: 'leader'),
    ];
  }

  @override
  void dispose() {
    _nimController.dispose();
    super.dispose();
  }

  Color _getColorFromLogo(String logo) {
    final colors = {
      'TI': Color(0xFF4A90E2),
      'BN': Color(0xFF52C77A),
      'GK': Color(0xFF9B59B6),
      'TP': Color(0xFFE8A87C),
      'BL': Color(0xFFF5A623),
      'OV': Color(0xFF1E90FF),
    };
    return colors[logo] ?? Color(0xFF4A90E2);
  }

  String _getInitials(String name) {
    return name.split(' ').take(2).map((word) => word[0].toUpperCase()).join();
  }

  void _handleRegister() {
    setState(() {
      isRegistered = true;
      showModePanel = true;
    });
  }

  void _handleModeSelection(String mode) {
    setState(() {
      selectedMode = mode;
      showModePanel = false;
    });

    if (mode == 'solo') {
      _showSoloConfirmationDialog();
    } else if (mode == 'grup') {
      _showGroupDialog();
    }
  }

  void _handleBackFromMode() {
    setState(() {
      selectedMode = null;
      showModePanel = true;
    });
  }

  void _showSoloConfirmationDialog() {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        child: Container(
          padding: EdgeInsets.all(20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Konfirmasi Pendaftaran Solo',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1E3A6E),
                  ),
                ),
                SizedBox(height: 16),
                // User Card
                Container(
                  padding: EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border.all(color: Color(0xFFDDDDDD), width: 1.5),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: Color(0xFFDCE8F8),
                          shape: BoxShape.circle,
                        ),
                        child: Center(
                          child: Text(
                            _getInitials(currentUserName),
                            style: TextStyle(
                              color: Color(0xFF1E3A6E),
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                          ),
                        ),
                      ),
                      SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              currentUserName,
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF333333),
                              ),
                            ),
                            Text(
                              currentUserNim,
                              style: TextStyle(
                                fontSize: 12,
                                color: Color(0xFF888888),
                              ),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: Color(0xFF1E3A6E),
                          borderRadius: BorderRadius.circular(5),
                        ),
                        child: Text(
                          'Solo',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                SizedBox(height: 16),
                // Warning
                Container(
                  padding: EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Color(0xFFFFF3E0),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Color(0xFFFFE0B2)),
                  ),
                  child: Text(
                    'Kamu akan mendaftar sebagai peserta magang individu.\nTidak bisa tambah anggota setelah konfirmasi.',
                    style: TextStyle(
                      fontSize: 13,
                      color: Color(0xFFE65100),
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
                SizedBox(height: 20),
                // Buttons
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () {
                          Navigator.pop(context);
                          _handleBackFromMode();
                        },
                        style: OutlinedButton.styleFrom(
                          side: BorderSide(
                            color: Color(0xFFDDDDDD),
                            width: 1.5,
                          ),
                          foregroundColor: Color(0xFF555555),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(9),
                          ),
                          padding: EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 12,
                          ),
                        ),
                        child: Text(
                          '← Kembali',
                          style: TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                    SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () {
                          Navigator.pop(context);
                          _handleSoloConfirm();
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Color(0xFF1E3A6E),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(9),
                          ),
                          padding: EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 12,
                          ),
                        ),
                        child: Text(
                          '✓ Konfirmasi',
                          style: TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showGroupDialog() {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        child: Container(
          padding: EdgeInsets.all(20),
          child: SingleChildScrollView(
            child: StatefulBuilder(
              builder: (context, setStateDialog) => Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header
                  Row(
                    children: [
                      Icon(Icons.people, color: Color(0xFF1E3A6E), size: 18),
                      SizedBox(width: 8),
                      Text(
                        'Kelompok Magang',
                        style: TextStyle(
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1E3A6E),
                          fontSize: 16,
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: 16),

                  // Members List
                  ...members.map((member) {
                    return Padding(
                      padding: EdgeInsets.only(bottom: 10),
                      child: Row(
                        children: [
                          Container(
                            width: 40,
                            height: 40,
                            decoration: BoxDecoration(
                              color: Color(0xFFDCE8F8),
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: Text(
                                _getInitials(member.name),
                                style: TextStyle(
                                  color: Color(0xFF1E3A6E),
                                  fontWeight: FontWeight.w600,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                          ),
                          SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  member.name,
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF333333),
                                  ),
                                ),
                                Text(
                                  member.nim,
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: Color(0xFF888888),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: member.role == 'leader'
                                  ? Color(0xFF1E3A6E)
                                  : Color(0xFFF0F0F0),
                              borderRadius: BorderRadius.circular(5),
                            ),
                            child: Text(
                              member.role == 'leader' ? 'Ketua' : 'Anggota',
                              style: TextStyle(
                                color: member.role == 'leader'
                                    ? Colors.white
                                    : Color(0xFF444444),
                                fontSize: 10,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                          if (member.role != 'leader')
                            Padding(
                              padding: EdgeInsets.only(left: 4),
                              child: GestureDetector(
                                onTap: () {
                                  setStateDialog(() {
                                    _removeGroupMember(member.nim);
                                  });
                                },
                                child: Container(
                                  width: 24,
                                  height: 24,
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    border: Border.all(
                                      color: Color(0xFFE74C3C),
                                      width: 1.5,
                                    ),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Center(
                                    child: Text(
                                      '✕',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: Color(0xFFE74C3C),
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
                    );
                  }),

                  // Invite Form - Show if not full
                  if (members.length < 2) ...[
                    SizedBox(height: 16),
                    TextField(
                      controller: _nimController,
                      style: TextStyle(
                        fontSize: 13,
                        color: Color(0xFF333333),
                      ),
                      decoration: InputDecoration(
                        hintText: 'Masukkan NIM anggota...',
                        hintStyle: TextStyle(
                          color: Colors.grey[500],
                          fontSize: 13,
                        ),
                        filled: true,
                        fillColor: Colors.white,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: BorderSide(
                            color: _showInviteError
                                ? Color(0xFFE74C3C)
                                : Color(0xFFDDDDDD),
                            width: 1,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: BorderSide(
                            color: _showInviteError
                                ? Color(0xFFE74C3C)
                                : Color(0xFFDDDDDD),
                            width: 1,
                          ),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                          borderSide: BorderSide(
                            color: _showInviteError
                                ? Color(0xFFE74C3C)
                                : Color(0xFF1E3A6E),
                            width: 1.5,
                          ),
                        ),
                        contentPadding: EdgeInsets.symmetric(
                          horizontal: 11,
                          vertical: 9,
                        ),
                        errorText:
                            _showInviteError ? _inviteErrorMessage : null,
                      ),
                    ),
                    SizedBox(height: 8),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () {
                          _addGroupMember();
                          setStateDialog(() {});
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Color(0xFF1E3A6E),
                          foregroundColor: Colors.white,
                          padding: EdgeInsets.symmetric(vertical: 9),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        child: Text(
                          'Invite',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ),
                  ] else ...[
                    SizedBox(height: 16),
                    Container(
                      width: double.infinity,
                      padding: EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Color(0xFFFFF3E0),
                        border: Border.all(
                          color: Color(0xFFFFE0B2),
                          width: 1.5,
                        ),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Kelompok sudah penuh — maks. 1 anggota',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 13,
                          color: Color(0xFFE65100),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],

                  SizedBox(height: 20),
                  // Buttons
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () {
                            Navigator.pop(context);
                            _handleBackFromMode();
                          },
                          style: OutlinedButton.styleFrom(
                            side: BorderSide(
                              color: Color(0xFFDDDDDD),
                              width: 1.5,
                            ),
                            foregroundColor: Color(0xFF555555),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(9),
                            ),
                            padding: EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 12,
                            ),
                          ),
                          child: Text(
                            '← Kembali',
                            style: TextStyle(fontWeight: FontWeight.w700),
                          ),
                        ),
                      ),
                      SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: () {
                            Navigator.pop(context);
                            _handleGroupConfirm();
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Color(0xFF1E3A6E),
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(9),
                            ),
                            padding: EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 12,
                            ),
                          ),
                          child: Text(
                            '✓ Konfirmasi',
                            style: TextStyle(fontWeight: FontWeight.w700),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _handleSoloConfirm() {
    setState(() {
      soloConfirmed = true;
      showModePanel = false;
      selectedMode = null;
    });
  }

  void _handleGroupConfirm() {
    // TODO: Submit group registration
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Konfirmasi grup dengan ${members.length} anggota'),
        backgroundColor: Color(0xFF1E3A6E),
      ),
    );
  }

  void _addGroupMember() {
    final nim = _nimController.text.trim();

    // Validation
    if (nim.isEmpty) {
      setState(() {
        _showInviteError = true;
        _inviteErrorMessage = 'NIM tidak boleh kosong';
      });
      Future.delayed(Duration(seconds: 2), () {
        if (mounted) {
          setState(() {
            _showInviteError = false;
          });
        }
      });
      return;
    }

    // Check if already exists
    if (members.any((m) => m.nim == nim)) {
      setState(() {
        _showInviteError = true;
        _inviteErrorMessage = 'Sudah ada di kelompok';
      });
      Future.delayed(Duration(seconds: 2), () {
        if (mounted) {
          setState(() {
            _showInviteError = false;
          });
        }
      });
      return;
    }

    // Check max members (leader + 1 member = 2)
    if (members.length >= 2) {
      setState(() {
        _showInviteError = true;
        _inviteErrorMessage = 'Kelompok sudah penuh';
      });
      return;
    }

    setState(() {
      members.add(
        Member(
          nim: nim,
          name: 'Anggota $nim', // TODO: Get actual name from backend
          role: 'member',
        ),
      );
      _nimController.clear();
      _showInviteError = false;
    });
  }

  void _removeGroupMember(String nim) {
    setState(() {
      members.removeWhere((m) => m.nim == nim);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.only(bottom: 0),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey[200]!, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 10,
            offset: Offset(0, 3),
          )
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Success Banner
          if (isRegistered && !soloConfirmed)
            Container(
              width: double.infinity,
              padding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: Color(0xFFE8F5E9),
                border: Border(
                  bottom: BorderSide(color: Color(0xFFC8E6C9), width: 2),
                ),
              ),
              child: Text(
                'Berhasil mendaftar! Pilih mode magang kamu.',
                style: TextStyle(
                  color: Color(0xFF1B5E20),
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ),

          // Solo Confirmed Banner
          if (soloConfirmed)
            Container(
              width: double.infinity,
              padding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: Color(0xFFF0F4FF),
                border: Border(
                  top: BorderSide(color: Color(0xFFDCE8F8), width: 2),
                ),
              ),
              child: Text(
                'Kamu terdaftar sebagai peserta magang Solo.',
                style: TextStyle(
                  color: Color(0xFF1E3A6E),
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ),

          // Card Content
          Padding(
            padding: EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header Row - Logo + Details + Badge
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Logo Container
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: _getColorFromLogo(widget.job.companyLogo),
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: _getColorFromLogo(widget.job.companyLogo)
                                .withValues(alpha: 0.2),
                            blurRadius: 8,
                            offset: Offset(0, 2),
                          )
                        ],
                      ),
                      child: Center(
                        child: Text(
                          widget.job.companyLogo,
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                            fontSize: 16,
                          ),
                        ),
                      ),
                    ),
                    SizedBox(width: 14),
                    // Job Details
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              Expanded(
                                child: Text(
                                  widget.job.position,
                                  style: TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFF1E3A8A),
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              if (widget.job.isNew)
                                Padding(
                                  padding: EdgeInsets.only(left: 8),
                                  child: Container(
                                    padding: EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 4,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Color(0xFFE8F5E9),
                                      borderRadius: BorderRadius.circular(6),
                                      border: Border.all(
                                        color: Color(0xFFC8E6C9),
                                      ),
                                    ),
                                    child: Text(
                                      'Baru',
                                      style: TextStyle(
                                        color: Color(0xFF2E7D32),
                                        fontSize: 10,
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          SizedBox(height: 6),
                          Row(
                            children: [
                              Icon(Icons.business,
                                  size: 13, color: Colors.grey[500]),
                              SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  widget.job.company,
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Color(0xFF888888),
                                    fontWeight: FontWeight.w500,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                SizedBox(height: 14),

                // Skills Tags
                if (widget.job.skills.isNotEmpty)
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: (expandedSkills
                                ? widget.job.skills
                                : widget.job.skills.take(3))
                            .map((skill) {
                          return Container(
                            padding: EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: Color(0xFFF4F5F7),
                              borderRadius: BorderRadius.circular(5),
                              border: Border.all(
                                color: Color(0xFFE8E8E8),
                              ),
                            ),
                            child: Text(
                              skill,
                              style: TextStyle(
                                fontSize: 11,
                                color: Color(0xFF333333),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                      if (widget.job.skills.length > 3)
                        GestureDetector(
                          onTap: () {
                            setState(() {
                              expandedSkills = !expandedSkills;
                            });
                          },
                          child: Padding(
                            padding: EdgeInsets.only(top: 8),
                            child: Text(
                              expandedSkills
                                  ? '- Sembunyikan'
                                  : '+${widget.job.skills.length - 3} keahlian lainnya',
                              style: TextStyle(
                                fontSize: 11,
                                color: Color(0xFF1E3A6E),
                                fontWeight: FontWeight.w600,
                                decoration: TextDecoration.underline,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),

                SizedBox(height: 14),

                // Divider
                Container(
                  height: 1,
                  color: Color(0xFFF0F0F0),
                ),

                SizedBox(height: 14),

                // Footer - Location + Button
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Row(
                        children: [
                          Icon(
                            Icons.location_on,
                            size: 16,
                            color: Color(0xFF888888),
                          ),
                          SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              widget.job.location,
                              style: TextStyle(
                                fontSize: 12,
                                color: Color(0xFF888888),
                                fontWeight: FontWeight.w600,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                    SizedBox(width: 12),
                    // Register Button
                    if (!isRegistered)
                      ElevatedButton(
                        onPressed: _handleRegister,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Color(0xFF1E3A6E),
                          foregroundColor: Colors.white,
                          padding: EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 9,
                          ),
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(9),
                          ),
                        ),
                        child: Text(
                          '⊕ Daftar',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      )
                    else
                      ElevatedButton(
                        onPressed: null,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Color(0xFF1B5E20),
                          disabledBackgroundColor: Color(0xFF1B5E20),
                          disabledForegroundColor: Colors.white,
                          padding: EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 9,
                          ),
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(9),
                          ),
                        ),
                        child: Text(
                          '✓ Terdaftar',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                  ],
                ),
              ],
            ),
          ),

          // Mode Selection Panel
          if (isRegistered && !soloConfirmed && showModePanel)
            Container(
              width: double.infinity,
              padding: EdgeInsets.all(16),
              decoration: BoxDecoration(
                border: Border(
                  top: BorderSide(color: Color(0xFFE8E8E8), width: 1),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Pilih Mode Magang',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1E3A6E),
                    ),
                  ),
                  SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: GestureDetector(
                          onTap: () => _handleModeSelection('solo'),
                          child: Container(
                            padding: EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              border: Border.all(
                                color: Color(0xFFE0E0E0),
                                width: 1.5,
                              ),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  '🧑‍💻',
                                  style: TextStyle(fontSize: 20),
                                ),
                                SizedBox(height: 8),
                                Text(
                                  'Solo',
                                  style: TextStyle(
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF333333),
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                      SizedBox(width: 12),
                      Expanded(
                        child: GestureDetector(
                          onTap: () => _handleModeSelection('grup'),
                          child: Container(
                            padding: EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              border: Border.all(
                                color: Color(0xFFE0E0E0),
                                width: 1.5,
                              ),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  '👥',
                                  style: TextStyle(fontSize: 20),
                                ),
                                SizedBox(height: 8),
                                Text(
                                  'Ajak Teman',
                                  style: TextStyle(
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF333333),
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
