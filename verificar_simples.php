<?php
// Configurações de segurança
header('Content-Type: application/json; charset=utf-8');

// Habilitar log de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Garantir que o diretório de logs existe
if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
}

// Função para registrar logs
function logVerificacao($message) {
    $logFile = 'logs/verificacao_simples.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Log de início
logVerificacao("Iniciando verificação simples");
logVerificacao("Método: " . $_SERVER["REQUEST_METHOD"]);
logVerificacao("Dados recebidos: " . json_encode($_POST));

// Obter o CPF da requisição
$cpf = isset($_POST['cpf']) ? preg_replace('/[^0-9]/', '', $_POST['cpf']) : '';
logVerificacao("CPF após limpeza: " . $cpf);

// Verificar se é o CPF específico
if ($cpf === '10249351404') {
    logVerificacao("CPF de teste encontrado: 102.493.514-04 (Laiz Alves Araújo)");
    
    // Retornar dados fixos da aluna
    echo json_encode([
        'status' => 'success',
        'nome' => 'Laiz Alves Araújo',
        'cpf' => '102.493.514-04',
        'matricula' => 'FA2025001',
        'curso' => 'Tecnólogo em Gestão de Recursos Humanos',
        'email' => 'laiz.araujo@email.com',
        'status' => 'Ativo',
        'data_ingresso' => '10/02/2023',
        'data_verificacao' => date('d/m/Y H:i:s'),
        'documento' => 'Declaração de Disciplinas e Ementas',
        'data_emissao' => '14/05/2025',
        'arquivo' => 'declaracao.pdf'
    ]);
} else {
    logVerificacao("CPF não corresponde ao CPF de teste");
    
    // Retornar erro
    echo json_encode([
        'status' => 'error',
        'message' => 'Aluno não encontrado'
    ]);
}
?>
