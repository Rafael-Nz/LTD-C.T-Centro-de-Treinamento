$filesToUpdate = @(
    "admin\views\partials\financeiro_catalogo.php",
    "admin\views\404.php",
    "admin\views\alunos.php",
    "admin\views\aluno_detalhe.php",
    "admin\views\aluno_form.php",
    "admin\views\ativar-conta.php",
    "admin\views\avaliacao_form.php",
    "admin\views\cargo_form.php",
    "admin\views\configuracoes.php",
    "admin\views\contrato_form.php",
    "admin\views\financeiro.php",
    "admin\views\financeiro_cobrancas.php",
    "admin\views\funcionarios.php",
    "admin\views\funcionario_form.php",
    "admin\views\gerenciar_turma.php",
    "admin\views\home.php",
    "admin\views\locais.php",
    "admin\views\local_form.php",
    "admin\views\login.php",
    "admin\views\modalidade_form.php",
    "admin\views\perfil.php",
    "admin\views\perfil_form.php",
    "admin\views\recuperar-senha.php",
    "admin\views\redefinir-senha.php",
    "admin\views\relatorios.php",
    "admin\views\treinos.php",
    "admin\views\treino_form.php",
    "admin\views\turmas.php",
    "admin\views\turma_form.php",
    "public\js\admin\financeiro.js",
    "public\views\partials\head.php",
    "public\views\ativar-conta.php",
    "public\views\dashboard.php",
    "public\views\home.php",
    "public\views\login.php",
    "public\views\mensalidade.php"
)

$baseDir = "D:\xampp\htdocs\ctt"

foreach ($relPath in $filesToUpdate) {
    $fullPath = Join-Path $baseDir $relPath
    if (!(Test-Path $fullPath)) {
        Write-Warning "File not found: $fullPath"
        continue
    }

    $content = [System.IO.File]::ReadAllText($fullPath, [System.Text.Encoding]::UTF8)

    # 1. Bootstrap CSS
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/bootstrap@5\.3\.8/dist/css/bootstrap\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/bootstrap/css/bootstrap.min.css')
    
    # 2. Bootstrap JS
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/bootstrap@5\.3\.8/dist/js/bootstrap\.bundle\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/bootstrap/js/bootstrap.bundle.min.js')
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/bootstrap@5\.3\.8/dist/js/bootstrap\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/bootstrap/js/bootstrap.min.js')

    # 3. Popper
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/@popperjs/core@2\.11\.[0-9]+/dist/umd/popper\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/popper/popper.min.js')

    # 4. jQuery
    $content = [regex]::Replace($content, 'https://code\.jquery\.com/jquery-3\.7\.1\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/jquery/jquery-3.7.1.min.js')

    # 5. Bootstrap Icons
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/bootstrap-icons@1\.[0-9\.]+/font/bootstrap-icons\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css')

    # 6. Phosphor Icons
    $content = [regex]::Replace($content, '(?:https://cdn\.jsdelivr\.net/npm|https://unpkg\.com)/@phosphor-icons/web@2\.1\.[0-9]+/src/regular/style\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/phosphor-icons/src/regular/style.css')
    $content = [regex]::Replace($content, '(?:https://cdn\.jsdelivr\.net/npm|https://unpkg\.com)/@phosphor-icons/web@2\.1\.[0-9]+/src/bold/style\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/phosphor-icons/src/bold/style.css')

    # 7. SweetAlert2
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/sweetalert2@11(?:\?[^"''\s<>]*)?', '/ctt/plugins/sweetalert2/sweetalert2.all.min.js')

    # 8. DataTables CSS
    $content = [regex]::Replace($content, 'https://cdn\.datatables\.net/2\.[0-9\.]+/css/dataTables\.bootstrap5\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/datatables/css/dataTables.bootstrap5.min.css')
    $content = [regex]::Replace($content, 'https://cdn\.datatables\.net/responsive/3\.[0-9\.]+/css/responsive\.bootstrap5\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/datatables/css/responsive.bootstrap5.min.css')

    # 8. DataTables JS
    $content = [regex]::Replace($content, 'https://cdn\.datatables\.net/2\.[0-9\.]+/js/dataTables\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/datatables/js/dataTables.min.js')
    $content = [regex]::Replace($content, 'https://cdn\.datatables\.net/2\.[0-9\.]+/js/dataTables\.bootstrap5\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/datatables/js/dataTables.bootstrap5.min.js')
    $content = [regex]::Replace($content, 'https://cdn\.datatables\.net/responsive/3\.[0-9\.]+/js/dataTables\.responsive\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/datatables/js/dataTables.responsive.min.js')
    $content = [regex]::Replace($content, 'https://cdn\.datatables\.net/responsive/3\.[0-9\.]+/js/responsive\.bootstrap5\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/datatables/js/responsive.bootstrap5.min.js')

    # 9. Select2
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/select2@4\.1\.0-rc\.0/dist/css/select2\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/select2/css/select2.min.css')
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/select2@4\.1\.0-rc\.0/dist/js/select2\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/select2/js/select2.min.js')
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/select2-bootstrap-5-theme@1\.3\.0/dist/select2-bootstrap-5-theme\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css')

    # 10. OverlayScrollbars
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/overlayscrollbars(?:@[0-9\.]+)?/styles/overlayscrollbars\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/overlayscrollbars/css/overlayscrollbars.min.css')
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/overlayscrollbars(?:@[0-9\.]+)?/browser/overlayscrollbars\.browser\.es6\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js')

    # 11. Tempus Dominus
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/@eonasdan/tempus-dominus@6\.9\.4/dist/css/tempus-dominus\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/tempus-dominus/css/tempus-dominus.min.css')
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/@eonasdan/tempus-dominus@6\.9\.4/dist/js/tempus-dominus\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/tempus-dominus/js/tempus-dominus.min.js')
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/@eonasdan/tempus-dominus@6\.9\.4/dist/js/locales/pt\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/tempus-dominus/js/locales/pt.js')

    # 12. Font Awesome
    # Se script kit fontawesome:
    $content = [regex]::Replace($content, '<script\s+src="https://kit\.fontawesome\.com/[a-zA-Z0-9]+\.js"[^>]*></script>', '<link rel="stylesheet" href="/ctt/plugins/fontawesome/css/all.min.css">')
    # Se css fontawesome:
    $content = [regex]::Replace($content, 'https://cdnjs\.cloudflare\.com/ajax/libs/font-awesome/6\.[0-9\.]+/css/all\.min\.css(?:\?[^"''\s<>]*)?', '/ctt/plugins/fontawesome/css/all.min.css')

    # 13. IMask
    $content = [regex]::Replace($content, 'https://unpkg\.com/imask(?:\?[^"''\s<>]*)?', '/ctt/plugins/imask/imask.min.js')

    # 14. FullCalendar
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/fullcalendar@6\.1\.20/index\.global\.min\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/fullcalendar/index.global.min.js')

    # 15. Chart.js
    $content = [regex]::Replace($content, 'https://cdn\.jsdelivr\.net/npm/chart\.js(?:\?[^"''\s<>]*)?', '/ctt/plugins/chartjs/chart.umd.min.js')

    # 16. Google Fonts
    # Remove preconnects
    $content = [regex]::Replace($content, '\s*<link rel="preconnect" href="https://fonts\.googleapis\.com">\r?\n?', '')
    $content = [regex]::Replace($content, '\s*<link rel="preconnect" href="https://fonts\.gstatic\.com" crossorigin>\r?\n?', '')
    # Troca manrope e inter
    $content = [regex]::Replace($content, 'https://fonts\.googleapis\.com/css2\?family=Manrope[^\s"''>]+', '/ctt/plugins/fonts/manrope.css')
    $content = [regex]::Replace($content, 'https://fonts\.googleapis\.com/css2\?family=Inter[^\s"''>]+', '/ctt/plugins/fonts/inter.css')

    # Remove atributos integrity e crossorigin de scripts/links locais que apontam para /ctt/plugins/
    $content = [regex]::Replace($content, '(?i)(<link[^>]+href="/ctt/plugins/[^"]+")\s+integrity="[^"]*"\s+crossorigin="[^"]*"', '$1')
    $content = [regex]::Replace($content, '(?i)(<link[^>]+href="/ctt/plugins/[^"]+")\s+crossorigin="[^"]*"\s+integrity="[^"]*"', '$1')
    $content = [regex]::Replace($content, '(?i)(<link[^>]+href="/ctt/plugins/[^"]+")\s+integrity="[^"]*"', '$1')
    $content = [regex]::Replace($content, '(?i)(<link[^>]+href="/ctt/plugins/[^"]+")\s+crossorigin="[^"]*"', '$1')

    $content = [regex]::Replace($content, '(?i)(<script[^>]+src="/ctt/plugins/[^"]+")\s+integrity="[^"]*"\s+crossorigin="[^"]*"', '$1')
    $content = [regex]::Replace($content, '(?i)(<script[^>]+src="/ctt/plugins/[^"]+")\s+crossorigin="[^"]*"\s+integrity="[^"]*"', '$1')
    $content = [regex]::Replace($content, '(?i)(<script[^>]+src="/ctt/plugins/[^"]+")\s+integrity="[^"]*"', '$1')
    $content = [regex]::Replace($content, '(?i)(<script[^>]+src="/ctt/plugins/[^"]+")\s+crossorigin="[^"]*"', '$1')

    [System.IO.File]::WriteAllText($fullPath, $content, [System.Text.Encoding]::UTF8)
    Write-Host "Updated: $relPath" -ForegroundColor Green
}
Write-Host "Todas as substituições concluídas!" -ForegroundColor Cyan
