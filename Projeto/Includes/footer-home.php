<?php
if (!isset($base_url)) { $base_url = '../'; }
if (session_status() === PHP_SESSION_NONE) { @session_start(); }

$rodape_logado = isset($_SESSION['usuario_id']);
$rodape_tipo = strtolower((string) ($_SESSION['usuario_tipo'] ?? ''));

$rodape_home = $base_url . 'home/homeAnt.php';
$rodape_login = $base_url . 'Login/login.php';
$rodape_cadastro = $base_url . 'Cadastro-geral/geral.php';
$rodape_landing = $base_url . 'Landing%20Page/landingpage.php';

$rodape_dashboard = null;
if ($rodape_logado && $rodape_tipo === 'promotor') {
    $rodape_dashboard = $base_url . 'Dashboard/dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"
      href="../Includes/css/footer-home.css?v=<?php echo filemtime(__DIR__ . '/../Includes/css/footer-home.css'); ?>">
</head>
<body>
    
</body>
</html>
<footer class="rodape">
    <div class="rodape-caixa">
        <div class="rodape-logo">
            <a href="<?php echo $rodape_home; ?>"><img src="<?php echo $base_url; ?>assets/LLOgo.png" alt="Nathus"></a>
        </div>
        <div class="rodape-links">
            <div class="rodape-coluna">
                <h4>Análise</h4>
                <ul>
                    <li><a href="<?php echo $rodape_home; ?>">Home</a></li>
                    <?php if ($rodape_dashboard !== null): ?>
                        <li><a href="<?php echo $rodape_dashboard; ?>">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo $rodape_landing; ?>#sobre">Sobre</a></li>
                    <li><a href="<?php echo $rodape_landing; ?>#servicos">Serviços</a></li>
                </ul>
            </div>
            <div class="rodape-coluna">
                <h4>Acessibilidade</h4>
                <ul>
                    <li><a href="<?php echo $rodape_login; ?>">Login</a></li>
                    <li><a href="<?php echo $rodape_cadastro; ?>">Cadastro</a></li>
                    <li><a href="<?php echo $rodape_landing; ?>#funcionalidades">Funcionalidades</a></li>
                    <li><a href="<?php echo $rodape_landing; ?>#planos-lp">Assinaturas</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="rodape-base">
        <p>Copyright © 2026 Todos os direitos reservados.</p>
        <div class="rodape-social">
            <a href="#" aria-label="Facebook"><img src="<?php echo $base_url; ?>assets/Icones/facebook.png" alt="Facebook"></a>
            <a href="#" aria-label="X"><img src="<?php echo $base_url; ?>assets/Icones/x.png" alt="X"></a>
            <a href="#" aria-label="YouTube"><img src="<?php echo $base_url; ?>assets/Icones/youtube.png" alt="YouTube"></a>
            <a href="#" aria-label="Instagram"><img src="<?php echo $base_url; ?>assets/Icones/instagram.png" alt="Instagram"></a>
            <a href="#" aria-label="TikTok"><img src="<?php echo $base_url; ?>assets/Icones/tiktok.png" alt="TikTok"></a>
        </div>
    </div>
</footer>
