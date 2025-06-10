<?php
// Usando namespaces para PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carrega o autoloader do Composer
require 'vendor/autoload.php';

// --- CONFIGURAÇÕES DE SEGURANÇA E AMBIENTE ---

// Define o cabeçalho de resposta como JSON
header('Content-Type: application/json; charset=utf-8');

// Configurações de erro para um ambiente de desenvolvimento.
// Em produção, os erros devem ser logados em um arquivo, não exibidos na tela.
ini_set('display_errors', 0); // Desativar em produção
ini_set('display_startup_errors', 0); // Desativar em produção
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php-error.log'); // Defina um caminho seguro para o log de erros


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
try {
    // 1. VERIFICAÇÃO INICIAL E VALIDAÇÃO DE MÉTODO
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        send_response('error', 'Método de requisição inválido.');
    }

    // 2. VALIDAÇÃO E SANITIZAÇÃO DOS DADOS DO FORMULÁRIO
    // Coleta, limpa e valida cada campo.
    $nome_empresa     = trim(filter_input(INPUT_POST, 'nome_empresa', FILTER_SANITIZE_STRING));
    $cnpj             = preg_replace('/[^0-9]/', '', $_POST['cnpj'] ?? '');
    $nome_solicitante = trim(filter_input(INPUT_POST, 'nome_solicitante', FILTER_SANITIZE_STRING));
    $telefone         = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
    $email            = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $link_planilha    = filter_input(INPUT_POST, 'link_planilha', FILTER_VALIDATE_URL);
    $tipo_solicitacao = trim(filter_input(INPUT_POST, 'tipo_solicitacao', FILTER_SANITIZE_STRING));
    $quantidade       = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
    $observacao       = trim(filter_input(INPUT_POST, 'observacao', FILTER_SANITIZE_STRING));
    $protocolo        = $_POST['protocolo'] ?? ('POLO-' . date('Ymd') . '-' . mt_rand(10000, 99999));

    // Verifica campos obrigatórios
    if (empty($nome_empresa) || empty($cnpj) || empty($nome_solicitante) || empty($email) || empty($link_planilha) || empty($tipo_solicitacao) || $quantidade === false || $quantidade < 1) {
        send_response('error', 'Por favor, preencha todos os campos obrigatórios corretamente.');
    }

    // 3. CONEXÃO COM O BANCO DE DADOS (MySQLi)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Habilita exceções para o MySQLi
    $conn = new mysqli('srv1487.hstgr.io', 'u682219090_faciencia_erp', 'T3cn0l0g1a@', 'u682219090_faciencia_erp');
    $conn->set_charset('utf8mb4');


    // 4. INSERÇÃO SEGURA COM PREPARED STATEMENTS
    $sql = "INSERT INTO solicitacoes_s 
                (protocolo, nome_empresa, cnpj, nome_solicitante, telefone, email, link_planilha, tipo_solicitacao, quantidade, observacao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    // 'ssssssssis' define os tipos de dados para cada parâmetro: s = string, i = integer
    $stmt->bind_param('ssssssssis',
        $protocolo,
        $nome_empresa,
        $cnpj,
        $nome_solicitante,
        $telefone,
        $email,
        $link_planilha,
        $tipo_solicitacao,
        $quantidade,
        $observacao
    );

    // Executa a inserção
    if (!$stmt->execute()) {
        throw new Exception('Erro ao salvar a solicitação no banco de dados.');
    }

    $stmt->close();
    $conn->close();

    // 5. ENVIO DE E-MAILS (PHPMailer)
    $mail = new PHPMailer(true);

    // Configurações SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'faciencia@lfmtecnologia.com';
    $mail->Password   = 'Faciencai@2025#$T3cn0l0g1a@';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    
    // E-mail para o polo solicitante (Cliente)
    $mailPolo = clone $mail;
    $mailPolo->setFrom('faciencia@lfmtecnologia.com', 'FaCiencia - Solicitação de Polo');
    $mailPolo->addAddress($email, $nome_solicitante);
    $mailPolo->isHTML(true);
    $mailPolo->Subject = "Solicitação Recebida - Protocolo {$protocolo}";
    $mailPolo->Body    = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; line-height: 1.6;'>
            <div style='background-color: #9d359c; color: white; padding: 20px; text-align: center;'>
                <h1>Solicitação de Polo Recebida</h1>
            </div>
            <div style='padding: 20px;'>
                <h2>Prezado(a) {$nome_solicitante},</h2>
                
                <p>Informamos que a FaCiencia recebeu sua solicitação de polo com sucesso!</p>
                
                <div style='background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3>Detalhes do Protocolo</h3>
                    <ul style='list-style-type: none; padding: 0;'>
                        <li><strong>Número do Protocolo:</strong> {$protocolo}</li>
                        <li><strong>Empresa:</strong> {$nome_empresa}</li>
                        <li><strong>Tipo de Solicitação:</strong> {$tipo_solicitacao}</li>
                        <li><strong>Data da Solicitação:</strong> " . date('d/m/Y H:i:s') . "</li>
                    </ul>
                </div>
                
                <p>Sua solicitação está em análise pela nossa equipe. Em breve, entraremos em contato para dar continuidade ao processo.</p>
                
                <p>Caso tenha dúvidas, entre em contato conosco:</p>
                <p>
                    <strong>Telefone:</strong> (41) 9 9256-2500<br>
                    <strong>Email:</strong> contato@faciencia.edu.br
                </p>
                
                <p style='color: #666; margin-top: 30px;'>
                    <em>Atenciosamente,<br>
                    Equipe FaCiencia<br>
                    Educação de qualidade e inovação para seu futuro profissional.</em>
                </p>
            </div>
        </body>
        </html>
    ";
    $mailPolo->send();

    // E-mail para a equipe interna
    $mailInterno = clone $mail;
    $mailInterno->setFrom('faciencia@lfmtecnologia.com', 'FaCiencia - Nova Solicitação');
    $mailInterno->addAddress('adriana.basso@faciencia.edu.br');
    $mailInterno->addAddress('mariane.souza@faciencia.edu.br');
    $mailInterno->addAddress('secretaria@faciencia.edu.br');
    $mailInterno->addAddress('barbara.batista@faciencia.edu.br');
    $mailInterno->addAddress('niceia.rodrigues@faciencia.edu.br');
    $mailInterno->addReplyTo($email, $nome_solicitante);
    $mailInterno->isHTML(true);
    $mailInterno->Subject = "Nova Solicitação de Polo: {$nome_empresa} - Protocolo {$protocolo}";
    $mailInterno->Body    = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #9d359c; color: white; padding: 20px; text-align: center;'>
                <h1>Nova Solicitação de Polo</h1>
            </div>
            <div style='padding: 20px;'>
                <h2>Detalhes Completos da Solicitação</h2>
                
                <p><strong>Protocolo:</strong> {$protocolo}</p>
                <p><strong>Nome da Empresa:</strong> {$nome_empresa}</p>
                <p><strong>CNPJ:</strong> {$cnpj}</p>
                <p><strong>Nome do Solicitante:</strong> {$nome_solicitante}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Telefone:</strong> {$telefone}</p>
                <p><strong>Link da Planilha:</strong> <a href='{$link_planilha}'>{$link_planilha}</a></p>
                <p><strong>Tipo de Solicitação:</strong> {$tipo_solicitacao}</p>
                <p><strong>Quantidade:</strong> {$quantidade}</p>
                <p><strong>Observações:</strong><br>" . nl2br(htmlspecialchars($observacao)) . "</p>
                <p><strong>Registro no banco:</strong> Sim</p>
            </div>
        </body>
        </html>
    ";
    $mailInterno->send();
    
    send_response('success', 'Solicitação enviada com sucesso!', $protocolo);

} catch (mysqli_sql_exception $e) {
    error_log("Erro de Banco de Dados: " . $e->getMessage());
    send_response('error', 'Ocorreu um erro técnico ao processar sua solicitação. Por favor, tente novamente mais tarde.');
} catch (Exception $e) {
    // Captura erros de PHPMailer e outras exceções gerais
    error_log("Erro Geral ou de E-mail: " . $e->getMessage());
    // Se o erro foi no envio de e-mail, a solicitação já pode ter sido salva.
    // O ideal é logar o erro para análise manual, mas informar o usuário que a solicitação foi recebida.
    send_response('success', 'Sua solicitação foi recebida, mas ocorreu um problema ao enviar a notificação por e-mail. Protocolo: ' . ($protocolo ?? 'N/A'));
}
