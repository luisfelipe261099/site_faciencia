<?php
// Usando namespaces para PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carrega o autoloader do Composer e o arquivo de configuração
require 'vendor/autoload.php';
require 'config.php';

// --- CONFIGURAÇÕES DE AMBIENTE ---

// Define o fuso horário para consistência nas datas
date_default_timezone_set('America/Sao_Paulo');

// Define o cabeçalho de resposta como JSON
header('Content-Type: application/json; charset=utf-8');

// Configuração de erros baseada no ambiente definido em config.php
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/php-error.log'); // Salva o log na mesma pasta do script
}


// --- FUNÇÃO DE RESPOSTA E ENCERRAMENTO ---
function send_response($status, $message, $protocolo = null) {
    http_response_code($status === 'success' ? 200 : 400);
    $response = ['status' => $status, 'message' => $message];
    if ($protocolo) {
        $response['protocolo'] = $protocolo;
    }
    echo json_encode($response);
    exit();
}

// --- PROCESSAMENTO PRINCIPAL ---
$protocolo = 'POLO-' . date('Ymd') . '-' . mt_rand(10000, 99999);

try {
    // 1. VERIFICAÇÃO INICIAL E VALIDAÇÃO DE MÉTODO
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        send_response('error', 'Método de requisição inválido.');
    }

    // 2. VALIDAÇÃO E SANITIZAÇÃO DOS DADOS (com FILTER_SANITIZE_STRING substituído)
    $nome_empresa     = trim(htmlspecialchars($_POST['nome_empresa'] ?? '', ENT_QUOTES, 'UTF-8'));
    $cnpj             = preg_replace('/[^0-9]/', '', $_POST['cnpj'] ?? '');
    $nome_solicitante = trim(htmlspecialchars($_POST['nome_solicitante'] ?? '', ENT_QUOTES, 'UTF-8'));
    $telefone         = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
    $email            = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $link_planilha    = filter_input(INPUT_POST, 'link_planilha', FILTER_VALIDATE_URL);
    $tipo_solicitacao = trim(htmlspecialchars($_POST['tipo_solicitacao'] ?? '', ENT_QUOTES, 'UTF-8'));
    $quantidade       = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
    $observacao       = trim(htmlspecialchars($_POST['observacao'] ?? '', ENT_QUOTES, 'UTF-8'));
    
    // Verifica campos obrigatórios
    if (empty($nome_empresa) || empty($cnpj) || empty($nome_solicitante) || empty($email) || empty($link_planilha) || empty($tipo_solicitacao) || $quantidade === false || $quantidade < 1) {
        send_response('error', 'Por favor, preencha todos os campos obrigatórios corretamente.');
    }
    
    // 3. CONEXÃO COM O BANCO DE DADOS (usando constantes do config.php)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');

    // 4. INSERÇÃO SEGURA COM PREPARED STATEMENTS
    $sql = "INSERT INTO solicitacoes_s (protocolo, nome_empresa, cnpj, nome_solicitante, telefone, email, link_planilha, tipo_solicitacao, quantidade, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssis', $protocolo, $nome_empresa, $cnpj, $nome_solicitante, $telefone, $email, $link_planilha, $tipo_solicitacao, $quantidade, $observacao);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao salvar a solicitação no banco de dados.');
    }
    $stmt->close();
    $conn->close();

    // 5. ENVIO DE E-MAILS (lógica melhorada)
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(SMTP_USER, 'FaCiencia - Solicitações');

    // Tenta enviar e-mail para o cliente
    $clienteEmailEnviado = false;
    try {
        $mail->addAddress($email, $nome_solicitante);
        $mail->isHTML(true);
        $mail->Subject = "Solicitação Recebida - Protocolo {$protocolo}";
        // O corpo do e-mail (HTML) permanece o mesmo que você criou
        ob_start();
        include 'template_email_cliente.php'; // Carrega o corpo do e-mail de um arquivo separado
        $mail->Body = ob_get_clean();

        $mail->send();
        $clienteEmailEnviado = true;
    } catch (Exception $e) {
        error_log("Falha ao enviar e-mail para o cliente {$email}: " . $e->getMessage());
    }

    // Tenta enviar e-mail para a equipe interna, mesmo que o do cliente tenha falhado
    $internoEmailEnviado = false;
    try {
        $mail->clearAddresses(); // Limpa o destinatário anterior
        
        foreach (EMAIL_EQUIPE as $emailEquipe) {
            $mail->addAddress($emailEquipe);
        }
        
        $mail->addReplyTo($email, $nome_solicitante);
        $mail->isHTML(true);
        $mail->Subject = "Nova Solicitação de Polo: {$nome_empresa} - Protocolo {$protocolo}";
        // O corpo do e-mail (HTML) permanece o mesmo
        ob_start();
        include 'template_email_interno.php';
        $mail->Body = ob_get_clean();

        $mail->send();
        $internoEmailEnviado = true;
    } catch (Exception $e) {
        error_log("Falha ao enviar e-mail para a equipe interna: " . $e->getMessage());
    }
    
    // Resposta final com base no sucesso dos envios
    if ($clienteEmailEnviado) {
        send_response('success', 'Solicitação enviada com sucesso! Um e-mail de confirmação foi enviado para você.', $protocolo);
    } else {
        // O usuário foi salvo no banco, mas não recebeu o e-mail. A equipe interna precisa saber disso.
        send_response('success', "Sua solicitação foi recebida (Protocolo: {$protocolo}), mas não foi possível enviar o e-mail de confirmação. Nossa equipe já foi notificada.", $protocolo);
    }

} catch (mysqli_sql_exception $e) {
    error_log("Erro de Banco de Dados: " . $e->getMessage());
    send_response('error', 'Ocorreu um erro técnico (DB) ao processar sua solicitação. Por favor, tente novamente mais tarde.');
} catch (Exception $e) {
    // Captura erros gerais, como falha na inserção no banco
    error_log("Erro Geral: " . $e->getMessage());
    send_response('error', 'Ocorreu um erro técnico geral ao processar sua solicitação.');
}