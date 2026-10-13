<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    /**
     * Display Company Profile settings page.
     */
    public function index()
    {
        $profile = CompanyProfile::getProfile();

        return view('pages.company.index', [
            'title' => 'Company Profile',
            'profile' => $profile,
        ]);
    }

    /**
     * Update Company Profile information & custom logo.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'secondary_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'sidebar_title' => 'nullable|string|max:100',
            'sidebar_subtitle' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'secondary_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'remove_logo' => 'nullable|boolean',
            'remove_secondary_logo' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama perusahaan utama (1) wajib diisi.',
            'logo.image' => 'File Logo 1 harus berupa berkas gambar (PNG, JPG, SVG, WEBP).',
            'logo.max' => 'Ukuran file Logo 1 tidak boleh melebihi 5 MB.',
            'secondary_logo.image' => 'File Logo 2 harus berupa berkas gambar (PNG, JPG, SVG, WEBP).',
            'secondary_logo.max' => 'Ukuran file Logo 2 tidak boleh melebihi 5 MB.',
        ]);

        $profile = CompanyProfile::getProfile();
        $data = $request->only([
            'name',
            'secondary_name',
            'tagline',
            'email',
            'phone',
            'address',
            'website',
            'sidebar_title',
            'sidebar_subtitle',
        ]);
        $destinationPath = public_path('uploads/company');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $logoChanges = [];

        // 1. Handle Primary Logo (Logo 1)
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_1_' . time() . '.' . $file->getClientOriginalExtension();

            // Remove old logo file and generated favicon
            if ($profile->logo_path && file_exists(public_path($profile->logo_path))) {
                @unlink(public_path($profile->logo_path));
            }
            $favPath = public_path('uploads/company/fav_' . pathinfo($profile->logo_path ?? '', PATHINFO_FILENAME) . '.png');
            if (file_exists($favPath)) {
                @unlink($favPath);
            }

            $file->move($destinationPath, $filename);
            $this->autoCropImage($destinationPath . '/' . $filename);
            $data['logo_path'] = 'uploads/company/' . $filename;
            $logoChanges[] = 'Logo 1 (Utama)';
        } elseif ($request->boolean('remove_logo')) {
            if ($profile->logo_path && file_exists(public_path($profile->logo_path))) {
                @unlink(public_path($profile->logo_path));
            }
            $data['logo_path'] = null;
            $logoChanges[] = 'Hapus Logo 1';
        }

        // 2. Handle Secondary Logo (Logo 2)
        if ($request->hasFile('secondary_logo')) {
            $file2 = $request->file('secondary_logo');
            $filename2 = 'logo_2_' . time() . '.' . $file2->getClientOriginalExtension();

            // Remove old secondary logo file
            if ($profile->secondary_logo_path && file_exists(public_path($profile->secondary_logo_path))) {
                @unlink(public_path($profile->secondary_logo_path));
            }

            $file2->move($destinationPath, $filename2);
            $this->autoCropImage($destinationPath . '/' . $filename2);
            $data['secondary_logo_path'] = 'uploads/company/' . $filename2;
            $logoChanges[] = 'Logo 2 (Kedua)';
        } elseif ($request->boolean('remove_secondary_logo')) {
            if ($profile->secondary_logo_path && file_exists(public_path($profile->secondary_logo_path))) {
                @unlink(public_path($profile->secondary_logo_path));
            }
            $data['secondary_logo_path'] = null;
            $logoChanges[] = 'Hapus Logo 2';
        }

        $profile->fill($data);
        $profile->save();

        $changeDesc = !empty($logoChanges) ? ' dengan perubahan: ' . implode(', ', $logoChanges) : '';
        ActivityLog::record(
            activity: 'Perbarui Profil Perusahaan',
            description: 'Memperbarui profil & identitas perusahaan "' . $profile->name . '"' . $changeDesc . '.',
            module: 'Pengaturan System'
        );

        return redirect()->back()->with('success', 'Profil, Nama & Logo Perusahaan berhasil diperbarui!');
    }

    /**
     * Auto crop excessive whitespace/transparency around logo for uniform visual display.
     */
    private function autoCropImage(string $fullPath): void
    {
        try {
            $info = @getimagesize($fullPath);
            if (!$info) return;

            $mime = $info['mime'];
            if (str_contains($mime, 'png')) {
                $img = @imagecreatefrompng($fullPath);
            } elseif (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                $img = @imagecreatefromjpeg($fullPath);
            } elseif (str_contains($mime, 'webp') && function_exists('imagecreatefromwebp')) {
                $img = @imagecreatefromwebp($fullPath);
            } else {
                return;
            }

            if (!$img) return;

            $width = imagesx($img);
            $height = imagesy($img);

            $minX = $width; $minY = $height; $maxX = 0; $maxY = 0;
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($img, $x, $y);
                    $alpha = ($rgb >> 24) & 0x7F;
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $isWhite = ($r > 240 && $g > 240 && $b > 240);
                    if ($alpha < 120 && !$isWhite) {
                        if ($x < $minX) $minX = $x;
                        if ($x > $maxX) $maxX = $x;
                        if ($y < $minY) $minY = $y;
                        if ($y > $maxY) $maxY = $y;
                    }
                }
            }

            if ($maxX > $minX && $maxY > $minY) {
                $margin = 15;
                $cropX = max(0, $minX - $margin);
                $cropY = max(0, $minY - $margin);
                $cropW = min($width - $cropX, ($maxX - $minX + 1) + ($margin * 2));
                $cropH = min($height - $cropY, ($maxY - $minY + 1) + ($margin * 2));

                $dst = imagecreatetruecolor($cropW, $cropH);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparentColor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefill($dst, 0, 0, $transparentColor);

                imagecopy($dst, $img, 0, 0, $cropX, $cropY, $cropW, $cropH);

                imagepng($dst, $fullPath);
                imagedestroy($dst);
            }
            imagedestroy($img);
        } catch (\Throwable $e) {}
    }
}
