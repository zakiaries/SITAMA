import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:sitama/features/student/ui/job_listing/bloc/job_listing_cubit.dart';
import 'package:sitama/features/student/ui/job_listing/widgets/job_card.dart';
import 'package:sitama/features/student/ui/job_listing/widgets/ai_recommendation_card.dart';
import 'package:sitama/service_locator.dart';

class JobListingPage extends StatefulWidget {
  const JobListingPage({Key? key}) : super(key: key);

  @override
  State<JobListingPage> createState() => _JobListingPageState();
}

class _JobListingPageState extends State<JobListingPage> {
  late JobListingCubit _jobListingCubit;

  @override
  void initState() {
    super.initState();
    _jobListingCubit = sl<JobListingCubit>();
    _jobListingCubit.getJobListings();
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Color(0xFFF8F9FB),
      body: BlocProvider.value(
        value: _jobListingCubit,
        child: BlocBuilder<JobListingCubit, JobListingState>(
          builder: (context, state) {
            if (state is JobListingLoading) {
              return Center(
                child: CircularProgressIndicator(
                  color: Color(0xFF1E3A8A),
                ),
              );
            }

            if (state is JobListingError) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.error_outline, size: 48, color: Colors.grey[400]),
                    SizedBox(height: 16),
                    Text(
                      'Error: ${state.message}',
                      style: TextStyle(color: Colors.grey[600], fontSize: 14),
                    ),
                    SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () {
                        _jobListingCubit.getJobListings();
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Color(0xFF1E3A8A),
                        padding: EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text('Coba Lagi', style: TextStyle(color: Colors.white)),
                    ),
                  ],
                ),
              );
            }

            if (state is JobListingLoaded) {
              return RefreshIndicator(
                color: Color(0xFF1E3A8A),
                onRefresh: () async {
                  await _jobListingCubit.getJobListings();
                },
                child: CustomScrollView(
                  slivers: [
                    SliverAppBar(
                      floating: false,
                      snap: false,
                      pinned: true,
                      elevation: 0,
                      backgroundColor: Colors.white,
                      title: Text(
                        'Lowongan Magang',
                        style: TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1E3A8A),
                        ),
                      ),
                      centerTitle: true,
                      bottom: PreferredSize(
                        preferredSize: const Size.fromHeight(115),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          child: Column(
                            children: [
                              // Search Bar - Enhanced
                              TextField(
                                decoration: InputDecoration(
                                  hintText: 'Cari posisi atau perusahaan...',
                                  hintStyle: TextStyle(color: Colors.grey[500], fontSize: 14),
                                  filled: true,
                                  fillColor: Colors.white,
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: BorderSide(color: Colors.grey[200]!),
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: BorderSide(color: Colors.grey[200]!, width: 1.5),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: BorderSide(color: Color(0xFF1E3A8A), width: 2),
                                  ),
                                  prefixIcon: Icon(Icons.search, color: Color(0xFF1E3A8A), size: 20),
                                  contentPadding: EdgeInsets.symmetric(vertical: 16, horizontal: 14),
                                ),
                                onChanged: (query) {
                                  _jobListingCubit.searchJobListings(query);
                                },
                              ),
                              SizedBox(height: 12),
                              // Category Filter Chips - Enhanced
                              SizedBox(
                                height: 42,
                                child: ListView.separated(
                                  scrollDirection: Axis.horizontal,
                                  itemCount: _jobListingCubit.categories.length,
                                  separatorBuilder: (_, __) => SizedBox(width: 10),
                                  itemBuilder: (context, index) {
                                    final category = _jobListingCubit.categories[index];
                                    final isSelected = category == state.selectedCategory;

                                    return GestureDetector(
                                      onTap: () {
                                        _jobListingCubit.filterByCategory(category);
                                      },
                                      child: AnimatedContainer(
                                        duration: Duration(milliseconds: 200),
                                        padding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                        decoration: BoxDecoration(
                                          color: isSelected ? Color(0xFF1E3A8A) : Colors.white,
                                          border: Border.all(
                                            color: isSelected ? Color(0xFF1E3A8A) : Colors.grey[300]!,
                                            width: 1.5,
                                          ),
                                          borderRadius: BorderRadius.circular(25),
                                          boxShadow: isSelected
                                              ? [
                                                  BoxShadow(
                                                    color: Color(0xFF1E3A8A).withOpacity(0.2),
                                                    blurRadius: 8,
                                                    offset: Offset(0, 2),
                                                  )
                                                ]
                                              : [],
                                        ),
                                        child: Center(
                                          child: Text(
                                            category,
                                            style: TextStyle(
                                              fontSize: 13,
                                              fontWeight: FontWeight.w600,
                                              color: isSelected ? Colors.white : Colors.grey[700],
                                            ),
                                          ),
                                        ),
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    SliverList(
                      delegate: SliverChildListDelegate([
                        SizedBox(height: 16),
                        // AI Recommendation
                        if (state.selectedCategory == 'Semua')
                          Padding(
                            padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            child: AiRecommendationCard(
                              recommendation: state.aiRecommendation,
                              onApply: () {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(
                                      'Terima kasih telah melamar ke ${state.aiRecommendation?.position}',
                                    ),
                                    backgroundColor: Color(0xFF1E3A8A),
                                    duration: Duration(seconds: 2),
                                  ),
                                );
                              },
                            ),
                          ),
                        
                        // Job Listings Header
                        if (state.jobListings.isNotEmpty)
                          Padding(
                            padding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Lowongan Tersedia',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFF1E3A8A),
                                  ),
                                ),
                                SizedBox(height: 4),
                                Text(
                                  '${state.jobListings.length} posisi tersedia untuk Anda',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey[600],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        
                        if (state.jobListings.isEmpty)
                          Padding(
                            padding: EdgeInsets.symmetric(vertical: 60),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Container(
                                  padding: EdgeInsets.all(24),
                                  decoration: BoxDecoration(
                                    color: Colors.grey[100],
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                  child: Icon(
                                    Icons.work_outline,
                                    size: 48,
                                    color: Colors.grey[400],
                                  ),
                                ),
                                SizedBox(height: 24),
                                Text(
                                  'Tidak ada lowongan yang sesuai',
                                  style: TextStyle(
                                    color: Colors.grey[700],
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                SizedBox(height: 8),
                                Text(
                                  'Coba ubah filter atau cari dengan kata kunci lain',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    color: Colors.grey[500],
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          )
                        else
                          Padding(
                            padding: EdgeInsets.symmetric(horizontal: 16),
                            child: Column(
                              children: [
                                ...state.jobListings.map((job) {
                                  return Padding(
                                    padding: EdgeInsets.only(bottom: 14),
                                    child: JobCard(
                                      job: job,
                                      onApply: () {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(
                                            content: Text(
                                              'Terima kasih telah melamar ke ${job.position}',
                                            ),
                                            backgroundColor: Color(0xFF1E3A8A),
                                            duration: Duration(seconds: 2),
                                          ),
                                        );
                                      },
                                    ),
                                  );
                                }).toList(),
                              ],
                            ),
                          ),
                        
                        SizedBox(height: 32),
                      ]),
                    ),
                  ],
                ),
              );
            }

            return Center(
              child: Text('Unknown state'),
            );
          },
        ),
      ),
    );
  }
}
