<?php
// Função para escapar texto e evitar problemas com HTML/injeção.
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Converte valores digitados em número decimal aceitando vírgula ou ponto.
function limpar_numero($valor): float {
    if ($valor === null || $valor === '') {
        return 0.0;
    }
    return (float) str_replace(',', '.', (string) $valor);
}

// Classifica o aluno com base na média final.
function situacao(float $media): string {
    if ($media >= 7.0) {
        return 'Aprovado';
    }
    if ($media >= 5.0) {
        return 'Recuperação';
    }
    return 'Reprovado';
}

// Gera uma mensagem automática de desempenho da turma.
function mensagem_desempenho(float $percentual): string {
    if ($percentual >= 80) {
        return 'Excelente desempenho geral da turma.';
    }
    if ($percentual >= 60) {
        return 'Desempenho satisfatório da turma.';
    }
    if ($percentual >= 40) {
        return 'Desempenho regular, com atenção necessária.';
    }
    return 'Desempenho abaixo do esperado. É importante reforçar os estudos.';
}

// Define em que etapa do formulário a aplicação está.
$etapa = $_POST['etapa'] ?? 'inicio';
$erro = '';
$turma = '';
$qtdAlunos = 0;

// Valida os dados da turma antes de avançar para a etapa de cadastro dos alunos.
if ($etapa === 'preencher' || $etapa === 'processar') {
    $turma = trim((string)($_POST['turma'] ?? ''));
    $qtdAlunos = (int)($_POST['qtd_alunos'] ?? 0);

    if ($turma === '' || $qtdAlunos <= 0) {
        $erro = 'Preencha o nome da turma e informe uma quantidade válida de alunos.';
        $etapa = 'inicio';
    }
}

// Vetor que guardará os dados individuais de cada aluno.
$alunos = [];

// Vetor com os resultados gerais da turma.
$resumo = [
    'media_turma' => 0,
    'maior_media' => null,
    'menor_media' => null,
    'aprovados' => 0,
    'recuperacao' => 0,
    'reprovados' => 0,
    'percentual_aprovacao' => 0,
    'soma_total_notas' => 0,
];

// Processa os dados somente quando o formulário final é enviado.
if ($etapa === 'processar') {
    $nomes = $_POST['nome'] ?? [];
    $p1 = $_POST['prova1'] ?? [];
    $p2 = $_POST['prova2'] ?? [];
    $trab = $_POST['trabalho'] ?? [];

    // Soma das médias para calcular a média geral da turma.
    $somaMedias = 0;

for ($i = 0; $i < $qtdAlunos; $i++) {
    $nome = trim((string)($nomes[$i] ?? ''));
    $nota1 = limpar_numero($p1[$i] ?? 0);
    $nota2 = limpar_numero($p2[$i] ?? 0);
    $nota3 = limpar_numero($trab[$i] ?? 0);

    if ($nota1 > 10 || $nota2 > 10 || $nota3 > 10) {
        echo "As notas não podem ser maiores que 10.";
        exit;
    }

    $soma = $nota1 + $nota2 + $nota3;
    $media = $soma / 3;
    $raiz = sqrt($soma);
    $maior = max($nota1, $nota2, $nota3);
    $menor = min($nota1, $nota2, $nota3);
    $dif = abs($maior - $menor);
    $sit = situacao($media);

    $alunos[] = [
        'nome' => $nome !== '' ? $nome : 'Aluno ' . ($i + 1),
        'nota1' => $nota1,
        'nota2' => $nota2,
        'nota3' => $nota3,
        'media' => $media,
        'raiz' => $raiz,
        'dif' => $dif,
        'situacao' => $sit,
    ];

    $somaMedias += $media;
    $resumo['soma_total_notas'] += $soma;


        // Atualiza maior e menor média encontradas.
        if ($resumo['maior_media'] === null || $media > $resumo['maior_media']) {
            $resumo['maior_media'] = $media;
        }
        if ($resumo['menor_media'] === null || $media < $resumo['menor_media']) {
            $resumo['menor_media'] = $media;
        }

        // Conta quantos alunos estão em cada situação.
        if ($sit === 'Aprovado') {
            $resumo['aprovados']++;
        } elseif ($sit === 'Recuperação') {
            $resumo['recuperacao']++;
        } else {
            $resumo['reprovados']++;
        }
    }

    // Média geral da turma.
    $resumo['media_turma'] = $qtdAlunos > 0 ? $somaMedias / $qtdAlunos : 0;

    // Percentual de aprovação, conforme a regra do enunciado.
    $resumo['percentual_aprovacao'] = $qtdAlunos > 0 ? ($resumo['aprovados'] / $qtdAlunos) * 100 : 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Web de Análise Estatística de Turma Escolar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page">
        <header class="hero">
            <div class="hero-badge">Boletim da Turma</div>
            <h1>Sistema Web de Análise Estatística de Turma Escolar</h1>
            <p>Cadastro, processamento e relatório estatístico de uma turma.</p>
        </header>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo h($erro); ?></div>
        <?php endif; ?>

        <?php if ($etapa === 'inicio'): ?>
            <section class="card">
                <h2>Entrada de dados</h2>
                <p class="section-text">Primeiro informe o nome da turma e a quantidade de alunos que serão cadastrados.</p>

                <form method="post" class="form-grid">
                    <input type="hidden" name="etapa" value="preencher">

                    <label>
                        Nome da turma
                        <input type="text" name="turma" required placeholder="Ex.: 3º A">
                    </label>

                    <label>
                        Quantidade de alunos
                        <input type="number" name="qtd_alunos" min="1" max="100" required placeholder="Ex.: 5">
                    </label>

                    <button type="submit">Continuar</button>
                </form>
            </section>

        <?php elseif ($etapa === 'preencher'): ?>
            <section class="card">
                <h2>Dados da turma</h2>
                <div class="info-line">
                    <p><strong>Turma:</strong> <?php echo h($turma); ?></p>
                    <p><strong>Quantidade de alunos:</strong> <?php echo (int)$qtdAlunos; ?></p>
                </div>
            </section>

            <section class="card">
                <h2>Cadastro dos alunos</h2>
                <p class="section-text">Preencha o nome e as três notas de cada aluno. Depois clique em processar para gerar o relatório.</p>

                <form method="post" class="stacked-form">
                    <input type="hidden" name="etapa" value="processar">
                    <input type="hidden" name="turma" value="<?php echo h($turma); ?>">
                    <input type="hidden" name="qtd_alunos" value="<?php echo (int)$qtdAlunos; ?>">

                    <?php for ($i = 0; $i < $qtdAlunos; $i++): ?>
                        <fieldset class="student-box">
                            <legend>Aluno <?php echo $i + 1; ?></legend>

                            <div class="form-grid four-columns">
                                <label>
                                    Nome
                                    <input type="text" name="nome[]" required placeholder="Nome do aluno">
                                </label>

                                <label>
                                    Nota da Prova 1
                                    <input type="number" name="prova1[]" step="0.01" min="0" max="10" required placeholder="0 a 10">
                                </label>

                                <label>
                                    Nota da Prova 2
                                    <input type="number" name="prova2[]" step="0.01" min="0" max="10" required placeholder="0 a 10">
                                </label>

                                <label>
                                    Nota de Trabalho
                                    <input type="number" name="trabalho[]" step="0.01" min="0" max="10" required placeholder="0 a 10">
                                </label>
                            </div>
                        </fieldset>
                    <?php endfor; ?>

                    <button type="submit">Processar dados</button>
                </form>
            </section>

        <?php else: ?>
            <section class="card">
                <h2>Relatório final da turma</h2>
                <p><strong>Turma:</strong> <?php echo h($turma); ?></p>
                <p class="highlight-message"><?php echo h(mensagem_desempenho((float)$resumo['percentual_aprovacao'])); ?></p>
            </section>

            <section class="summary-grid">
                <article class="summary-card">
                    <span>Média geral</span>
                    <strong><?php echo number_format((float)$resumo['media_turma'], 2, ',', '.'); ?></strong>
                </article>

                <article class="summary-card">
                    <span>Maior média</span>
                    <strong><?php echo number_format((float)$resumo['maior_media'], 2, ',', '.'); ?></strong>
                </article>

                <article class="summary-card">
                    <span>Menor média</span>
                    <strong><?php echo number_format((float)$resumo['menor_media'], 2, ',', '.'); ?></strong>
                </article>

                <article class="summary-card">
                    <span>Aprovados</span>
                    <strong><?php echo (int)$resumo['aprovados']; ?></strong>
                </article>

                <article class="summary-card">
                    <span>Recuperação</span>
                    <strong><?php echo (int)$resumo['recuperacao']; ?></strong>
                </article>

                <article class="summary-card">
                    <span>Reprovados</span>
                    <strong><?php echo (int)$resumo['reprovados']; ?></strong>
                </article>

                <article class="summary-card">
                    <span>Percentual de aprovação</span>
                    <strong><?php echo number_format((float)$resumo['percentual_aprovacao'], 2, ',', '.'); ?>%</strong>
                </article>

                <article class="summary-card">
                    <span>Soma total das notas</span>
                    <strong><?php echo number_format((float)$resumo['soma_total_notas'], 2, ',', '.'); ?></strong>
                </article>
            </section>

            <section class="card">
                <h2>Tabela completa dos alunos</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Prova 1</th>
                                <th>Prova 2</th>
                                <th>Trabalho</th>
                                <th>Média</th>
                                <th>Raiz da soma</th>
                                <th>Diferença abs.</th>
                                <th>Situação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunos as $a): ?>
                                <tr>
                                    <td><?php echo h($a['nome']); ?></td>
                                    <td><?php echo number_format($a['nota1'], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($a['nota2'], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($a['nota3'], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($a['media'], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($a['raiz'], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($a['dif'], 2, ',', '.'); ?></td>
                                    <td><?php echo h($a['situacao']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card">
                <h2>Resumo interpretativo</h2>
                <p><strong>Média da turma:</strong> <?php echo number_format((float)$resumo['media_turma'], 2, ',', '.'); ?></p>
                <p><strong>Maior média encontrada:</strong> <?php echo number_format((float)$resumo['maior_media'], 2, ',', '.'); ?></p>
                <p><strong>Menor média encontrada:</strong> <?php echo number_format((float)$resumo['menor_media'], 2, ',', '.'); ?></p>
                <p><strong>Percentual de aprovação:</strong> <?php echo number_format((float)$resumo['percentual_aprovacao'], 2, ',', '.'); ?>%</p>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>
