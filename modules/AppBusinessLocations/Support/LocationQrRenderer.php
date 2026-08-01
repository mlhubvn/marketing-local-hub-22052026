<?php

namespace Modules\AppBusinessLocations\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Str;
use Modules\AppBusinessLocations\Models\BusinessLocation;

class LocationQrRenderer
{
    public function render(BusinessLocation $location, ?array $design = null): string
    {
        $design = LocationQrStyleCatalog::normalize($design ?? (array) ($location->qr_design ?? []));
        $target = $location->qrTargetUrl();
        $qr = $this->renderQr($target, $design);

        if (($design['frame_style'] ?? 'card') === 'minimal') {
            return $qr;
        }

        return $this->wrapFrame($qr, $location, $design);
    }

    protected function renderQr(string $value, array $design): string
    {
        $pattern = (string) ($design['pattern'] ?? 'square');

        return match ($pattern) {
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
                if (! $matrix->get($x, $y)) {
                    continue;
                }

                $svg[] = '<rect x="'.(($x + $margin) * $unit).'" y="'.(($y + $margin) * $unit).'" width="'.$unit.'" height="'.$unit.'" fill="'.$foreground.'"/>';
            }
        }

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

        $svg = [
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$canvasSize.' '.$canvasSize.'" width="'.$canvasSize.'" height="'.$canvasSize.'" role="img" aria-label="QR code">',
            '<defs><linearGradient id="location-qr-gradient" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="'.$foreground.'"/><stop offset="100%" stop-color="'.$accent.'"/></linearGradient></defs>',
            '<rect width="100%" height="100%" fill="'.$background.'"/>',
        ];

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (! $matrix->get($x, $y) || $this->isFinder($x, $y, $matrixSize)) {
                    continue;
                }

                $svg[] = '<rect x="'.(($x + $margin) * $unit).'" y="'.(($y + $margin) * $unit).'" width="'.($unit * 0.92).'" height="'.($unit * 0.92).'" rx="'.($unit * 0.45).'" fill="url(#location-qr-gradient)"/>';
            }
        }

        $this->finder($svg, $margin, $unit, 0, 0, $foreground, $accent, $background);
        $this->finder($svg, $margin, $unit, $matrixSize - 7, 0, $foreground, $accent, $background);
        $this->finder($svg, $margin, $unit, 0, $matrixSize - 7, $foreground, $accent, $background);

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

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    protected function wrapFrame(string $qrSvg, BusinessLocation $location, array $design): string
    {
        $qrEmbedded = $this->embedQrSvg($qrSvg, 86, 96, 348, 348);
        $background = $this->hex((string) $design['background_color'], '#ffffff');
        $surface = $this->hex((string) $design['surface_color'], '#ecfeff');
        $accent = $this->hex((string) $design['accent_color'], '#14b8a6');
        $foreground = $this->hex((string) $design['foreground_color'], '#0f172a');
        $label = $this->escape((string) $design['label']);
        $name = $this->escape(Str::limit((string) $location->name, 34));
        $address = $this->escape(Str::limit((string) $location->address, 46));
        $frameStyle = (string) ($design['frame_style'] ?? 'card');
        $height = $frameStyle === 'label' ? 560 : 620;

        $svg = [
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 520 '.$height.'" width="520" height="'.$height.'" role="img" aria-label="Location QR code">',
            '<rect width="520" height="'.$height.'" rx="32" fill="'.$surface.'"/>',
            '<rect x="18" y="18" width="484" height="'.($height - 36).'" rx="28" fill="'.$background.'" stroke="'.$accent.'" stroke-opacity="0.25" stroke-width="2"/>',
            '<text x="260" y="66" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="20" font-weight="700" fill="'.$foreground.'">'.$label.'</text>',
            $qrEmbedded,
        ];

        if ((bool) ($design['show_location_name'] ?? true)) {
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

    protected function embedQrSvg(string $qrSvg, int $x, int $y, int $width, int $height): string
    {
        return preg_replace_callback('/<svg\b([^>]*)>/', function (array $matches) use ($x, $y, $width, $height): string {
            $attributes = preg_replace('/\s(?:x|y|width|height)="[^"]*"/', '', $matches[1]) ?? $matches[1];

            return '<svg x="'.$x.'" y="'.$y.'" width="'.$width.'" height="'.$height.'"'.$attributes.'>';
        }, $qrSvg, 1) ?? $qrSvg;
    }
}
