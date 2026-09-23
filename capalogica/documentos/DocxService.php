<?php
declare(strict_types=1);

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;

final class DocxService
{
    public function contrato(array $contrato, array $empresa = []): array
    {
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!is_file($autoload)) {
            throw new RuntimeException('PhpWord no está instalado.');
        }

        require_once $autoload;

        $empresa = $this->normalizarEmpresa($empresa);
        $cliente = $this->normalizarCliente($contrato);
        $hitos = $this->jsonArray($contrato['hitos'] ?? []);
        $integrantes = $this->jsonArray($contrato['integrantes'] ?? []);
        $plantilla = $this->rutaPlantilla();
        if ($plantilla !== '') {
            return $this->contratoDesdePlantilla($plantilla, $contrato, $empresa, $cliente, $hitos);
        }
        $documento = new PhpWord();
        $documento->setDefaultFontName('Arial');
        $documento->setDefaultFontSize(10);
        $seccion = $documento->addSection([
            'marginTop' => Converter::cmToTwip(2),
            'marginBottom' => Converter::cmToTwip(2),
            'marginLeft' => Converter::cmToTwip(2.2),
            'marginRight' => Converter::cmToTwip(2.2),
        ]);
        $this->agregarCabecera($seccion, $empresa);
        $this->agregarPie($seccion, $empresa);

        $seccion->addTitle('CONTRATO DE LOCACIÓN DE SERVICIOS', 1);
        $seccion->addText(
            $empresa['titulo_contrato'] !== '' ? $empresa['titulo_contrato'] : 'PROGRAMA DE ACOMPAÑAMIENTO PARA TITULACIÓN POR TESIS',
            ['bold' => true, 'size' => 12],
            ['alignment' => 'center', 'spaceAfter' => 220]
        );
        $this->parrafo($seccion, 'Las partes y condiciones de este contrato se determinarán con la configuración legal vigente aprobada por Tessa.');
        $this->parrafo($seccion, 'EL LOCADOR: ' . $this->partesEmpresa($empresa));
        $this->parrafo($seccion, 'EL USUARIO: ' . $this->partesCliente($cliente));
        $this->parrafo($seccion, 'LAS PARTES: ' . ($empresa['razon_social'] !== '' ? $empresa['razon_social'] : 'Tessa') . ' y el usuario identificado en este documento.');

        foreach ($this->cargarClausulas() as $clausula) {
            $seccion->addText((string)$clausula['titulo'], ['bold' => true], ['spaceBefore' => 140, 'spaceAfter' => 70]);
            foreach ((array)$clausula['parrafos'] as $texto) {
                $this->parrafo($seccion, $this->reemplazar($texto, $contrato, $empresa, $cliente));
            }
            foreach ((array)($clausula['viñetas'] ?? []) as $texto) {
                $seccion->addListItem($this->reemplazar((string)$texto, $contrato, $empresa, $cliente), 0, null, ['listType' => 'bullet']);
            }
        }

        $seccion->addText('Tarifas de titulación por tesis', ['bold' => true], ['spaceBefore' => 160]);
        $this->tablaTarifas($seccion);
        $seccion->addText('Cronograma de pagos', ['bold' => true], ['spaceBefore' => 160]);
        $this->tablaHitos($seccion, $hitos);
        $seccion->addText('Titularidad de cuenta y medios de pago', ['bold' => true], ['spaceBefore' => 160]);
        $this->tablaMediosPago($seccion, $empresa);
        $seccion->addText('Firmas', ['bold' => true], ['spaceBefore' => 220]);
        $this->firmas($seccion, $empresa, $cliente);
        $this->parrafo($seccion, 'Se suscribe en ' . ($empresa['ciudad'] !== '' ? $empresa['ciudad'] : '__________________') . ', a los ____ días del mes de __________________ de 2026.');

        $temporal = tempnam(sys_get_temp_dir(), 'tessa-docx-');
        if ($temporal === false) throw new RuntimeException('No se pudo preparar el documento.');
        IOFactory::createWriter($documento, 'Word2007')->save($temporal);
        $contenido = file_get_contents($temporal);
        unlink($temporal);
        if ($contenido === false) throw new RuntimeException('No se pudo leer el documento generado.');
        $numero = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string)($contrato['con_numero'] ?? $contrato['id_contrato'] ?? 'contrato')) ?: 'contrato';
        return ['nombre' => 'contrato-' . $numero . '.docx', 'contenido' => $contenido];
    }

    private function contratoDesdePlantilla(string $ruta, array $contrato, array $empresa, array $cliente, array $hitos): array
    {
        $plantilla = new TemplateProcessor($ruta);

        $fecha = (string)($contrato['fecha'] ?? date('Y-m-d'));
        $timestamp = strtotime($fecha) ?: time();
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $plantilla->setValues([
            'RAZON_SOCIAL' => $empresa['razon_social'],
            'RUC' => $empresa['ruc'],
            'DOMICILIO_LEGAL' => $empresa['domicilio_legal'],
            'REPRESENTANTE_NOMBRE' => $empresa['representante_nombre'],
            'REPRESENTANTE_DOCUMENTO' => $empresa['representante_documento'],
            'NOMBRE_CLIENTE' => $cliente['nombre'],
            'DNI_CLIENTE' => $cliente['documento'],
            'DIRECCION_CLIENTE' => $cliente['direccion'],
            'CORREO_CLIENTE' => $cliente['correo'],
            'CIUDAD' => $empresa['ciudad'],
            'TELEFONOS_CONTACTO' => $empresa['pie_telefonos'],
            'DIA_FIRMA' => date('j', $timestamp),
            'MES_FIRMA' => $meses[(int)date('n', $timestamp)],
            'ANIO_FIRMA' => date('Y', $timestamp),
        ]);

        if ($hitos !== []) {
            $plantilla->cloneRow('hito_numero', count($hitos));
            foreach (array_values($hitos) as $indice => $hito) {
                $numero = $indice + 1;
                $plantilla->setValues([
                    'hito_numero#' . $numero => (string)($hito['numero'] ?? $numero),
                    'hito_descripcion#' . $numero => (string)($hito['descripcion'] ?? ('Cuota ' . $numero)),
                    'hito_fecha#' . $numero => $this->fechaLarga((string)($hito['fecha_vencimiento'] ?? '')),
                    'hito_monto#' . $numero => 'S/ ' . number_format((float)($hito['monto'] ?? 0), 2),
                    'servicio_nombre#' . $numero => (string)($contrato['servicio_nombre'] ?? $contrato['servicio'] ?? 'Servicio contratado'),
                ]);
            }
        }

        $medios = $this->mediosPago($empresa);
        if ($medios !== []) {
            $plantilla->cloneRow('medio_pago', count($medios));
            foreach (array_values($medios) as $indice => $medio) {
                $numero = $indice + 1;
                $plantilla->setValues([
                    'medio_pago#' . $numero => (string)($medio['medio'] ?? ''),
                    'titular_pago#' . $numero => (string)($medio['titular'] ?? ''),
                    'numero_pago#' . $numero => (string)($medio['numero'] ?? ''),
                    'cci_pago#' . $numero => (string)($medio['cci'] ?? ''),
                ]);
            }
        } else {
            $plantilla->setValues(['medio_pago' => '', 'titular_pago' => '', 'numero_pago' => '', 'cci_pago' => '']);
        }

        $temporal = tempnam(sys_get_temp_dir(), 'tessa-docx-');
        if ($temporal === false) throw new RuntimeException('No se pudo preparar el documento.');
        $plantilla->saveAs($temporal);
        $contenido = file_get_contents($temporal);
        unlink($temporal);
        if ($contenido === false) throw new RuntimeException('No se pudo leer el documento generado.');
        $numero = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string)($contrato['con_numero'] ?? $contrato['id_contrato'] ?? 'contrato')) ?: 'contrato';
        return ['nombre' => 'contrato-' . $numero . '.docx', 'contenido' => $contenido];
    }

    private function agregarCabecera(object $seccion, array $empresa): void
    {
        $cabecera = $seccion->addHeader();
        $tabla = $cabecera->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $tabla->addRow();
        $celdaLogo = $tabla->addCell(Converter::cmToTwip(5.4));
        $logo = dirname(__DIR__, 2) . '/assets/recursos/imagenes/tessa-logo-contrato.png';
        if (is_file($logo)) $celdaLogo->addImage($logo, ['width' => 145, 'height' => 38]);
        $celdaTitulo = $tabla->addCell(Converter::cmToTwip(11), ['valign' => 'center']);
        $celdaTitulo->addText($empresa['titulo_contrato'] !== '' ? $empresa['titulo_contrato'] : 'CONTRATO DE TITULACIÓN POR TESIS', ['bold' => true, 'size' => 11], ['alignment' => 'center']);
    }

    private function agregarPie(object $seccion, array $empresa): void
    {
        $pie = $seccion->addFooter();
        $texto = trim($empresa['pie_telefonos']) !== '' ? 'TESSA | ' . $empresa['pie_telefonos'] : 'TESSA';
        $pie->addText($texto, ['size' => 9], ['alignment' => 'center']);
    }

    private function tablaHitos(object $seccion, array $hitos): void
    {
        $tabla = $seccion->addTable(['borderSize' => 6, 'borderColor' => 'B7B7B7', 'cellMargin' => 80]);
        $tabla->addRow();
        foreach (['Cuota', 'Descripción', 'Vencimiento', 'Importe'] as $encabezado) $tabla->addCell()->addText($encabezado, ['bold' => true]);
        $total = 0.0;
        foreach ($hitos as $hito) {
            $monto = (float)($hito['monto'] ?? 0);
            $total += $monto;
            $tabla->addRow();
            $tabla->addCell()->addText((string)($hito['numero'] ?? ''));
            $tabla->addCell()->addText((string)($hito['descripcion'] ?? ''));
            $tabla->addCell()->addText($this->fechaLarga((string)($hito['fecha_vencimiento'] ?? '')));
            $tabla->addCell()->addText('S/ ' . number_format($monto, 2));
        }
        $tabla->addRow();
        $tabla->addCell()->addText('');
        $tabla->addCell()->addText('');
        $tabla->addCell()->addText('Total', ['bold' => true]);
        $tabla->addCell()->addText('S/ ' . number_format($total, 2), ['bold' => true]);
    }

    private function tablaTarifas(object $seccion): void
    {
        $tabla = $seccion->addTable(['borderSize' => 6, 'borderColor' => 'B7B7B7', 'cellMargin' => 80]);
        $tabla->addRow();
        foreach (['Nivel', 'Precio total', '40% - Firma', '40% - Entrega plan', '20% - A los 2 meses'] as $encabezado) $tabla->addCell()->addText($encabezado, ['bold' => true]);
        foreach ([['N1', 3500], ['N2', 4500], ['N3', 5500], ['N4', 6500]] as [$nivel, $total]) {
            $tabla->addRow();
            $tabla->addCell()->addText($nivel);
            $tabla->addCell()->addText('S/ ' . number_format($total, 2));
            $tabla->addCell()->addText('S/ ' . number_format($total * 0.4, 2));
            $tabla->addCell()->addText('S/ ' . number_format($total * 0.4, 2));
            $tabla->addCell()->addText('S/ ' . number_format($total * 0.2, 2));
        }
    }

    private function tablaMediosPago(object $seccion, array $empresa): void
    {
        $tabla = $seccion->addTable(['borderSize' => 6, 'borderColor' => 'B7B7B7', 'cellMargin' => 80]);
        $tabla->addRow();
        foreach (['Medio', 'Titular', 'Número', 'CCI'] as $encabezado) $tabla->addCell()->addText($encabezado, ['bold' => true]);
        $medios = $this->mediosPago($empresa);
        foreach ($medios as $medio) {
            $tabla->addRow();
            foreach (['medio', 'titular', 'numero', 'cci'] as $campo) $tabla->addCell()->addText((string)($medio[$campo] ?? ''));
        }
        if ($medios === []) {
            $tabla->addRow();
            $tabla->addCell(Converter::cmToTwip(15))->addText('Medios de pago pendientes de configuración y aprobación.');
        }
    }

    private function firmas(object $seccion, array $empresa, array $cliente): void
    {
        $tabla = $seccion->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $tabla->addRow();
        $locador = $tabla->addCell(Converter::cmToTwip(8));
        $usuario = $tabla->addCell(Converter::cmToTwip(8));
        $locador->addText('__________________________________', [], ['alignment' => 'center']);
        $locador->addText('EL LOCADOR', ['bold' => true], ['alignment' => 'center']);
        $locador->addText($this->partesRepresentante($empresa), [], ['alignment' => 'center']);
        $usuario->addText('__________________________________', [], ['alignment' => 'center']);
        $usuario->addText('EL USUARIO', ['bold' => true], ['alignment' => 'center']);
        $usuario->addText($this->partesCliente($cliente), [], ['alignment' => 'center']);
    }

    private function rutaPlantilla(): string
    {
        $ruta = trim((string)(getenv('TESSA_CONTRATO_PLANTILLA') ?: ''));
        if ($ruta === '') $ruta = dirname(__DIR__, 2) . '/storage/plantillas/contrato-tessa.docx';
        return is_file($ruta) ? $ruta : '';
    }

    private function cargarClausulas(): array
    {
        $ruta = trim((string)(getenv('TESSA_CONTRATO_CLAUSULAS') ?: ''));
        if ($ruta === '' || !is_file($ruta)) return [];
        $datos = json_decode((string)file_get_contents($ruta), true);
        return is_array($datos) ? $datos : [];
    }

    private function mediosPago(array $empresa = []): array
    {
        $configurados = $this->jsonArray($empresa['medios_pago'] ?? []);
        if ($configurados !== []) return $configurados;
        $ruta = trim((string)(getenv('TESSA_CONTRATO_MEDIOS_PAGO') ?: ''));
        if ($ruta === '' || !is_file($ruta)) return [];
        $datos = json_decode((string)file_get_contents($ruta), true);
        return is_array($datos) ? $datos : [];
    }

    private function normalizarEmpresa(array $empresa): array
    {
        return array_merge(array_fill_keys(['razon_social', 'ruc', 'domicilio_legal', 'representante_nombre', 'representante_cargo', 'representante_documento', 'ciudad', 'titulo_contrato', 'pie_telefonos'], ''), $empresa);
    }

    private function normalizarCliente(array $contrato): array
    {
        $nombre = trim((string)($contrato['nombres'] ?? '') . ' ' . (string)($contrato['apellidos'] ?? ''));
        return [
            'nombre' => (string)($contrato['cliente_label'] ?? $nombre),
            'documento' => (string)($contrato['documento'] ?? ''),
            'direccion' => (string)($contrato['direccion'] ?? ''),
            'correo' => (string)($contrato['correo'] ?? ''),
        ];
    }

    private function partesEmpresa(array $empresa): string
    {
        return trim($empresa['razon_social'] . ($empresa['ruc'] !== '' ? ', RUC N.º ' . $empresa['ruc'] : '') . ($empresa['domicilio_legal'] !== '' ? ', domicilio ' . $empresa['domicilio_legal'] : ''));
    }

    private function partesRepresentante(array $empresa): string
    {
        return trim($empresa['representante_nombre'] . ($empresa['representante_cargo'] !== '' ? ', ' . $empresa['representante_cargo'] : '') . ($empresa['representante_documento'] !== '' ? ' | DNI N.º ' . $empresa['representante_documento'] : ''));
    }

    private function partesCliente(array $cliente): string
    {
        return trim($cliente['nombre'] . ($cliente['documento'] !== '' ? ', DNI N.º ' . $cliente['documento'] : '') . ($cliente['direccion'] !== '' ? ', domicilio ' . $cliente['direccion'] : '') . ($cliente['correo'] !== '' ? ', correo electrónico ' . $cliente['correo'] : ''));
    }

    private function reemplazar(string $texto, array $contrato, array $empresa, array $cliente): string
    {
        return strtr($texto, ['{RAZON_SOCIAL}' => $empresa['razon_social'], '{RUC}' => $empresa['ruc'], '{NOMBRE_CLIENTE}' => $cliente['nombre'], '{DNI_CLIENTE}' => $cliente['documento'], '{CIUDAD}' => $empresa['ciudad'], '{NUMERO_CONTRATO}' => (string)($contrato['con_numero'] ?? '')]);
    }

    private function parrafo(object $seccion, string $texto): void
    {
        $seccion->addText($texto, [], ['alignment' => 'both', 'spaceAfter' => 90]);
    }

    private function jsonArray(mixed $valor): array
    {
        if (is_string($valor)) $valor = json_decode($valor, true);
        return is_array($valor) ? $valor : [];
    }

    private function fechaLarga(string $fecha): string
    {
        $timestamp = strtotime($fecha);
        if ($timestamp === false) return $fecha;
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return date('j', $timestamp) . ' de ' . $meses[(int)date('n', $timestamp)] . ' de ' . date('Y', $timestamp);
    }
}
