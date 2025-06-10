<?php
// Configurações de cabeçalho
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Habilitar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Criar arquivo de log
$log_file = 'verificacao_log.txt';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Requisição recebida\n", FILE_APPEND);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Método: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - POST: " . json_encode($_POST) . "\n", FILE_APPEND);

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método inválido']);
    exit;
}

// Obter o CPF da requisição
$cpf = isset($_POST['cpf']) ? $_POST['cpf'] : '';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - CPF recebido: " . $cpf . "\n", FILE_APPEND);

// Remover caracteres não numéricos
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - CPF limpo: " . $cpf_limpo . "\n", FILE_APPEND);

// Verificar se é o CPF específico (013.770.471-21)
if ($cpf_limpo === '01377047121') {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - CPF válido encontrado!\n", FILE_APPEND);

    // Retornar dados fixos da aluna
    $resposta = [
        'status' => 'success',
        'nome' => 'Laiz Alves Araújo',
        'cpf' => '013.770.471-21',
        'matricula' => '77783772',
        'curso' => 'Pós-Graduação em Neurociência do Comportamento',
        'email' => 'laiz.araujo@email.com',
        'status' => 'Término de Curso',
        'data_ingresso' => '15/11/2023',
        'data_conclusao' => '31/02/2025',
        'data_verificacao' => date('d/m/Y H:i:s'),
        'documento' => 'Declaração de Disciplinas e Ementas',
        'data_emissao' => '14/05/2025',
        'arquivo' => 'declaracao.pdf'
    ];

    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Resposta: " . json_encode($resposta) . "\n", FILE_APPEND);
    echo json_encode($resposta);
} else {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - CPF não corresponde ao CPF de teste\n", FILE_APPEND);

    // Retornar erro
    echo json_encode([
        'status' => 'error',
        'message' => 'Aluno não encontrado'
    ]);
}
?>
