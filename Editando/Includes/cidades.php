<?php

include_once __DIR__ . '/cidades-rj.php';

function cidades_cadastro() {
    $lista = [];
    foreach (cidades_do_rio() as $nome) {
        $lista[] = ['cidade' => $nome, 'uf' => 'RJ'];
    }

    $outras = [
        'AC' => ['Rio Branco'],
        'AL' => ['Maceió', 'Arapiraca'],
        'AP' => ['Macapá'],
        'AM' => ['Manaus'],
        'BA' => ['Salvador', 'Feira de Santana', 'Vitória da Conquista', 'Camaçari', 'Ilhéus', 'Itabuna'],
        'CE' => ['Fortaleza', 'Caucaia', 'Juazeiro do Norte'],
        'DF' => ['Brasília'],
        'ES' => ['Vitória', 'Vila Velha', 'Serra', 'Cariacica', 'Linhares'],
        'GO' => ['Goiânia', 'Aparecida de Goiânia', 'Anápolis'],
        'MA' => ['São Luís', 'Imperatriz'],
        'MT' => ['Cuiabá', 'Várzea Grande', 'Rondonópolis'],
        'MS' => ['Campo Grande', 'Dourados'],
        'MG' => ['Belo Horizonte', 'Uberlândia', 'Contagem', 'Juiz de Fora', 'Betim', 'Montes Claros', 'Uberaba', 'Governador Valadares', 'Ipatinga'],
        'PA' => ['Belém', 'Ananindeua', 'Santarém'],
        'PB' => ['João Pessoa', 'Campina Grande'],
        'PR' => ['Curitiba', 'Londrina', 'Maringá', 'Ponta Grossa', 'Cascavel', 'Foz do Iguaçu'],
        'PE' => ['Recife', 'Jaboatão dos Guararapes', 'Olinda', 'Caruaru', 'Petrolina'],
        'PI' => ['Teresina', 'Parnaíba'],
        'RN' => ['Natal', 'Mossoró'],
        'RS' => ['Porto Alegre', 'Caxias do Sul', 'Pelotas', 'Canoas', 'Santa Maria', 'Novo Hamburgo'],
        'RO' => ['Porto Velho'],
        'RR' => ['Boa Vista'],
        'SC' => ['Florianópolis', 'Joinville', 'Blumenau', 'Chapecó', 'Itajaí', 'Balneário Camboriú'],
        'SP' => ['São Paulo', 'Guarulhos', 'Campinas', 'São Bernardo do Campo', 'Santo André', 'Osasco', 'São José dos Campos', 'Ribeirão Preto', 'Sorocaba', 'Santos', 'Mauá', 'São José do Rio Preto', 'Mogi das Cruzes', 'Diadema', 'Jundiaí', 'Piracicaba', 'Bauru', 'São Vicente', 'Franca', 'Praia Grande', 'Guarujá', 'Taubaté', 'Limeira', 'Suzano', 'Barueri', 'Indaiatuba', 'Cotia', 'Americana', 'São Carlos', 'Itu', 'Atibaia', 'Bragança Paulista', 'Ubatuba', 'Caraguatatuba'],
        'SE' => ['Aracaju'],
        'TO' => ['Palmas'],
    ];

    foreach ($outras as $uf => $cidades) {
        foreach ($cidades as $nome) {
            $lista[] = ['cidade' => $nome, 'uf' => $uf];
        }
    }

    usort($lista, function ($a, $b) {
        $troca = ['á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c'];
        $na = strtr(mb_strtolower($a['cidade'], 'UTF-8'), $troca);
        $nb = strtr(mb_strtolower($b['cidade'], 'UTF-8'), $troca);
        if ($na === $nb) {
            return $a['uf'] <=> $b['uf'];
        }
        return $na <=> $nb;
    });

    return $lista;
}
