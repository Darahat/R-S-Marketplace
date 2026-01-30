<?php
namespace App\Services;

use App\Repositories\BrandRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Address;
class BrandService{
  public function __construct(private BrandRepository $repo)
    {
    }
    public function createBrand(array $data){
        $this->repo->createBrand($data);
    }


}
