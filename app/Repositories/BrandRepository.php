<?php
namespace App\Repositories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;

class BrandRepository{
    public function getAllBrands():Collection{
        $brands = Brand::where('status', true)->orderBy('name')->get();
        return $brands;
    }
    public function getAllCategory():Collection{
        $category =  Category::where('status', true)->orderBy('name')->get();
        return $category;
    }
    public function createBrand(array $data):?Brand{
        return Brand::create($data);
    }
    public function findBrand(int $id):?Brand{
        $brand = Brand::findOrFail($id);
        return $brand;
    }
    public function updateBrand(array $data,int $brandId):bool{
        return Brand::where('id',$brandId)->update($data);
    }
    public function destroyBrand(int $brandId):bool{
        return Brand::where('id',$brandId)->delete();
    }
}
