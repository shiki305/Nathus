<?php

class PdfSimples {
    private const LARGURA = 595.28;
    private const ALTURA = 841.89;

    private array $paginas = [];
    private string $buf = "";
    private float $y = 0;
    private array $logo = [];

    public function __construct() {
        $this->novaPagina(false);
    }

    public function y(): float {
        return $this->y;
    }

    public function irPara(float $y): void {
        $this->y = $y;
    }

    public function novaPagina(bool $guardar = true): void {
        if ($guardar && $this->buf !== "") {
            $this->paginas[] = $this->buf;
        }
        $this->buf = "";
        $this->y = self::ALTURA - 36;
    }

    public function espaco(float $altura): bool {
        if ($this->y - $altura < 40) {
            $this->novaPagina();
            return true;
        }
        return false;
    }

    public function descer(float $quantidade): void {
        $this->y -= $quantidade;
    }

    public function retangulo(float $x, float $y, float $largura, float $altura, array $rgb): void {
        $this->buf .= sprintf(
            "%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re f\n",
            $rgb[0],
            $rgb[1],
            $rgb[2],
            $x,
            $y,
            $largura,
            $altura
        );
    }

    public function texto(float $x, float $baseline, float $tamanho, string $texto, bool $negrito = false, array $rgb = [0.14, 0.08, 0.18]): void {
        $fonte = $negrito ? "F2" : "F1";
        $this->buf .= sprintf("%.3f %.3f %.3f rg\n", $rgb[0], $rgb[1], $rgb[2]);
        $this->buf .= sprintf(
            "BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $fonte,
            $tamanho,
            $x,
            $baseline,
            $this->escapar($texto)
        );
    }

    public function desenharLogo(string $arquivo, float $x, float $y, float $largura): bool {
        $imagem = function_exists("imagecreatefrompng") ? @imagecreatefrompng($arquivo) : false;
        if ($imagem === false) {
            return false;
        }

        imagepalettetotruecolor($imagem);
        $w = imagesx($imagem);
        $h = imagesy($imagem);
        $rgb = "";
        $alfa = "";
        for ($linha = 0; $linha < $h; $linha++) {
            for ($coluna = 0; $coluna < $w; $coluna++) {
                $pixel = imagecolorat($imagem, $coluna, $linha);
                $rgb .= chr(($pixel >> 16) & 255) . chr(($pixel >> 8) & 255) . chr($pixel & 255);
                // O GD usa 0 para opaco. A máscara do PDF usa 255.
                $alfa .= chr((int) round((127 - ($pixel >> 24)) * 255 / 127));
            }
        }

        $this->logo = ["largura" => $w, "altura" => $h, "rgb" => gzcompress($rgb), "alfa" => gzcompress($alfa)];
        $this->buf .= sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /Logo Do Q\n", $largura, $largura * $h / $w, $x, $y);
        return true;
    }

    public function emitir(): string {
        if ($this->buf !== "") {
            $this->paginas[] = $this->buf;
            $this->buf = "";
        }

        $objetos = [];
        $objetos[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objetos[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        $kids = [];
        $id = 5;
        $recursosLogo = "";
        if ($this->logo !== []) {
            $logoId = $id++;
            $mascaraId = $id++;
            $dimensoes = "/Width " . $this->logo["largura"] . " /Height " . $this->logo["altura"];
            $imagem = "/Type /XObject /Subtype /Image {$dimensoes} /BitsPerComponent 8 /Filter /FlateDecode";
            $objetos[$logoId] = $this->fluxo($this->logo["rgb"], "$imagem /ColorSpace /DeviceRGB /SMask $mascaraId 0 R");
            $objetos[$mascaraId] = $this->fluxo($this->logo["alfa"], "$imagem /ColorSpace /DeviceGray");
            $recursosLogo = " /XObject << /Logo $logoId 0 R >>";
        }

        foreach ($this->paginas as $conteudo) {
            $paginaId = $id++;
            $fluxoId = $id++;
            $kids[] = $paginaId . " 0 R";
            $objetos[$paginaId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 "
                . self::LARGURA . " " . self::ALTURA
                . "] /Resources << /Font << /F1 3 0 R /F2 4 0 R >>{$recursosLogo} >> /Contents {$fluxoId} 0 R >>";
            $objetos[$fluxoId] = $this->fluxo($conteudo);
        }

        $objetos[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objetos[2] = "<< /Type /Pages /Kids [" . implode(" ", $kids) . "] /Count " . count($this->paginas) . " >>";
        ksort($objetos);

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        $maximo = max(array_keys($objetos));
        for ($i = 1; $i <= $maximo; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i . " 0 obj\n" . $objetos[$i] . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . ($maximo + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maximo; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . ($maximo + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        return $pdf;
    }

    private function fluxo(string $conteudo, string $atributos = ""): string {
        return "<< /Length " . strlen($conteudo) . " $atributos >>\nstream\n" . $conteudo . "\nendstream";
    }

    private function escapar(string $texto): string {
        $texto = str_replace(["\r", "\n"], " ", $texto);
        $convertido = @iconv("UTF-8", "Windows-1252//TRANSLIT//IGNORE", $texto);
        if ($convertido !== false) {
            $texto = $convertido;
        }
        return str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $texto);
    }
}

function relatorio_pdf_cortar(string $texto, float $largura, float $tamanho): string {
    $texto = trim($texto);
    $maximo = max(1, (int) floor($largura / ($tamanho * 0.5)));
    if (mb_strlen($texto) <= $maximo) {
        return $texto;
    }
    return rtrim(mb_substr($texto, 0, $maximo - 3)) . "...";
}

function relatorio_gerar_pdf(array $dados, string $promotor): string {
    $pdf = new PdfSimples();
    $ano = (int) $dados["ano"];
    $margem = 36;
    $cinza = [0.47, 0.42, 0.51];
    $escuro = [0.14, 0.08, 0.18];

    $amarelo = [245 / 255, 182 / 255, 0];
    $grafite = [32 / 255, 32 / 255, 38 / 255];
    $roxo = [0.165, 0.118, 0.314];
    $pdf->retangulo(0, 785, 595.28, 56.89, $roxo);
    if (!$pdf->desenharLogo(__DIR__ . "/../assets/Logo.png", $margem, 808, 90)) {
        $pdf->texto($margem, 812, 16, "Nathus", true, [1, 1, 1]);
    }
    $pdf->texto(146, 813, 14, "Relatório de solicitações " . $ano, true, [1, 1, 1]);

    $pdf->irPara(760);
    $pdf->texto($margem, $pdf->y(), 12, $promotor !== "" ? $promotor : "Promotor", true, $grafite);
    $pdf->descer(16);
    $pdf->texto($margem, $pdf->y(), 9, "Gerado em " . date("d/m/Y") . " às " . date("H:i"), false, $cinza);
    $pdf->descer(22);

    $pdf->texto($margem, $pdf->y(), 12, "Resumo", true, $escuro);
    $pdf->descer(16);

    $resumo = [
        ["Recebidas", (string) $dados["total"], true],
        ["Aceitas", (string) $dados["aceitas"], true],
        ["Taxa de aceite", $dados["taxa"] === null ? "-" : $dados["taxa"] . "%", true],
        ["Convidados", (string) $dados["convidados"], false],
        ["Pendentes", (string) $dados["contagem"]["Pendente"], false],
        ["Concluídas", (string) $dados["contagem"]["Concluída"], false],
        ["Recusadas", (string) $dados["contagem"]["Recusada"], false],
        ["Canceladas", (string) $dados["contagem"]["Cancelada"], false],
    ];

    $larguraCard = 124;
    $alturaCard = 42;
    $coluna = 0;
    $topoCards = $pdf->y();
    foreach ($resumo as [$rotulo, $valor, $destaque]) {
        if ($coluna === 4) {
            $coluna = 0;
            $topoCards -= 50;
        }
        $x = $margem + ($coluna * ($larguraCard + 8));
        $fundo = $destaque ? [0.930, 0.908, 0.969] : [0.965, 0.957, 0.980];
        $pdf->retangulo($x, $topoCards - $alturaCard, $larguraCard, $alturaCard, $fundo);
        $pdf->texto($x + 8, $topoCards - 13, 8, $rotulo, $destaque, $destaque ? $grafite : $cinza);
        $pdf->texto($x + 8, $topoCards - 34, $destaque ? 17 : 14, $valor, true, $grafite);
        $coluna++;
    }
    $pdf->irPara($topoCards - $alturaCard - 22);

    $pdf->texto($margem, $pdf->y(), 12, "Solicitações em " . $ano, true, $escuro);
    $pdf->descer(14);
    $fraseConvidados = $dados["convidados"] > 0
        ? $dados["convidados"] . " convidados nos eventos aceitos"
        : "Nenhum evento aceito neste ano";
    $pdf->texto($margem, $pdf->y(), 9, $fraseConvidados, false, $cinza);
    $pdf->descer(16);

    if ($dados["total"] === 0) {
        $pdf->texto($margem, $pdf->y(), 10, "Nenhuma solicitação recebida em " . $ano . ".", false, $cinza);
        $pdf->descer(22);
    } else {
        $celula = 43.5;
        $base = $pdf->y() - 46;
        $pico = max(1, (int) $dados["pico"]);
        foreach ($dados["nomes_meses"] as $indice => $nome) {
            $quantidade = (int) $dados["por_mes"][$indice + 1];
            $x = $margem + ($indice * $celula);
            $altura = $quantidade === 0 ? 0 : max(4, ($quantidade / $pico) * 36);
            if ($altura > 0) {
                $pdf->retangulo($x + 10, $base, 16, $altura, $amarelo);
                $rotulo = (string) $quantidade;
                // Os algarismos da Helvetica Bold ocupam 0,556 em cada.
                $larguraRotulo = strlen($rotulo) * 8 * 0.556;
                $pdf->texto($x + 18 - $larguraRotulo / 2, $base + $altura + 4, 8, $rotulo, true, $escuro);
            }
            $pdf->texto($x + 8, $base - 12, 8, $nome, false, $cinza);
        }
        $pdf->irPara($base - 28);
    }

    $pdf->texto($margem, $pdf->y(), 12, "Detalhes", true, $escuro);
    $pdf->descer(14);
    $pdf->texto(
        $margem,
        $pdf->y(),
        9,
        $dados["contagem"]["Pendente"] . " pendentes  |  " . $dados["contagem"]["Concluída"] . " concluídas",
        false,
        $cinza
    );
    $pdf->descer(16);

    if ($dados["total"] === 0) {
        $pdf->texto($margem, $pdf->y(), 10, "Quando chegarem solicitações, elas aparecem aqui.", false, $cinza);
        return $pdf->emitir();
    }

    $colunas = [
        ["Evento", 150],
        ["Cliente", 116],
        ["Data da festa", 112],
        ["Convidados", 72],
        ["Status", 70],
    ];

    $desenharCabecalho = function () use ($pdf, $margem, $colunas, $cinza, $escuro) {
        $pdf->espaco(28);
        $x = $margem;
        foreach ($colunas as $colunaTabela) {
            $pdf->texto($x, $pdf->y(), 8, $colunaTabela[0], true, $cinza);
            $x += $colunaTabela[1];
        }
        $pdf->descer(6);
        $pdf->retangulo($margem, $pdf->y(), 520, 0.6, [0.90, 0.88, 0.93]);
        $pdf->descer(14);
    };

    $desenharCabecalho();
    foreach ($dados["linhas"] as $linha) {
        if ($pdf->espaco(20)) {
            $pdf->texto($margem, $pdf->y(), 9, "Detalhes (continuação)", true, $escuro);
            $pdf->descer(16);
            $desenharCabecalho();
        }
        $valores = [
            relatorio_pdf_cortar((string) ($linha["nome_evento"] ?: "Sem nome"), $colunas[0][1] - 6, 9),
            relatorio_pdf_cortar((string) ($linha["nome_cliente"] ?: "-"), $colunas[1][1] - 6, 9),
            relatorio_pdf_cortar(relatorio_mostrar_data($linha["data_festa"] ?? ""), $colunas[2][1] - 6, 9),
            (int) ($linha["numero_convidados"] ?? 0) > 0 ? (string) (int) $linha["numero_convidados"] : "-",
            relatorio_rotulo_status($linha["status"] ?? ""),
        ];
        $x = $margem;
        foreach ($valores as $indice => $valor) {
            $pdf->texto($x, $pdf->y(), 9, $valor, false, $escuro);
            $x += $colunas[$indice][1];
        }
        $pdf->descer(8);
        $pdf->retangulo($margem, $pdf->y(), 520, 0.4, [0.94, 0.92, 0.96]);
        $pdf->descer(12);
    }

    return $pdf->emitir();
}
