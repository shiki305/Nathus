<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nathus</title>
    <link rel="stylesheet" href="../Includes/css/global.css?v=<?php echo filemtime(__DIR__ . "/../Includes/css/global.css"); ?>">
    <link rel="stylesheet" href="css/landingpage.css?v=<?php echo filemtime(__DIR__ . "/css/landingpage.css"); ?>">
</head>

<body>
    <section class="hero">
        <div class="hero-texto">
            <div class="logo"><img class="logo" src="../assets/LLOgo.png" alt=""></div>

            <h2>A Solução definitiva para o Mercado de Festas</h2>
            <p>Oferecendo a todo organizador a chance de criar os melhores eventos e abrir as portas
                para experiências incríveis.
                Comece hoje sua jornada com a Nathus para se destacar em nossa comunidade vibrante de organizadores.</p>

         <div class="hero-botao">
    <a href="../Cadastro-geral/geral.php" class="hero1-botao">CADASTRAR</a>
    <a href="../Login/login.php" class="hero2-botao">LOGIN</a>
</div>
        </div>
    </section>

    <section class="secao01" id="sobre">
        <figure class="imagem01">
            <img class="imagem01" src="../assets/sec01-img.png" alt="">
        </figure>

        <div class="secao01-texto">
            <h2>A Nathus Veio Para <strong>Revolucionar</strong> o Setor de Festas!</h2>
            <ul class="secao01-lista">
                <li><img src="../assets/Checkbox.svg" alt="">Intermediação segura</li>
                <li><img src="../assets/Checkbox.svg" alt="">Redução de burocracia</li>
                <li><img src="../assets/Checkbox.svg" alt="">Facilidade na negociação</li>
            </ul>

            <section class="secao01-botao"><a href="../home/organizadores.php">Explorar</a></section>
        </div>
    </section>

    <section class="secao02" id="servicos">
        <div class="secao02-texto">
            <h2>Nos concentramos em 3 áreas principais:</h2>
            <p>
                Atuamos em 3 tipos de áreas: <strong>Organização</strong>, <strong>Local</strong> e
                <strong>Buffet</strong>,
                promotores completos que integram todas essas funções para entregar o evento como um todo.
            </p>

            <div class="secao02-cartao">
                <div class="secao-cartao">
                    <div class="card-topo">
                        <figure class="card-icone">
                            <img src="../assets/organizacao.png" alt="">
                        </figure>
                        <h3>Organização</h3>
                    </div>
                    <p>Planeje seu evento do início ao fim com profissionais experientes</p>
                </div>

                <div class="secao-cartao">
                    <div class="card-topo">
                        <figure class="card-icone">
                            <img src="../assets/localizacao.png" alt="">
                        </figure>
                        <h3>Local</h3>
                    </div>
                    <p>Encontre o espaço ideal para sua festa</p>
                </div>

                <div class="secao-cartao">
                    <div class="card-topo">
                        <figure class="card-icone">
                            <img src="../assets/buffet.png" alt="">
                        </figure>
                        <h3>Buffet</h3>
                    </div>
                    <p>Escolha o cardápio perfeito para encantar seus convidados</p>
                </div>
            </div>

            <div class="secao02-botao">
    <a href="../home/organizadores.php">Explorar Áreas</a>
</div>
        </div>
    </section>

    <section class="secao03" id="funcionalidades">
        <div class="secao03-texto">
            <h2>Como Funciona?</h2>
            <p>Três passos simples separam você do evento dos sonhos.</p>
        </div>

        <div class="secao03-passos">
            <div class="passo">
                <div class="circulo">1</div>
                <h3>Economia de Tempo</h3>
                <p>Economia de tempo e dinheiro com processos otimizados.</p>
            </div>

            <div class="linha"></div>

            <div class="passo">
                <div class="circulo">2</div>
                <h3>Economia de Tempo</h3>
                <p>Economia de tempo e dinheiro com processos otimizados.</p>
            </div>

            <div class="linha"></div>

            <div class="passo">
                <div class="circulo">3</div>
                <h3>Economia de Tempo</h3>
                <p>Economia de tempo e dinheiro com processos otimizados.</p>
            </div>
        </div>

        <div class="secao03-botao">
    <a href="../Cadastro-geral/geral.php">Começar Agora</a>
</div>
    </section>

    <section class="secao04">
        <div class="secao04-texto">
            <h2>O que tem na sua festa com a Nathus?</h2>
            <p>Festas planejadas por especialistas, com atendimento personalizado durante todo o processo.</p>

            <div class="secao04-botao">
    <a href="../home/organizadores.php">Ver Serviços</a>
</div>
        </div>

        <div class="festa-painel">
            <img class="festa-foto" src="../assets/home/bp.png" alt="Salão de festa decorado">
            <ul class="festa-lista">
                <li><img src="../assets/Checkbox.svg" alt="">Decoração sob medida</li>
                <li><img src="../assets/Checkbox.svg" alt="">Buffet para os convidados</li>
                <li><img src="../assets/Checkbox.svg" alt="">Espaço reservado</li>
            </ul>
        </div>
    </section>

    <section id="secao-5">
        <h1 class="secao5-titulo">Por que escolher a NATHUS?</h1>
        <p class="secao5-subtitulo">
            Atuamos em 3 tipos de áreas: <strong>Organização</strong>, <strong>Local</strong> e <strong>Buffet</strong>,
            promotores completos que integram todas essas funções para entregar o evento como um todo.
        </p>

        <div class="cartoes-caixa">
            <div class="card-item">
                <img src="../assets/economia.png" alt="Economia de tempo" class="card-imagem">
                <h3>Economia de Tempo</h3>
                <p>Economia de tempo e dinheiro com processos otimizados.</p>
            </div>

            <div class="card-item">
                <img src="../assets/transparencia.png" alt="Transparência" class="card-imagem">
                <h3>Transparência</h3>
                <p>Segurança e transparência em todas as negociações.</p>
            </div>

            <div class="card-item">
                <img src="../assets/personalizacao.png" alt="Personalização" class="card-imagem">
                <h3>Personalização</h3>
                <p>Adapte tudo às necessidades do seu negócio.</p>
            </div>
        </div>

<div class="btn-secao5"><a href="#planos-lp">Ver Planos</a></div>    </section>

    <section class="planos-lp" id="planos-lp">
        <h1>Confira o plano ideal para o seu negócio!</h1>

        <div class="planos-lp-conteudo">
            <div class="planos-lp-cartao">
                <div class="plano-cabecalho plano-cabecalho-mensal">
                    <h2 class="planos-lp-titulo">Plano Mensal</h2>
                </div>
                <p class="planos-lp-preco preco-mensal"><strong>R$29,90</strong>/mês</p>
                <hr class="plano-divider">
                <ul class="planos-lp-lista">
                    <li><span class="plano-check"></span><span>Acesso ilimitado à plataforma</span></li>
                    <li><span class="plano-check"></span><span>Personalização básica do perfil</span></li>
                    <li><span class="plano-check"></span><span>Cancele quando quiser</span></li>
                </ul>
<div class="plano-botao btn-mensal"><a href="../Login/login.php">Quero este plano →</a></div>
         </div>

            <div class="planos-lp-cartao">
                <div class="plano-cabecalho plano-cabecalho-anual">
                    <h2 class="planos-lp-titulo">Plano Anual</h2>
                </div>
                <p class="planos-lp-preco preco-anual"><strong>R$320,90</strong>/ano</p>
                <hr class="plano-divider">
                <ul class="planos-lp-lista">
                    <li><span class="plano-check plano-check-ouro"></span><span>Acesso completo a plataforma por 12 meses</span></li>
                    <li><span class="plano-check plano-check-ouro"></span><span>Mesmas funcionalidades que o plano mensal</span></li>
                    <li><span class="plano-check plano-check-ouro"></span><span>Suporte especializado via chat e celular</span></li>
                </ul>
                <div class="plano-botao btn-mensal"><a href="../Login/login.php">Quero este plano →</a></div>
            </div>
        </div>
    </section>

    <section class="secao07">
        <div class="secao07-caixa">
            <h2>O que os clientes dizem sobre a Nathus?</h2>
            <p class="secao07-sub">
                Atuamos em 3 tipos de áreas: <strong>Organização</strong>, <strong>Local</strong> e
                <strong>Buffet</strong>,
                promotores completos que integram todas essas funções para entregar o evento como um todo.
            </p>

            <div class="secao07-cartoes">
                <div class="secao07-cartao">
                    <img src="../assets/jorge.png" alt="Jorge Antônio" class="avatar">
                    <h3>Jorge Antônio</h3>
                    <p>Valeu, Nathus! Minha festa de formatura foi épica! Organização top, ambiente incrível e muita emoção.</p>
                </div>

                <div class="secao07-cartao">
                    <img src="../assets/miguel.png" alt="Miguel Santaroni" class="avatar">
                    <h3>Miguel Santaroni</h3>
                    <p>Agradecemos à Nathus pela excelente organização da festa da empresa! Profissionalismo e diversão na medida certa.</p>
                </div>

                <div class="secao07-cartao">
                    <img src="../assets/casimiro.png" alt="Casimiro" class="avatar">
                    <h3>Casimiro</h3>
                    <p>Nathus, vocês fizeram nosso noivado ser ainda mais especial. Cada detalhe mostrou cuidado e carinho.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="secao08">
        <div class="secao08-texto">
            <p class="secao08-selo">Sua festa começa aqui</p>
            <h2>Pronto para Começar?</h2>
            <p>Cadastre-se agora e encontre o promotor perfeito para sua festa!</p>

            <div class="secao08-botao">
    <button type="button" class="abrir-newsletter">Saiba mais</button>
</div>
        </div>

        <figure class="imagem08">
            <img src="../assets/pronto pra comecar.png" alt="Organizadora cumprimentando um cliente em um evento">
        </figure>
    </section>

    <?php include_once __DIR__ . '/../Includes/footer-home.php'; ?>

    <div class="newsletter-overlay" id="newsletterOverlay" hidden>
        <div class="newsletter-caixa" role="dialog" aria-modal="true" aria-labelledby="newsletterTitulo">
            <button type="button" class="newsletter-fechar" id="fecharNewsletter" aria-label="Fechar">×</button>
            <p class="newsletter-selo">Newsletter</p>
            <h2 id="newsletterTitulo">Saiba mais</h2>
            <p class="newsletter-texto">Deixe seu e-mail e receba novidades da Nathus: dicas para festas e avisos da plataforma.</p>

            <form class="newsletter-form" id="formNewsletter" novalidate>
                <label class="newsletter-rotulo" for="newsletterEmail">E-mail</label>
                <input type="email" id="newsletterEmail" name="email" placeholder="Digite seu e-mail" required autocomplete="email">
                <p class="newsletter-aviso" id="newsletterAviso" hidden></p>
                <button type="submit" class="newsletter-enviar">Quero receber</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var overlay = document.getElementById("newsletterOverlay");
            var abrires = document.querySelectorAll(".abrir-newsletter");
            var fechar = document.getElementById("fecharNewsletter");
            var form = document.getElementById("formNewsletter");
            var aviso = document.getElementById("newsletterAviso");
            var campo = document.getElementById("newsletterEmail");
            if (!overlay || !abrires.length || !form) return;

            function abrirPopup() {
                aviso.hidden = true;
                aviso.textContent = "";
                form.hidden = false;
                overlay.hidden = false;
                campo.focus();
            }

            function fecharPopup() {
                overlay.hidden = true;
            }

            abrires.forEach(function (botao) {
                botao.addEventListener("click", abrirPopup);
            });
            fechar.addEventListener("click", fecharPopup);
            overlay.addEventListener("click", function (evento) {
                if (evento.target === overlay) fecharPopup();
            });
            document.addEventListener("keydown", function (evento) {
                if (evento.key === "Escape" && !overlay.hidden) fecharPopup();
            });

            form.addEventListener("submit", function (evento) {
                evento.preventDefault();
                aviso.hidden = true;
                if (!campo.value.trim() || !campo.checkValidity()) {
                    aviso.textContent = "Informe um e-mail válido.";
                    aviso.className = "newsletter-aviso erro";
                    aviso.hidden = false;
                    campo.focus();
                    return;
                }

                var botao = form.querySelector(".newsletter-enviar");
                botao.disabled = true;

                fetch("newsletter.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "email=" + encodeURIComponent(campo.value.trim())
                })
                    .then(function (resposta) { return resposta.json(); })
                    .then(function (dados) {
                        aviso.textContent = dados.mensagem || "Não foi possível salvar agora.";
                        aviso.className = "newsletter-aviso" + (dados.ok ? " ok" : " erro");
                        aviso.hidden = false;
                        if (dados.ok) campo.value = "";
                    })
                    .catch(function () {
                        aviso.textContent = "Não foi possível salvar agora. Tente de novo.";
                        aviso.className = "newsletter-aviso erro";
                        aviso.hidden = false;
                    })
                    .finally(function () {
                        botao.disabled = false;
                    });
            });
        })();
    </script>
</body>

</html>