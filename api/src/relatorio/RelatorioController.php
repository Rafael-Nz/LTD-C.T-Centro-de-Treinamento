<?php
namespace Relatorio;

use Core\Http\Controller;

class RelatorioController extends Controller
{
    private RelatorioService $service;

    public function __construct()
    {
        $this->service = new RelatorioService();
    }

    public function metricas(): void
    {
        $this->json($this->service->metricas());
    }

    public function gerar(): void
    {
        try {
            $this->json($this->service->gerar((string) $this->input('tipo', ''), $this->filters()));
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('[RelatorioController::gerar] ' . $e->getMessage());
            $this->error('Erro ao gerar relatorio.', 500);
        }
    }

    public function exportar(): void
    {
        try {
            $result = $this->service->gerar((string) $this->input('tipo', ''), $this->filters());
            $rows = $result['registros'];
            $formato = strtolower((string) $this->input('formato', 'csv'));
            if (!in_array($formato, ['csv', 'xlsx'], true)) {
                throw new \InvalidArgumentException('Formato de exportacao invalido.');
            }

            $rows = $this->mascararDados($rows);
            if ($formato === 'xlsx') {
                $this->enviarXlsx($result['tipo'], $rows);
            }

            $this->enviarCsv($result['tipo'], $rows);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        }
    }

    private function filters(): array
    {
        return [
            'modalidade' => $this->input('modalidade', ''),
            'aluno' => $this->input('aluno', ''),
            'turma' => $this->input('turma', ''),
            'cargo' => $this->input('cargo', ''),
            'dataInicio' => $this->input('dataInicio', ''),
            'dataFim' => $this->input('dataFim', ''),
            'status' => $this->input('status', ''),
        ];
    }

    private function mascararCpf(?string $cpf): string
    {
        $digitos = preg_replace('/\D/', '', (string) $cpf);
        if (strlen($digitos) <= 4) {
            return $digitos ?: '-';
        }
        return str_repeat('*', strlen($digitos) - 4) . substr($digitos, -4);
    }

    private function mascararDados(array $rows): array
    {
        foreach ($rows as &$row) {
            if (isset($row['cpf'])) {
                $row['cpf'] = $this->mascararCpf($row['cpf']);
            }
        }
        unset($row);
        return $rows;
    }

    private function enviarCsv(string $tipo, array $rows): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio-' . $tipo . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, "\xEF\xBB\xBF");
        if ($rows) {
            fputcsv($output, array_keys($rows[0]), ';');
            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }
        }
        fclose($output);
        exit;
    }

    private function enviarXlsx(string $tipo, array $rows): void
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('A extensao ZIP do PHP e necessaria para exportar XLSX.');
        }

        $headers = $rows ? array_keys($rows[0]) : [];
        $sheetRows = [];
        if ($headers) {
            $sheetRows[] = $headers;
        }
        foreach ($rows as $row) {
            $sheetRows[] = array_values($row);
        }

        $zip = new \ZipArchive();
        $arquivo = tempnam(sys_get_temp_dir(), 'relatorio-');
        if ($zip->open($arquivo, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Nao foi possivel criar o arquivo XLSX.');
        }
        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->relsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($sheetRows));
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="relatorio-' . $tipo . '.xlsx"');
        readfile($arquivo);
        unlink($arquivo);
        exit;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function sheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($rows as $rowIndex => $row) {
            $xml .= '<row r="' . ($rowIndex + 1) . '">';
            foreach (array_values($row) as $columnIndex => $value) {
                $column = '';
                $index = $columnIndex + 1;
                while ($index > 0) {
                    $remainder = ($index - 1) % 26;
                    $column = chr(65 + $remainder) . $column;
                    $index = intdiv($index - 1, 26);
                }
                $xml .= '<c r="' . $column . ($rowIndex + 1) . '" t="inlineStr"><is><t>' . $this->xml((string) ($value ?? '')) . '</t></is></c>';
            }
            $xml .= '</row>';
        }
        return $xml . '</sheetData></worksheet>';
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';
    }

    private function relsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Relatorio" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>';
    }
}
