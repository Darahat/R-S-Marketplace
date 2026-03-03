<?php
namespace App\Services;
use Illuminate\Support\Str;
use App\Repositories\AuthRepository;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
class AuthService{
  public function __construct(private AuthRepository $repo)
    {
    }
}
