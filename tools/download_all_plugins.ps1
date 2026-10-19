[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$basePlugins = "D:\xampp\htdocs\ctt\plugins"

function Download-Asset {
    param(
        [string]$Url,
        [string]$DestPath
    )
    $dir = Split-Path $DestPath
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }

    Write-Host "Downloading $Url -> $DestPath"
    $wc = New-Object System.Net.WebClient
    $wc.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36")
    try {
        $wc.DownloadFile($Url, $DestPath)
        Write-Host "OK ($((Get-Item $DestPath).Length) bytes)" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: $_" -ForegroundColor Red
    }
}

# 1. Bootstrap 5.3.8
Download-Asset "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" "$basePlugins\bootstrap\css\bootstrap.min.css"
Download-Asset "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" "$basePlugins\bootstrap\js\bootstrap.bundle.min.js"
Download-Asset "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" "$basePlugins\bootstrap\js\bootstrap.min.js"

# 2. Popper.js 2.11.8
Download-Asset "https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" "$basePlugins\popper\popper.min.js"

# 3. jQuery 3.7.1
Download-Asset "https://code.jquery.com/jquery-3.7.1.min.js" "$basePlugins\jquery\jquery-3.7.1.min.js"

# 4. Bootstrap Icons 1.13.1
Download-Asset "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" "$basePlugins\bootstrap-icons\font\bootstrap-icons.min.css"
Download-Asset "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/fonts/bootstrap-icons.woff2" "$basePlugins\bootstrap-icons\font\fonts\bootstrap-icons.woff2"
Download-Asset "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/fonts/bootstrap-icons.woff" "$basePlugins\bootstrap-icons\font\fonts\bootstrap-icons.woff"

# 5. Phosphor Icons 2.1.1 e 2.1.2
Download-Asset "https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" "$basePlugins\phosphor-icons\src\regular\style.css"
Download-Asset "https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/Phosphor.woff2" "$basePlugins\phosphor-icons\src\regular\Phosphor.woff2"
Download-Asset "https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/Phosphor.woff" "$basePlugins\phosphor-icons\src\regular\Phosphor.woff"
Download-Asset "https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/Phosphor.ttf" "$basePlugins\phosphor-icons\src\regular\Phosphor.ttf"

Download-Asset "https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" "$basePlugins\phosphor-icons\src\bold\style.css"
Download-Asset "https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/Phosphor-Bold.woff2" "$basePlugins\phosphor-icons\src\bold\Phosphor-Bold.woff2"
Download-Asset "https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/Phosphor-Bold.woff" "$basePlugins\phosphor-icons\src\bold\Phosphor-Bold.woff"
Download-Asset "https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/Phosphor-Bold.ttf" "$basePlugins\phosphor-icons\src\bold\Phosphor-Bold.ttf"

# 6. SweetAlert2 11
Download-Asset "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" "$basePlugins\sweetalert2\sweetalert2.all.min.js"
Download-Asset "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" "$basePlugins\sweetalert2\sweetalert2.min.css"

# 7. DataTables 2.3.8 & Responsive 3.0.8
Download-Asset "https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.min.css" "$basePlugins\datatables\css\dataTables.bootstrap5.min.css"
Download-Asset "https://cdn.datatables.net/responsive/3.0.8/css/responsive.bootstrap5.min.css" "$basePlugins\datatables\css\responsive.bootstrap5.min.css"
Download-Asset "https://cdn.datatables.net/2.3.8/js/dataTables.min.js" "$basePlugins\datatables\js\dataTables.min.js"
Download-Asset "https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.min.js" "$basePlugins\datatables\js\dataTables.bootstrap5.min.js"
Download-Asset "https://cdn.datatables.net/responsive/3.0.8/js/dataTables.responsive.min.js" "$basePlugins\datatables\js\dataTables.responsive.min.js"
Download-Asset "https://cdn.datatables.net/responsive/3.0.8/js/responsive.bootstrap5.min.js" "$basePlugins\datatables\js\responsive.bootstrap5.min.js"

# 8. Select2 & Bootstrap 5 Theme
Download-Asset "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" "$basePlugins\select2\css\select2.min.css"
Download-Asset "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" "$basePlugins\select2\js\select2.min.js"
Download-Asset "https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" "$basePlugins\select2\css\select2-bootstrap-5-theme.min.css"

# 9. OverlayScrollbars
Download-Asset "https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css" "$basePlugins\overlayscrollbars\css\overlayscrollbars.min.css"
Download-Asset "https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js" "$basePlugins\overlayscrollbars\js\overlayscrollbars.browser.es6.min.js"

# 10. Tempus Dominus 6.9.4
Download-Asset "https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" "$basePlugins\tempus-dominus\css\tempus-dominus.min.css"
Download-Asset "https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" "$basePlugins\tempus-dominus\js\tempus-dominus.min.js"
Download-Asset "https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/locales/pt.js" "$basePlugins\tempus-dominus\js\locales\pt.js"

# 11. Font Awesome 6.5.2
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" "$basePlugins\fontawesome\css\all.min.css"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" "$basePlugins\fontawesome\js\all.min.js"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-solid-900.woff2" "$basePlugins\fontawesome\webfonts\fa-solid-900.woff2"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-solid-900.ttf" "$basePlugins\fontawesome\webfonts\fa-solid-900.ttf"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-regular-400.woff2" "$basePlugins\fontawesome\webfonts\fa-regular-400.woff2"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-regular-400.ttf" "$basePlugins\fontawesome\webfonts\fa-regular-400.ttf"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-brands-400.woff2" "$basePlugins\fontawesome\webfonts\fa-brands-400.woff2"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-brands-400.ttf" "$basePlugins\fontawesome\webfonts\fa-brands-400.ttf"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-v4compatibility.woff2" "$basePlugins\fontawesome\webfonts\fa-v4compatibility.woff2"
Download-Asset "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-v4compatibility.ttf" "$basePlugins\fontawesome\webfonts\fa-v4compatibility.ttf"

# 12. IMask
Download-Asset "https://unpkg.com/imask/dist/imask.min.js" "$basePlugins\imask\imask.min.js"

# 13. FullCalendar 6.1.20
Download-Asset "https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js" "$basePlugins\fullcalendar\index.global.min.js"

# 14. Chart.js 4.4.7
Download-Asset "https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js" "$basePlugins\chartjs\chart.umd.min.js"

Write-Host "Download concluído!" -ForegroundColor Cyan
