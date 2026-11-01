@foreach([
    'razor-labs.svg' => 'Razor Labs',
    'cys-group.svg' => 'CYS Group',
    'siderise.png' => 'Siderise',
    'globalsys.svg' => 'Globalsys',
    'magna.jpg' => 'Magna',
] as $clientImage => $clientName)
    <div @class(['item' => !($clientLogoGrid ?? false)])>
        <img src="{{ asset('frontend/images/client-logo/'.$clientImage) }}"
             alt="{{ $clientName }}" width="150" height="77" loading="lazy" decoding="async"
             style="max-width:100%;object-fit:contain">
    </div>
@endforeach
