<?php

namespace App\View\Components\Pdf;

use App\Models\Institucion;
use Illuminate\View\Component;
use Illuminate\View\View;

class Header extends Component
{
    public ?string $logo1Path;

    public ?string $logo2Path;

    public string $fecha;

    public function __construct(
        public ?Institucion $institucion = null,
        public string $titulo = 'Reporte',
        public ?string $nombreInstitucion = null,
    ) {
        $this->logo1Path = $this->logoPath($institucion?->logo1);
        $this->logo2Path = $this->logoPath($institucion?->logo2);
        $this->fecha = now()->timezone('America/Guayaquil')->format('d/m/Y H:i');
    }

    private function logoPath(?string $logo): ?string
    {
        if (! $logo) {
            return null;
        }
        $base = realpath(storage_path('app/public'));
        $path = realpath(storage_path('app/public/'.$logo));
        if (! $base || ! $path || ! str_starts_with($path, $base.DIRECTORY_SEPARATOR) || ! is_file($path)) {
            return null;
        }
        if (! extension_loaded('gd') && (getimagesize($path)[2] ?? null) !== IMAGETYPE_JPEG) {
            $compatible = storage_path('app/pdf-logos/'.hash_file('sha256', $path).'.jpg');

            return is_file($compatible) ? $compatible : null;
        }

        return $path;
    }

    public function render(): View
    {
        return view('components.pdf.header');
    }
}
