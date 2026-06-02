import 'package:dartz/dartz.dart';
import 'package:sitama/features/auth/data/models/register_req_params.dart';
import 'package:sitama/features/auth/domain/repositories/auth.dart';
import 'package:sitama/service_locator.dart';
import 'package:sitama/utils/usecase.dart';

class RegisterUseCase extends UseCase<Either, RegisterReqParams> {
  @override
  Future<Either> call({RegisterReqParams? param}) async {
    return await sl<AuthRepostory>().register(param!);
  }
}
