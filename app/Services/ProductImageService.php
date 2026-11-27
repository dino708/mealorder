<?php
namespace App\Services;

use App\Models\{Product,OrderItem};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductImageService
{
    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['image_file'=>$message]);
    }

    // Decode then re-encode pixels. Never publish bytes or names supplied by users.
    public function store(UploadedFile $file): string
    {
        $allowed=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'];
        $ext=strtolower($file->getClientOriginalExtension());
        $meta=@getimagesize($file->getPathname());
        if (!$file->isValid() || $file->getSize()>5*1024*1024 || !isset($allowed[$ext])
            || !$meta || ($meta['mime']??null)!==$allowed[$ext]
            || $file->getMimeType()!==$allowed[$ext]
            || $meta[0]<1 || $meta[1]<1 || $meta[0]>4096 || $meta[1]>4096
            || $meta[0]*$meta[1]>12000000) {
            $this->invalid('Gunakan gambar JPG, JPEG, PNG atau WebP asli, maksimal 5 MB dan 12 megapiksel.');
        }
        if (!function_exists('imagecreatefromstring')) {
            $this->invalid('Penyimpanan gambar belum tersedia. Hubungi administrator sistem.');
        }
        $bytes=file_get_contents($file->getPathname());
        $source=$bytes===false?false:@imagecreatefromstring($bytes);
        if (!$source) $this->invalid('Konten gambar tidak dapat dibaca.');
        $scale=min(1,1200/max($meta[0],$meta[1]));
        $w=max(1,(int)round($meta[0]*$scale)); $h=max(1,(int)round($meta[1]*$scale));
        $dest=imagecreatetruecolor($w,$h);
        if (!$dest) { imagedestroy($source); $this->invalid('Gambar gagal diproses.'); }
        imagefill($dest,0,0,imagecolorallocate($dest,255,255,255));
        $ok=imagecopyresampled($dest,$source,0,0,0,0,$w,$h,$meta[0],$meta[1]);
        ob_start();
        try { $encoded=$ok && imagejpeg($dest,null,86); $jpeg=ob_get_contents(); }
        finally { ob_end_clean(); imagedestroy($source); imagedestroy($dest); }
        if (!$encoded || !$jpeg) $this->invalid('Gambar gagal dioptimalkan.');
        $path='meal-images/'.Str::uuid().'.jpg';
        try {
            if (!Storage::disk('public')->put($path,$jpeg)) throw new \RuntimeException('Storage write failed');
        } catch (\Throwable $e) {
            $this->discard($path); report($e); $this->invalid('Foto gagal disimpan. Silakan coba lagi.');
        }
        return $path;
    }

    private function managed(?string $path): ?string
    {
        if (!$path) return null;
        $path=preg_replace('~^/?storage/~','',$path);
        // Only flat generated names, never default assets or user-supplied paths.
        return preg_match('~^meal-images/[a-zA-Z0-9_-]+\.(?:jpe?g|png|webp)$~D',$path)?$path:null;
    }

    public function discard(?string $path): void
    {
        if (!$path=$this->managed($path)) return;
        try { Storage::disk('public')->delete($path); } catch (\Throwable $e) { report($e); }
    }

    public function cleanup(?string $path): void
    {
        if (!$path=$this->managed($path)) return;
        $variants=[$path,'/storage/'.$path,'storage/'.$path];
        try {
            if (Product::withTrashed()->whereIn('image',$variants)->exists()
                || OrderItem::whereIn('product_image',$variants)->exists()) return;
            $this->discard($path);
        } catch (\Throwable $e) { report($e); }
    }
}
