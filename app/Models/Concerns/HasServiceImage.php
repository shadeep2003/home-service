<?php
namespace App\Models\Concerns;
use Illuminate\Support\Facades\Storage;
trait HasServiceImage
{
    public function imageUrl(): string
    {
        if ($this->image && Storage::disk('service_images')->exists($this->image)) {
            return asset(Storage::disk('service_images')->url($this->image));
        }
        // New offerings inherit their parent's real photo until assigned their own.
        if ($this instanceof \App\Models\ServiceSubcategory && $this->category) {
            return $this->category->imageUrl();
        }
        return asset(Storage::disk('service_images')->url('home-services.webp'));
    }
    public function imageAlt(): string
    {
        if ($this->image && $this->image_alt && Storage::disk('service_images')->exists($this->image)) {
            return $this->image_alt;
        }
        if ($this instanceof \App\Models\ServiceSubcategory && $this->category) {
            return $this->category->imageAlt();
        }
        return 'Professional home service tools ready for work.';
    }
    public function imageSrcset(): string
    {
        $path = $this->image;
        if (!$path || !Storage::disk('service_images')->exists($path)) {
            if ($this instanceof \App\Models\ServiceSubcategory && $this->category) { return $this->category->imageSrcset(); }
            $path = 'home-services.webp';
        }
        $small = preg_replace('/\.webp$/', '-480.webp', $path);
        $disk = Storage::disk('service_images');
        if ($small !== $path && $disk->exists($small)) {
            return asset($disk->url($small)).' 480w, '.asset($disk->url($path)).' 960w';
        }
        return asset($disk->url($path)).' 960w';
    }
}
