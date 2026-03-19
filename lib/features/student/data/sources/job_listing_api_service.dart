import 'package:dartz/dartz.dart';
import 'package:sitama/features/student/data/models/job_listing.dart';

abstract class JobListingApiService {
  Future<Either> getJobListings();
  Future<Either> getJobListingsByCategory(String category);
  Future<Either> searchJobListings(String query);
  Future<Either> getAiRecommendation();
}

class JobListingApiServiceImpl implements JobListingApiService {
  // Mock data untuk development tanpa backend
  final List<Map<String, dynamic>> _mockJobListings = [
    {
      'id': '1',
      'position': 'Frontend Developer Intern',
      'company': 'PT. Telkom Indonesia',
      'company_logo': 'TI',
      'description': 'Kami mencari Frontend Developer yang berpengalaman dengan Flutter dan Dart',
      'skills': ['Flutter', 'Dart', 'UI/UX'],
      'location': 'Semarang',
      'category': 'IT',
      'is_new': true,
      'created_at': DateTime.now().toIso8601String(),
    },
    {
      'id': '2',
      'position': 'Backend Engineer Intern',
      'company': 'Bank Negara Indonesia',
      'company_logo': 'BN',
      'description': 'Mencari Backend Engineer untuk mengembangkan REST API',
      'skills': ['Laravel', 'PHP', 'MySQL'],
      'location': 'Semarang',
      'category': 'IT',
      'is_new': false,
      'created_at': DateTime.now().subtract(Duration(days: 2)).toIso8601String(),
    },
    {
      'id': '3',
      'position': 'UI/UX Designer Intern',
      'company': 'Gojek',
      'company_logo': 'GK',
      'description': 'Desain antarmuka untuk aplikasi mobile dan web',
      'skills': ['Figma', 'Prototyping', 'Research'],
      'location': 'Jakarta (Remote)',
      'category': 'Desain',
      'is_new': true,
      'created_at': DateTime.now().toIso8601String(),
    },
    {
      'id': '4',
      'position': 'Data Analyst Intern',
      'company': 'Tokopedia',
      'company_logo': 'TP',
      'description': 'Analisis data untuk mendukung keputusan bisnis',
      'skills': ['Python', 'SQL', 'Tableau'],
      'location': 'Jakarta',
      'category': 'Data',
      'is_new': false,
      'created_at': DateTime.now().subtract(Duration(days: 5)).toIso8601String(),
    },
    {
      'id': '5',
      'position': 'Digital Marketing Intern',
      'company': 'Bukalapak',
      'company_logo': 'BL',
      'description': 'Strategi marketing digital dan social media management',
      'skills': ['Digital Marketing', 'Social Media', 'Analytics'],
      'location': 'Jakarta',
      'category': 'Marketing',
      'is_new': true,
      'created_at': DateTime.now().toIso8601String(),
    },
    {
      'id': '6',
      'position': 'Mobile Developer Intern',
      'company': 'OVO',
      'company_logo': 'OV',
      'description': 'Develop mobile app dengan React Native',
      'skills': ['React Native', 'JavaScript', 'Mobile App'],
      'location': 'Jakarta',
      'category': 'IT',
      'is_new': false,
      'created_at': DateTime.now().subtract(Duration(days: 3)).toIso8601String(),
    },
  ];

  @override
  Future<Either> getJobListings() async {
    try {
      // Simulasi delay API call
      await Future.delayed(Duration(seconds: 1));
      
      List<JobListingModel> jobs = _mockJobListings
          .map((job) => JobListingModel.fromMap(job))
          .toList();
      
      return Right(jobs);
    } catch (e) {
      return Left(e.toString());
    }
  }

  @override
  Future<Either> getJobListingsByCategory(String category) async {
    try {
      await Future.delayed(Duration(seconds: 1));
      
      List<JobListingModel> jobs = _mockJobListings
          .where((job) => job['category'].toLowerCase() == category.toLowerCase())
          .map((job) => JobListingModel.fromMap(job))
          .toList();
      
      return Right(jobs);
    } catch (e) {
      return Left(e.toString());
    }
  }

  @override
  Future<Either> searchJobListings(String query) async {
    try {
      await Future.delayed(Duration(seconds: 1));
      
      List<JobListingModel> jobs = _mockJobListings
          .where((job) =>
              job['position'].toLowerCase().contains(query.toLowerCase()) ||
              job['company'].toLowerCase().contains(query.toLowerCase()) ||
              job['skills'].any((skill) => skill.toLowerCase().contains(query.toLowerCase())))
          .map((job) => JobListingModel.fromMap(job))
          .toList();
      
      return Right(jobs);
    } catch (e) {
      return Left(e.toString());
    }
  }

  @override
  Future<Either> getAiRecommendation() async {
    try {
      await Future.delayed(Duration(seconds: 1));
      
      // Return 1 random job sebagai rekomendasi AI
      if (_mockJobListings.isNotEmpty) {
        _mockJobListings.shuffle();
        JobListingModel recommendation = JobListingModel.fromMap(_mockJobListings.first);
        return Right(recommendation);
      }
      
      return Left("No jobs available");
    } catch (e) {
      return Left(e.toString());
    }
  }
}
