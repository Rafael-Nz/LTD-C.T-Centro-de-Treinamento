<?php

declare(strict_types=1);

/**
 * tools/download_all_plugins.php
 * 
 * Script CLI em PHP puro para download e atualização das dependências de front-end para a pasta plugins/.
 * Utilizado para manutenção e upgrade controlado das bibliotecas offline (Bootstrap, DataTables, FontAwesome, etc.)
 * sem depender de PowerShell ou executáveis externos.
 * 
 * Como a pasta plugins/ já é versionada no repositório para garantir compatibilidade e funcionamento
 * offline imediato no XAMPP, este script deve ser executado apenas quando for necessário atualizar
 * ou recompor as versões dos assets de terceiros.
 * 
 * Uso:
 *   php tools/download_all_plugins.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Este script deve ser executado exclusivamente via linha de comando (CLI).\n";
    exit(1);
}

$rootDir = dirname(__DIR__);
$basePlugins = $rootDir . DIRECTORY_SEPARATOR . 'plugins';

echo "\n======================================================\n";
echo "  Cross C.T | Download de Plugins Offline (PHP Puro)\n";
echo "  Destino: {$basePlugins}\n";
echo "======================================================\n\n";

$assets = [
    // 1. Bootstrap 5.3.8
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css' => 'bootstrap/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js' => 'bootstrap/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js' => 'bootstrap/js/bootstrap.min.js',

    // 2. Popper.js 2.11.8
    'https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js' => 'popper/popper.min.js',

    // 3. jQuery 3.7.1
    'https://code.jquery.com/jquery-3.7.1.min.js' => 'jquery/jquery-3.7.1.min.js',

    // 4. Bootstrap Icons 1.13.1
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css' => 'bootstrap-icons/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/fonts/bootstrap-icons.woff2' => 'bootstrap-icons/font/fonts/bootstrap-icons.woff2',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/fonts/bootstrap-icons.woff' => 'bootstrap-icons/font/fonts/bootstrap-icons.woff',

    // 5. Phosphor Icons 2.1.1 e 2.1.2
    'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css' => 'phosphor-icons/src/regular/style.css',
    'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/Phosphor.woff2' => 'phosphor-icons/src/regular/Phosphor.woff2',
    'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/Phosphor.woff' => 'phosphor-icons/src/regular/Phosphor.woff',
    'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/Phosphor.ttf' => 'phosphor-icons/src/regular/Phosphor.ttf',
    'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css' => 'phosphor-icons/src/bold/style.css',
    'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/Phosphor-Bold.woff2' => 'phosphor-icons/src/bold/Phosphor-Bold.woff2',
    'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/Phosphor-Bold.woff' => 'phosphor-icons/src/bold/Phosphor-Bold.woff',
    'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/Phosphor-Bold.ttf' => 'phosphor-icons/src/bold/Phosphor-Bold.ttf',

    // 6. SweetAlert2 11
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js' => 'sweetalert2/sweetalert2.all.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css' => 'sweetalert2/sweetalert2.min.css',

    // 7. DataTables 2.3.8 & Responsive 3.0.8
    'https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.min.css' => 'datatables/css/dataTables.bootstrap5.min.css',
    'https://cdn.datatables.net/responsive/3.0.8/css/responsive.bootstrap5.min.css' => 'datatables/css/responsive.bootstrap5.min.css',
    'https://cdn.datatables.net/2.3.8/js/dataTables.min.js' => 'datatables/js/dataTables.min.js',
    'https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.min.js' => 'datatables/js/dataTables.bootstrap5.min.js',
    'https://cdn.datatables.net/responsive/3.0.8/js/dataTables.responsive.min.js' => 'datatables/js/dataTables.responsive.min.js',
    'https://cdn.datatables.net/responsive/3.0.8/js/responsive.bootstrap5.min.js' => 'datatables/js/responsive.bootstrap5.min.js',

    // 8. Select2 & Bootstrap 5 Theme
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' => 'select2/css/select2.min.css',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js' => 'select2/js/select2.min.js',
    'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css' => 'select2/css/select2-bootstrap-5-theme.min.css',

    // 9. OverlayScrollbars
    'https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css' => 'overlayscrollbars/css/overlayscrollbars.min.css',
    'https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js' => 'overlayscrollbars/js/overlayscrollbars.browser.es6.min.js',

    // 10. Tempus Dominus 6.9.4
    'https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css' => 'tempus-dominus/css/tempus-dominus.min.css',
    'https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js' => 'tempus-dominus/js/tempus-dominus.min.js',

    // 11. Font Awesome 6.5.2
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css' => 'fontawesome/css/all.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js' => 'fontawesome/js/all.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-solid-900.woff2' => 'fontawesome/webfonts/fa-solid-900.woff2',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-solid-900.ttf' => 'fontawesome/webfonts/fa-solid-900.ttf',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-regular-400.woff2' => 'fontawesome/webfonts/fa-regular-400.woff2',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-regular-400.ttf' => 'fontawesome/webfonts/fa-regular-400.ttf',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-brands-400.woff2' => 'fontawesome/webfonts/fa-brands-400.woff2',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-brands-400.ttf' => 'fontawesome/webfonts/fa-brands-400.ttf',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-v4compatibility.woff2' => 'fontawesome/webfonts/fa-v4compatibility.woff2',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-v4compatibility.ttf' => 'fontawesome/webfonts/fa-v4compatibility.ttf',

    // 12. IMask
    'https://unpkg.com/imask/dist/imask.min.js' => 'imask/imask.min.js',

    // 13. FullCalendar 6.1.20
    'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js' => 'fullcalendar/index.global.min.js',

    // 14. Chart.js 4.4.7
    'https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js' => 'chartjs/chart.umd.min.js',

    // 15. Swagger UI 5.18.2
    'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui.css' => 'swagger-ui/swagger-ui.css',
    'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui-bundle.js' => 'swagger-ui/swagger-ui-bundle.js',
    'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui-standalone-preset.js' => 'swagger-ui/swagger-ui-standalone-preset.js',
];

$total = count($assets);
$current = 0;
$erros = 0;

$hasCurl = function_exists('curl_init');

foreach ($assets as $url => $relativeDest) {
    $current++;
    $destPath = $basePlugins . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDest);
    $dir = dirname($destPath);

    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            echo "[{$current}/{$total}] ERRO ao criar pasta: {$dir}\n";
            $erros++;
            continue;
        }
    }

    echo "[{$current}/{$total}] Baixando: {$relativeDest} ... ";

    $success = false;

    if ($hasCurl) {
        $ch = curl_init($url);
        $fp = fopen($destPath, 'wb');
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (PHP CLI Offline Asset Downloader)',
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $exec = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($exec && $httpCode >= 200 && $httpCode < 300) {
            $success = true;
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'user_agent' => 'Mozilla/5.0 (PHP CLI Offline Asset Downloader)',
                'timeout' => 30,
                'follow_location' => 1
            ],
            'ssl' => [
                'verify_peer' => true
            ]
        ]);
        $content = @file_get_contents($url, false, $context);
        if ($content !== false) {
            file_put_contents($destPath, $content);
            $success = true;
        }
    }

    if ($success && file_exists($destPath) && filesize($destPath) > 0) {
        $size = round(filesize($destPath) / 1024, 1);
        echo "OK ({$size} KB)\n";
    } else {
        echo "FALHOU!\n";
        $erros++;
    }
}

// Garante o locale pt-BR do Tempus Dominus
$ptLocaleFile = $basePlugins . DIRECTORY_SEPARATOR . 'tempus-dominus' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'locales' . DIRECTORY_SEPARATOR . 'pt.js';
if (!file_exists($ptLocaleFile)) {
    @mkdir(dirname($ptLocaleFile), 0777, true);
    file_put_contents($ptLocaleFile, "// Tempus Dominus pt-BR locale provider\n(function () {\n    const ptLocale = { name: 'pt', localization: { today: 'Hoje', clear: 'Limpar seleção', close: 'Fechar', selectMonth: 'Selecionar Mês', previousMonth: 'Mês Anterior', nextMonth: 'Próximo Mês', selectYear: 'Selecionar Ano', previousYear: 'Ano Anterior', nextYear: 'Próximo Ano', selectDecade: 'Selecionar Década', previousDecade: 'Década Anterior', nextDecade: 'Próxima Década', previousCentury: 'Século Anterior', nextCentury: 'Próximo Século', pickHour: 'Escolher Hora', incrementHour: 'Aumentar Hora', decrementHour: 'Diminuir Hora', pickMinute: 'Escolher Minuto', incrementMinute: 'Aumentar Minuto', decrementMinute: 'Diminuir Minuto', pickSecond: 'Escolher Segundo', incrementSecond: 'Aumentar Segundo', decrementSecond: 'Diminuir Segundo', toggleMeridiem: 'Alternar AM/PM', selectTime: 'Selecionar Hora', selectDate: 'Selecionar Data', dayViewHeaderFormat: { month: 'long', year: 'numeric' }, locale: 'pt-BR', startOfTheWeek: 0 } };\n    if (typeof window !== 'undefined' && window.tempusDominus && typeof window.tempusDominus.loadLocale === 'function') { window.tempusDominus.loadLocale(ptLocale); }\n})();\n");
    echo "Configurado locale pt.js para Tempus Dominus.\n";
}

echo "\n------------------------------------------------------\n";
if ($erros === 0) {
    echo "Sucesso! Todos os {$total} assets offline foram baixados e salvos em plugins/.\n";
} else {
    echo "Concluído com {$erros} erro(s). Verifique sua conexão com a internet.\n";
}
echo "------------------------------------------------------\n\n";
