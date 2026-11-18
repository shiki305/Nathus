<?php

function apagar_upload_antigo($pasta, $nome_arquivo){
    if(empty($nome_arquivo)){
        return false;
    }
    if(strpos($nome_arquivo, "/") !== false || strpos($nome_arquivo, "\\") !== false || strpos($nome_arquivo, "..") !== false){
        return false;
    }
    $caminho = __DIR__ . "/../uploads/" . $pasta . "/" . $nome_arquivo;
    if(is_file($caminho)){
        return unlink($caminho);
    }
    return false;
}

/**
 * Remove registros cujos arquivos desapareceram da pasta uploads.
 *
 * - Galeria: DELETE da linha inteira (a foto é o registro).
 * - Pacote:  imagem = NULL (o pacote continua, só perde a imagem).
 * - Promotor: banner / foto_perfil = NULL (a página cai no fallback).
 *
 * Chamar no topo das páginas que listam imagens, ANTES de montar o HTML.
 */
function limpar_uploads_orfaos($conexao, $id_promotor){
    $pastas = ["galeria" => "promotor_galeria", "pacotes" => "promotor_pacote"];

    foreach($pastas as $pasta => $tabela){
        if($tabela === "promotor_galeria"){
            $stmt = $conexao->prepare("SELECT id, arquivo FROM promotor_galeria WHERE id_promotor = ?");
        } else {
            $stmt = $conexao->prepare("SELECT id, imagem AS arquivo FROM promotor_pacote WHERE id_promotor = ? AND imagem IS NOT NULL AND imagem <> ''");
        }
        $stmt->bind_param("i", $id_promotor);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $sem_arquivo = [];
        $tem_arquivo = [];
        while($linha = $resultado->fetch_assoc()){
            $nome = basename($linha["arquivo"]); // barra e ".." nunca passam
            if(is_file(__DIR__ . "/../uploads/" . $pasta . "/" . $nome)){
                $tem_arquivo[] = (int)$linha["id"];
            } else {
                $sem_arquivo[] = (int)$linha["id"];
            }
        }

        // Apaga em lote (uma query só) os registros órfãos.
        if(!empty($sem_arquivo)){
            $placeholders = implode(",", array_fill(0, count($sem_arquivo), "?"));
            $tipos = str_repeat("i", count($sem_arquivo));

            if($tabela === "promotor_galeria"){
                $stmtDel = $conexao->prepare("DELETE FROM promotor_galeria WHERE id IN ($placeholders)");
            } else {
                $stmtDel = $conexao->prepare("UPDATE promotor_pacote SET imagem = NULL WHERE id IN ($placeholders)");
            }
            $stmtDel->bind_param($tipos, ...$sem_arquivo);
            $stmtDel->execute();
        }
    }

    // Banner e foto de perfil órfãos: anula a coluna (a página usa o fallback).
    $stmtFoto = $conexao->prepare("SELECT banner, foto_perfil FROM promotor WHERE id = ? LIMIT 1");
    $stmtFoto->bind_param("i", $id_promotor);
    $stmtFoto->execute();
    $promotor = $stmtFoto->get_result()->fetch_assoc();

    if($promotor){
        $banner_orfao = !empty($promotor["banner"]) && !is_file(__DIR__ . "/../uploads/banner/" . basename($promotor["banner"]));
        $foto_orfa    = !empty($promotor["foto_perfil"]) && !is_file(__DIR__ . "/../uploads/perfil-promotor/" . basename($promotor["foto_perfil"]));

        if($banner_orfao || $foto_orfa){
            $stmtUp = $conexao->prepare("UPDATE promotor SET banner = ?, foto_perfil = ? WHERE id = ?");
            $banner_novo = $banner_orfao ? null : $promotor["banner"];
            $foto_nova   = $foto_orfa    ? null : $promotor["foto_perfil"];
            $stmtUp->bind_param("ssi", $banner_novo, $foto_nova, $id_promotor);
            $stmtUp->execute();

            if($foto_orfa){
                limpar_cache_foto_perfil();
            }
        }
    }
}

function otimizar_e_salvar_imagem($arquivo, $pasta_destino, $prefixo, $largura_max, $altura_max, $qualidade = 80){
    if(!isset($arquivo["tmp_name"]) || $arquivo["error"] != 0 || !is_uploaded_file($arquivo["tmp_name"])){
        return false;
    }

    $info = @getimagesize($arquivo["tmp_name"]);
    if(!$info){
        return false;
    }

    $mime = $info["mime"];
    $mimes_permitidos = ["image/jpeg", "image/png", "image/webp"];
    if(!in_array($mime, $mimes_permitidos, true)){
        return false;
    }

    if(!function_exists("imagecreatetruecolor")){
        $extensao = image_type_to_extension($info[2], false);
        $nome_original = $prefixo . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . strtolower($extensao === "jpeg" ? "jpg" : $extensao);
        if(!is_dir($pasta_destino)){
            mkdir($pasta_destino, 0775, true);
        }
        $caminho_original = rtrim($pasta_destino, "/\\") . "/" . $nome_original;
        return move_uploaded_file($arquivo["tmp_name"], $caminho_original) ? $nome_original : false;
    }

    switch($mime){
        case "image/jpeg":  $origem = @imagecreatefromjpeg($arquivo["tmp_name"]); break;
        case "image/png":   $origem = @imagecreatefrompng($arquivo["tmp_name"]);  break;
        case "image/webp":  $origem = @imagecreatefromwebp($arquivo["tmp_name"]); break;
        default:            $origem = false;
    }

    if(!$origem){
        return false;
    }

    if($mime === "image/jpeg" && function_exists("exif_read_data")){
        $exif = @exif_read_data($arquivo["tmp_name"]);
        if(!empty($exif["Orientation"])){
            switch($exif["Orientation"]){
                case 3: $origem = imagerotate($origem, 180, 0); break;
                case 6: $origem = imagerotate($origem, -90, 0); break;
                case 8: $origem = imagerotate($origem, 90, 0);  break;
            }
        }
    }

    $largura_original  = imagesx($origem);
    $altura_original = imagesy($origem);
    $escala = min($largura_max / $largura_original, $altura_max / $altura_original, 1);

    $nova_largura  = (int)round($largura_original * $escala);
    $nova_altura = (int)round($altura_original * $escala);

    $canvas = imagecreatetruecolor($nova_largura, $nova_altura);

    if($mime === "image/png"){
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparente = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $nova_largura, $nova_altura, $transparente);
    }
    else{
        $branco = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $nova_largura, $nova_altura, $branco);
    }

    imagecopyresampled($canvas, $origem, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);
    imagedestroy($origem);

    if(!is_dir($pasta_destino)){
        mkdir($pasta_destino, 0775, true);
    }

    $nome_arquivo = $prefixo . "_" . time() . "_" . bin2hex(random_bytes(4)) . ".webp";
    $caminho_final = rtrim($pasta_destino, "/\\") . "/" . $nome_arquivo;

    $salvou = imagewebp($canvas, $caminho_final, $qualidade);
    imagedestroy($canvas);

    return $salvou ? $nome_arquivo : false;
}
