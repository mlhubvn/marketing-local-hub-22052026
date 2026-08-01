<?php

namespace Modules\AppBusinessProfiles\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class BusinessQrRenderer
{
    public function render(LocalBusiness $business, ?array $design = null, ?string $targetUrl = null): string
    {
        $design = BusinessQrStyleCatalog::normalize($design ?? (array) ($business->qr_design ?? []));
        $qr = $this->renderQr($targetUrl ?: $business->qrTargetUrl(), $design);

        if (($design['frame_style'] ?? 'card') === 'minimal') {
            return $qr;
        }

        return $this->wrapFrame($qr, $business, $design);
    }

    public function renderPng(LocalBusiness $business, ?array $design = null, ?string $targetUrl = null): string
    {
        $design = BusinessQrStyleCatalog::normalize($design ?? (array) ($business->qr_design ?? []));
        $qr = $this->renderQrPngImage($targetUrl ?: $business->qrTargetUrl(), $design);

        if (($design['frame_style'] ?? 'card') === 'minimal') {
            $this->applyLogoOverlayToImage($qr, $design);

            return $this->pngString($qr);
        }

        $framed = $this->wrapFramePngImage($qr, $business, $design);

        return $this->pngString($framed);
    }

    protected function renderQr(string $value, array $design): string
    {
        return match ((string) ($design['pattern'] ?? 'square')) {
            'gradient_rounded' => $this->renderRoundedGradient($value, $design),
            'mosaic' => $this->renderMosaic($value, $design),
            default => $this->renderSquare($value, $design),
        };
    }

    protected function renderSquare(string $value, array $design): string
    {
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::H(), 'UTF-8', null, false)->getMatrix();
        $matrixSize = $matrix->getWidth();
        $margin = 4;
        $unit = 8;
        $canvasSize = ($matrixSize + ($margin * 2)) * $unit;
        $foreground = $this->hex((string) $design['foreground_color'], '#0f172a');
        $background = $this->hex((string) $design['background_color'], '#ffffff');
        $svg = [
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$canvasSize.' '.$canvasSize.'" width="'.$canvasSize.'" height="'.$canvasSize.'" role="img" aria-label="QR code">',
            '<rect width="100%" height="100%" fill="'.$background.'"/>',
        ];

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if ($matrix->get($x, $y)) {
                    $svg[] = '<rect x="'.(($x + $margin) * $unit).'" y="'.(($y + $margin) * $unit).'" width="'.$unit.'" height="'.$unit.'" fill="'.$foreground.'"/>';
                }
            }
        }

        $svg[] = $this->logoOverlay($canvasSize, $design);
        $svg[] = '</svg>';

        return implode('', $svg);
    }

    protected function renderRoundedGradient(string $value, array $design): string
    {
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::H(), 'UTF-8', null, false)->getMatrix();
        $matrixSize = $matrix->getWidth();
        $margin = 4;
        $unit = 8;
        $canvasSize = ($matrixSize + ($margin * 2)) * $unit;
        $foreground = $this->hex((string) $design['foreground_color'], '#0f766e');
        $accent = $this->hex((string) $design['accent_color'], '#14b8a6');
        $background = $this->hex((string) $design['background_color'], '#ffffff');
        $gradientId = 'business-qr-gradient-'.substr(md5($value.$foreground.$accent), 0, 8);
        $svg = [
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$canvasSize.' '.$canvasSize.'" width="'.$canvasSize.'" height="'.$canvasSize.'" role="img" aria-label="QR code">',
            '<defs><linearGradient id="'.$gradientId.'" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="'.$foreground.'"/><stop offset="100%" stop-color="'.$accent.'"/></linearGradient></defs>',
            '<rect width="100%" height="100%" fill="'.$background.'"/>',
        ];

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (! $matrix->get($x, $y) || $this->isFinder($x, $y, $matrixSize)) {
                    continue;
                }

                $svg[] = '<rect x="'.(($x + $margin) * $unit).'" y="'.(($y + $margin) * $unit).'" width="'.($unit * 0.92).'" height="'.($unit * 0.92).'" rx="'.($unit * 0.45).'" fill="url(#'.$gradientId.')"/>';
            }
        }

        $this->finder($svg, $margin, $unit, 0, 0, $foreground, $accent, $background);
        $this->finder($svg, $margin, $unit, $matrixSize - 7, 0, $foreground, $accent, $background);
        $this->finder($svg, $margin, $unit, 0, $matrixSize - 7, $foreground, $accent, $background);
        $svg[] = $this->logoOverlay($canvasSize, $design);
        $svg[] = '</svg>';

        return implode('', $svg);
    }

    protected function renderMosaic(string $value, array $design): string
    {
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::H(), 'UTF-8', null, false)->getMatrix();
        $matrixSize = $matrix->getWidth();
        $margin = 4;
        $unit = 8;
        $canvasSize = ($matrixSize + ($margin * 2)) * $unit;
        $foreground = $this->hex((string) $design['foreground_color'], '#15803d');
        $accent = $this->hex((string) $design['accent_color'], '#65a30d');
        $background = $this->hex((string) $design['background_color'], '#ffffff');
        $shape = (string) ($design['shape'] ?? 'circle');
        $svg = [
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$canvasSize.' '.$canvasSize.'" width="'.$canvasSize.'" height="'.$canvasSize.'" role="img" aria-label="QR code">',
            '<rect width="100%" height="100%" rx="'.($unit * 2).'" fill="'.$background.'"/>',
        ];

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (! $matrix->get($x, $y)) {
                    continue;
                }

                $cx = ($x + $margin + 0.5) * $unit;
                $cy = ($y + $margin + 0.5) * $unit;
                $size = $this->isFinder($x, $y, $matrixSize) ? $unit * 0.94 : $unit * 0.72;
                $svg[] = $this->dot($cx, $cy, $size, $shape, $this->isFinder($x, $y, $matrixSize) ? $accent : $foreground);
            }
        }

        $svg[] = $this->logoOverlay($canvasSize, $design);
        $svg[] = '</svg>';

        return implode('', $svg);
    }

    protected function wrapFrame(string $qrSvg, LocalBusiness $business, array $design): string
    {
        $qrEmbedded = $this->embedQrSvg($qrSvg, 86, 96, 348, 348);
        $background = $this->hex((string) $design['background_color'], '#ffffff');
        $surface = $this->hex((string) $design['surface_color'], '#ecfeff');
        $accent = $this->hex((string) $design['accent_color'], '#14b8a6');
        $foreground = $this->hex((string) $design['foreground_color'], '#0f172a');
        $label = $this->escape((string) $design['label']);
        $name = $this->escape(Str::limit((string) $business->name, 34));
        $address = $this->escape(Str::limit((string) $business->address, 46));
        $frameStyle = (string) ($design['frame_style'] ?? 'card');
        $height = $frameStyle === 'label' ? 560 : 620;
        $svg = [
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 520 '.$height.'" width="520" height="'.$height.'" role="img" aria-label="Business QR code">',
            '<rect width="520" height="'.$height.'" rx="32" fill="'.$surface.'"/>',
            '<rect x="18" y="18" width="484" height="'.($height - 36).'" rx="28" fill="'.$background.'" stroke="'.$accent.'" stroke-opacity="0.25" stroke-width="2"/>',
            '<text x="260" y="66" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="20" font-weight="700" fill="'.$foreground.'">'.$label.'</text>',
            $qrEmbedded,
        ];

        if ((bool) ($design['show_business_name'] ?? true)) {
            $svg[] = '<text x="260" y="482" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="22" font-weight="800" fill="'.$foreground.'">'.$name.'</text>';
        }

        if ($frameStyle !== 'label' && (bool) ($design['show_address'] ?? true) && $address !== '') {
            $svg[] = '<text x="260" y="516" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="14" font-weight="500" fill="'.$foreground.'" opacity="0.68">'.$address.'</text>';
        }

        $svg[] = '<rect x="184" y="'.($height - 72).'" width="152" height="34" rx="17" fill="'.$accent.'" fill-opacity="0.12"/>';
        $svg[] = '<text x="260" y="'.($height - 50).'" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="12" font-weight="800" letter-spacing="2" fill="'.$accent.'">MKT AI</text>';
        $svg[] = '</svg>';

        return implode('', $svg);
    }

    protected function finder(array &$svg, int $margin, int $unit, int $x, int $y, string $outer, string $dot, string $background): void
    {
        $px = ($x + $margin) * $unit;
        $py = ($y + $margin) * $unit;
        $size = $unit * 7;
        $svg[] = '<rect x="'.$px.'" y="'.$py.'" width="'.$size.'" height="'.$size.'" rx="'.($unit * 0.8).'" fill="'.$outer.'"/>';
        $svg[] = '<rect x="'.($px + $unit * 1.1).'" y="'.($py + $unit * 1.1).'" width="'.($unit * 4.8).'" height="'.($unit * 4.8).'" rx="'.($unit * 0.45).'" fill="'.$background.'"/>';
        $svg[] = '<rect x="'.($px + $unit * 2.05).'" y="'.($py + $unit * 2.05).'" width="'.($unit * 2.9).'" height="'.($unit * 2.9).'" rx="'.($unit * 0.35).'" fill="'.$dot.'"/>';
    }

    protected function dot(float $cx, float $cy, float $size, string $shape, string $color): string
    {
        $half = $size / 2;

        return match ($shape) {
            'square' => '<rect x="'.($cx - $half).'" y="'.($cy - $half).'" width="'.$size.'" height="'.$size.'" fill="'.$color.'"/>',
            'rounded' => '<rect x="'.($cx - $half).'" y="'.($cy - $half).'" width="'.$size.'" height="'.$size.'" rx="'.($size * 0.28).'" fill="'.$color.'"/>',
            default => '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.($size / 2).'" fill="'.$color.'"/>',
        };
    }

    protected function isFinder(int $x, int $y, int $size): bool
    {
        return ($x <= 6 && $y <= 6)
            || ($x >= $size - 7 && $y <= 6)
            || ($x <= 6 && $y >= $size - 7);
    }

    protected function hex(string $hex, string $fallback): string
    {
        $hex = strtolower(trim($hex));
        $hex = str_starts_with($hex, '#') ? $hex : '#'.$hex;

        return preg_match('/^#[0-9a-f]{6}$/', $hex) ? $hex : $fallback;
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    protected function renderQrPngImage(string $value, array $design): \GdImage
    {
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::H(), 'UTF-8', null, false)->getMatrix();
        $matrixSize = $matrix->getWidth();
        $margin = 4;
        $unit = 16;
        $canvasSize = ($matrixSize + ($margin * 2)) * $unit;
        $image = imagecreatetruecolor($canvasSize, $canvasSize);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $background = $this->gdColor($image, $this->hex((string) $design['background_color'], '#ffffff'));
        $foreground = $this->gdColor($image, $this->hex((string) $design['foreground_color'], '#0f172a'));
        $accent = $this->gdColor($image, $this->hex((string) $design['accent_color'], '#14b8a6'));
        imagefilledrectangle($image, 0, 0, $canvasSize, $canvasSize, $background);

        $pattern = (string) ($design['pattern'] ?? 'square');
        $shape = (string) ($design['shape'] ?? 'square');

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (! $matrix->get($x, $y)) {
                    continue;
                }

                $px = (int) (($x + $margin) * $unit);
                $py = (int) (($y + $margin) * $unit);
                $color = $pattern === 'gradient_rounded'
                    ? $this->gdGradientColor($image, $design, $x + $y, $matrixSize * 2)
                    : ($this->isFinder($x, $y, $matrixSize) ? $accent : $foreground);

                if ($pattern === 'mosaic') {
                    $size = $this->isFinder($x, $y, $matrixSize) ? (int) round($unit * 0.94) : (int) round($unit * 0.72);
                    $cx = $px + (int) round($unit / 2);
                    $cy = $py + (int) round($unit / 2);
                    $this->gdDot($image, $cx, $cy, $size, $shape, $color);
                    continue;
                }

                if ($pattern === 'gradient_rounded' && ! $this->isFinder($x, $y, $matrixSize)) {
                    $this->gdRoundedRect($image, $px, $py, (int) round($unit * 0.92), (int) round($unit * 0.92), (int) round($unit * 0.45), $color);
                    continue;
                }

                imagefilledrectangle($image, $px, $py, $px + $unit - 1, $py + $unit - 1, $color);
            }
        }

        if ($pattern === 'gradient_rounded') {
            $this->gdFinder($image, $margin, $unit, 0, 0, $foreground, $accent, $background);
            $this->gdFinder($image, $margin, $unit, $matrixSize - 7, 0, $foreground, $accent, $background);
            $this->gdFinder($image, $margin, $unit, 0, $matrixSize - 7, $foreground, $accent, $background);
        }

        $this->applyLogoOverlayToImage($image, $design);

        return $image;
    }

    protected function wrapFramePngImage(\GdImage $qr, LocalBusiness $business, array $design): \GdImage
    {
        $scale = 2;
        $width = 520 * $scale;
        $height = (($design['frame_style'] ?? 'card') === 'label' ? 560 : 620) * $scale;
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $background = $this->gdColor($image, $this->hex((string) $design['background_color'], '#ffffff'));
        $surface = $this->gdColor($image, $this->hex((string) $design['surface_color'], '#ecfeff'));
        $accent = $this->gdColor($image, $this->hex((string) $design['accent_color'], '#14b8a6'));
        $foreground = $this->gdColor($image, $this->hex((string) $design['foreground_color'], '#0f172a'));
        $muted = $this->gdColor($image, $this->hex((string) $design['foreground_color'], '#0f172a'), 55);

        imagefilledrectangle($image, 0, 0, $width, $height, $surface);
        $this->gdRoundedRect($image, 36, 36, 968, $height - 72, 56, $background);
        imagesetthickness($image, 4);
        $this->gdRoundedRectOutline($image, 36, 36, 968, $height - 72, 56, $accent);
        imagesetthickness($image, 1);

        $this->gdCenterText($image, (string) $design['label'], 5, 66 * $scale, $foreground);
        imagecopyresampled($image, $qr, 172, 192, 0, 0, 696, 696, imagesx($qr), imagesy($qr));

        if ((bool) ($design['show_business_name'] ?? true)) {
            $this->gdCenterText($image, Str::limit((string) $business->name, 34), 5, 482 * $scale, $foreground);
        }

        if (($design['frame_style'] ?? 'card') !== 'label' && (bool) ($design['show_address'] ?? true) && filled($business->address)) {
            $this->gdCenterText($image, Str::limit((string) $business->address, 46), 3, 516 * $scale, $muted);
        }

        $badgeX = 184 * $scale;
        $badgeY = ($height - 72 * $scale);
        $this->gdRoundedRect($image, $badgeX, $badgeY, 152 * $scale, 34 * $scale, 17 * $scale, $this->gdColor($image, $this->hex((string) $design['accent_color'], '#14b8a6'), 35));
        $this->gdCenterText($image, 'MKT AI', 2, $height - 50 * $scale, $accent);

        return $image;
    }

    protected function applyLogoOverlayToImage(\GdImage $image, array $design): void
    {
        if (! (bool) ($design['logo_enabled'] ?? true)) {
            return;
        }

        $logo = $this->gdLogoImage($design);

        if (! $logo) {
            return;
        }

        $canvasSize = imagesx($image);
        $box = (int) round($canvasSize * 0.18);
        $pad = (int) round($box * 0.10);
        $logoSize = $box - ($pad * 2);
        $x = (int) round(($canvasSize - $box) / 2);
        $logoX = $x + $pad;
        $center = (int) round($canvasSize / 2);
        $white = $this->gdColor($image, '#ffffff');
        imagefilledellipse($image, $center, $center, $box, $box, $white);
        imageellipse($image, $center, $center, $box, $box, $this->gdColor($image, '#0f172a', 40));

        $square = imagecreatetruecolor($logoSize, $logoSize);
        imagealphablending($square, false);
        imagesavealpha($square, true);
        imagefilledrectangle($square, 0, 0, $logoSize, $logoSize, imagecolorallocatealpha($square, 0, 0, 0, 127));

        $srcW = imagesx($logo);
        $srcH = imagesy($logo);
        $srcSize = min($srcW, $srcH);
        $srcX = (int) floor(($srcW - $srcSize) / 2);
        $srcY = (int) floor(($srcH - $srcSize) / 2);
        imagecopyresampled($square, $logo, 0, 0, $srcX, $srcY, $logoSize, $logoSize, $srcSize, $srcSize);
        $transparent = imagecolorallocatealpha($square, 0, 0, 0, 127);
        $center = $logoSize / 2;
        $radius = $logoSize / 2;

        for ($py = 0; $py < $logoSize; $py++) {
            for ($px = 0; $px < $logoSize; $px++) {
                if (((($px + 0.5) - $center) ** 2) + (((($py + 0.5) - $center) ** 2)) > ($radius ** 2)) {
                    imagesetpixel($square, $px, $py, $transparent);
                }
            }
        }

        imagecopy($image, $square, $logoX, $logoX, 0, 0, $logoSize, $logoSize);

        imagedestroy($square);
        imagedestroy($logo);
    }

    protected function gdLogoImage(array $design): ?\GdImage
    {
        $contents = null;
        $dataUri = (string) ($design['logo_data_uri'] ?? '');

        if (str_starts_with($dataUri, 'data:image/') && str_contains($dataUri, ',')) {
            $contents = base64_decode((string) str($dataUri)->after(','), true) ?: null;
        } elseif (filled($design['logo_path'] ?? null) && Storage::disk('public')->exists((string) $design['logo_path'])) {
            $contents = Storage::disk('public')->get((string) $design['logo_path']);
        }

        return $contents ? @imagecreatefromstring($contents) ?: null : null;
    }

    protected function pngString(\GdImage $image): string
    {
        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return $png;
    }

    protected function logoOverlay(int $canvasSize, array $design): string
    {
        if (! (bool) ($design['logo_enabled'] ?? true) || blank($design['logo_path'] ?? null)) {
            return '';
        }

        $dataUri = (string) ($design['logo_data_uri'] ?? '');

        if (str_starts_with($dataUri, 'data:image/')) {
            $payload = $dataUri;
        } else {
            $path = (string) $design['logo_path'];

            if (! Storage::disk('public')->exists($path)) {
                return '';
            }

            $mime = (string) (Storage::disk('public')->mimeType($path) ?: 'image/png');

            if (! in_array($mime, ['image/png', 'image/jpeg', 'image/webp', 'image/gif'], true)) {
                return '';
            }

            $payload = 'data:'.$mime.';base64,'.base64_encode((string) Storage::disk('public')->get($path));
        }

        $box = round($canvasSize * 0.18, 2);
        $pad = round($box * 0.10, 2);
        $image = $box - ($pad * 2);
        $x = round(($canvasSize - $box) / 2, 2);
        $imageX = $x + $pad;
        $center = round($canvasSize / 2, 2);
        $radius = round($image / 2, 2);
        $clipId = 'business-qr-logo-'.substr(md5($payload.$canvasSize), 0, 10);

        return '<defs><clipPath id="'.$clipId.'"><circle cx="'.$center.'" cy="'.$center.'" r="'.$radius.'"/></clipPath></defs>'
            .'<circle cx="'.$center.'" cy="'.$center.'" r="'.round($box / 2, 2).'" fill="#ffffff"/>'
            .'<circle cx="'.$center.'" cy="'.$center.'" r="'.round($box / 2, 2).'" fill="#ffffff" stroke="#0f172a" stroke-opacity="0.10" stroke-width="'.max(1, round($canvasSize * 0.008, 2)).'"/>'
            .'<image x="'.$imageX.'" y="'.$imageX.'" width="'.$image.'" height="'.$image.'" href="'.$payload.'" preserveAspectRatio="xMidYMid slice" clip-path="url(#'.$clipId.')"/>';
    }

    protected function embedQrSvg(string $qrSvg, int $x, int $y, int $width, int $height): string
    {
        return preg_replace_callback('/<svg\b([^>]*)>/', function (array $matches) use ($x, $y, $width, $height): string {
            $attributes = preg_replace('/\s(?:x|y|width|height)="[^"]*"/', '', $matches[1]) ?? $matches[1];

            return '<svg x="'.$x.'" y="'.$y.'" width="'.$width.'" height="'.$height.'"'.$attributes.'>';
        }, $qrSvg, 1) ?? $qrSvg;
    }

    protected function gdColor(\GdImage $image, string $hex, int $alpha = 0): int
    {
        $hex = ltrim($this->hex($hex, '#000000'), '#');

        return imagecolorallocatealpha(
            $image,
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
            max(0, min(127, $alpha))
        );
    }

    protected function gdGradientColor(\GdImage $image, array $design, int $position, int $max): int
    {
        $start = $this->rgb($this->hex((string) $design['foreground_color'], '#0f766e'));
        $end = $this->rgb($this->hex((string) $design['accent_color'], '#14b8a6'));
        $ratio = $max > 0 ? max(0, min(1, $position / $max)) : 0;

        return imagecolorallocate(
            $image,
            (int) round($start[0] + (($end[0] - $start[0]) * $ratio)),
            (int) round($start[1] + (($end[1] - $start[1]) * $ratio)),
            (int) round($start[2] + (($end[2] - $start[2]) * $ratio))
        );
    }

    protected function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    protected function gdDot(\GdImage $image, int $cx, int $cy, int $size, string $shape, int $color): void
    {
        $half = (int) round($size / 2);

        match ($shape) {
            'square' => imagefilledrectangle($image, $cx - $half, $cy - $half, $cx + $half, $cy + $half, $color),
            'rounded' => $this->gdRoundedRect($image, $cx - $half, $cy - $half, $size, $size, (int) round($size * 0.28), $color),
            default => imagefilledellipse($image, $cx, $cy, $size, $size, $color),
        };
    }

    protected function gdFinder(\GdImage $image, int $margin, int $unit, int $x, int $y, int $outer, int $dot, int $background): void
    {
        $px = ($x + $margin) * $unit;
        $py = ($y + $margin) * $unit;
        $size = $unit * 7;

        $this->gdRoundedRect($image, $px, $py, $size, $size, (int) round($unit * 0.8), $outer);
        $this->gdRoundedRect($image, $px + (int) round($unit * 1.1), $py + (int) round($unit * 1.1), (int) round($unit * 4.8), (int) round($unit * 4.8), (int) round($unit * 0.45), $background);
        $this->gdRoundedRect($image, $px + (int) round($unit * 2.05), $py + (int) round($unit * 2.05), (int) round($unit * 2.9), (int) round($unit * 2.9), (int) round($unit * 0.35), $dot);
    }

    protected function gdRoundedRect(\GdImage $image, int $x, int $y, int $width, int $height, int $radius, int $color): void
    {
        $radius = max(0, min($radius, (int) floor(min($width, $height) / 2)));

        imagefilledrectangle($image, $x + $radius, $y, $x + $width - $radius, $y + $height, $color);
        imagefilledrectangle($image, $x, $y + $radius, $x + $width, $y + $height - $radius, $color);
        imagefilledellipse($image, $x + $radius, $y + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x + $width - $radius, $y + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x + $radius, $y + $height - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x + $width - $radius, $y + $height - $radius, $radius * 2, $radius * 2, $color);
    }

    protected function gdRoundedRectOutline(\GdImage $image, int $x, int $y, int $width, int $height, int $radius, int $color): void
    {
        $radius = max(0, min($radius, (int) floor(min($width, $height) / 2)));

        imageline($image, $x + $radius, $y, $x + $width - $radius, $y, $color);
        imageline($image, $x + $radius, $y + $height, $x + $width - $radius, $y + $height, $color);
        imageline($image, $x, $y + $radius, $x, $y + $height - $radius, $color);
        imageline($image, $x + $width, $y + $radius, $x + $width, $y + $height - $radius, $color);
        imagearc($image, $x + $radius, $y + $radius, $radius * 2, $radius * 2, 180, 270, $color);
        imagearc($image, $x + $width - $radius, $y + $radius, $radius * 2, $radius * 2, 270, 360, $color);
        imagearc($image, $x + $radius, $y + $height - $radius, $radius * 2, $radius * 2, 90, 180, $color);
        imagearc($image, $x + $width - $radius, $y + $height - $radius, $radius * 2, $radius * 2, 0, 90, $color);
    }

    protected function gdCenterText(\GdImage $image, string $text, int $font, int $baselineY, int $color): void
    {
        $fontPath = $this->gdFontPath();

        if ($fontPath) {
            $text = trim(strip_tags($text));

            if ($text === '') {
                return;
            }

            $size = match ($font) {
                5 => 18,
                4 => 16,
                3 => 13,
                default => 10,
            };
            $text = (string) str($text)->limit($font === 5 ? 36 : 46, '...');
            $box = imagettfbbox($size, 0, $fontPath, $text);
            $textWidth = $box ? abs($box[2] - $box[0]) : 0;
            imagettftext($image, $size, 0, (int) round((imagesx($image) - $textWidth) / 2), $baselineY, $color, $fontPath, $text);

            return;
        }

        $text = $this->gdText($text, 42);

        if ($text === '') {
            return;
        }

        $textWidth = imagefontwidth($font) * strlen($text);
        imagestring($image, $font, (int) round((imagesx($image) - $textWidth) / 2), $baselineY - imagefontheight($font), $text, $color);
    }

    protected function gdText(string $text, int $limit): string
    {
        $text = trim(strip_tags($text));

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (is_string($converted)) {
                $text = $converted;
            }
        }

        $text = preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
        $text = preg_replace('/\s+/', ' ', $text) ?? '';

        return (string) str(trim($text))->limit($limit, '...');
    }

    protected function gdFontPath(): ?string
    {
        if (! function_exists('imagettftext')) {
            return null;
        }

        foreach ([
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
