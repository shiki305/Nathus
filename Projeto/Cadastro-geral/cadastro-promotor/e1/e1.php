<?php session_start(); if(!isset($_SESSION["cadastro"])) { $_SESSION["cadastro"] = []; } ?>
<?php $titulo = "Cadastro"; $css = "css/e1.css"; $voltar = "../../geral.php"; include "../topo.php"; ?>
            <div class="conteudo-formulario">
                <h1>Insira suas informações</h1>
                <hr class="barra">

                <?php if(isset($_GET["erro"]) && $_GET["erro"] === "email"){ ?>
                    <p class="erro-cadastro">Este e-mail já está cadastrado. Faça login ou use outro e-mail.</p>
                <?php } elseif(($_GET["erro"] ?? "") === "contato"){ ?>
                    <p class="erro-cadastro">Confira o telefone com DDD e informe um CPF com 11 dígitos.</p>
                <?php } elseif(isset($_GET["erro"])){ ?>
                    <p class="erro-cadastro">Não foi possível concluir o cadastro. Tente novamente.</p>
                <?php } ?>

                <form action="../e2/e2.php" method="post">
                    <div class="campo campo-grande">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" value="<?php echo isset($_SESSION['cadastro']['nome']) ? htmlspecialchars($_SESSION['cadastro']['nome']) : ""; ?>" required>
                    </div>

                    <div class="campo campo-grande">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['cadastro']['email']) ? htmlspecialchars($_SESSION['cadastro']['email']) : ""; ?>" required>
                    </div>

                    <div class="campo campo-grande">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <div class="linha-formulario">
                        <div class="campo">
                            <label for="telefone">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" data-mascara="telefone" maxlength="19" autocomplete="tel" value="<?php echo isset($_SESSION['cadastro']['telefone']) ? htmlspecialchars($_SESSION['cadastro']['telefone']) : ""; ?>" required placeholder="(XX) XXXXX-XXXX">
                        </div>

                        <div class="campo">
                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" data-mascara="cpf" maxlength="14" value="<?php echo isset($_SESSION['cadastro']['cpf']) ? htmlspecialchars($_SESSION['cadastro']['cpf']) : ""; ?>" required inputmode="numeric" placeholder="XXX.XXX.XXX-XX">
                        </div>
                    </div>

                    <div class="titulo-extra">
                        <h2>Informações Adicionais</h2>
                        <hr class="barra2">
                    </div>

                    <div class="linha-formulario">
                        <div class="campo">
                            <label for="fantasia">Nome Fantasia</label>
                            <input type="text" id="fantasia" name="fantasia" value="<?php echo isset($_SESSION['cadastro']['fantasia']) ? htmlspecialchars($_SESSION['cadastro']['fantasia']) : ""; ?>">
                        </div>

                        <div class="campo">
                            <label for="cnpj">CNPJ</label>
                            <input type="text" id="cnpj" name="cnpj" data-mascara="cnpj" maxlength="18" value="<?php echo isset($_SESSION['cadastro']['cnpj']) ? htmlspecialchars($_SESSION['cadastro']['cnpj']) : ""; ?>" autocapitalize="characters" placeholder="XX.XXX.XXX/XXXX-XX">
                        </div>
                    </div>

                    <button type="submit">Continuar</button>
                </form>
            </div>
<?php require_once __DIR__ . '/../../../Includes/mascaras.php'; ?>
<?php include "../fim.php"; ?>
