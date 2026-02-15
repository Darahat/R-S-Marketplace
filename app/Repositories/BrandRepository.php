<?php
namespace App\Repositories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;

class BrandRepository{
    public function getAllBrands():Collection{
         return $this->getBrandByStatus(true);
    }
    public function getAllCategory():Collection{
        return Category::where('status', true)->orderBy('name')->get();
    }
    public function createBrand(array $data):?Brand{
        return Brand::create($data);
    }
    public function findBrand(int $id):?Brand{
        return Brand::find($id);
    }
    public function updateBrand(array $data,int $brandId):bool{
        return Brand::where('id',$brandId)->update($data);
    }
    public function destroyBrand(int $brandId):bool{
        return Brand::where('id',$brandId)  ->delete();
    }
    public function getBrandByStatus(bool $brandStatus):Collection{
        return Brand::where('status',$brandStatus)->orderBy('name')->get();
    }
}
