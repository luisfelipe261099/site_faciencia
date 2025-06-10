<?php
// Configuração de exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Importar as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Caminho para o autoload do Composer (ajuste conforme sua estrutura)
    require_once 'vendor/autoload.php';

    // Função para log de erros
    function logError($message) {
        $log_file = 'logs/error_log_' . date('Y-m') . '.log';
        if (!file_exists('logs')) {
            mkdir('logs', 0755, true);
        }
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
    }

    try {
        // Coletando e sanitizando dados do formulário
        $indicador_nome = filter_input(INPUT_POST, 'indicador_nome', FILTER_SANITIZE_STRING);
        $indicador_email = filter_input(INPUT_POST, 'indicador_email', FILTER_SANITIZE_EMAIL);
        $indicador_telefone = filter_input(INPUT_POST, 'indicador_telefone', FILTER_SANITIZE_STRING);
        $indicador_relacao = filter_input(INPUT_POST, 'indicador_relacao', FILTER_SANITIZE_STRING);

        $indicado_nome = filter_input(INPUT_POST, 'indicado_nome', FILTER_SANITIZE_STRING);
        $indicado_email = filter_input(INPUT_POST, 'indicado_email', FILTER_SANITIZE_EMAIL);
        $indicado_telefone = filter_input(INPUT_POST, 'indicado_telefone', FILTER_SANITIZE_STRING);
        $indicado_cidade = filter_input(INPUT_POST, 'indicado_cidade', FILTER_SANITIZE_STRING);

        $area_interesse = filter_input(INPUT_POST, 'area_interesse', FILTER_SANITIZE_STRING);
        $curso_especifico = filter_input(INPUT_POST, 'curso_especifico', FILTER_SANITIZE_STRING);
        $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING);
        $autorizacao = isset($_POST['autorizacao']) ? 'Sim' : 'Não';

        // Validação rigorosa dos dados
        $errors = [];
        if (!$indicador_nome) $errors[] = "Nome do indicador é obrigatório";
        if (!filter_var($indicador_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email do indicador inválido";
        if (!$indicado_nome) $errors[] = "Nome do indicado é obrigatório";
        if (!filter_var($indicado_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email do indicado inválido";
        if ($autorizacao !== 'Sim') $errors[] = "Autorização é obrigatória";

        if (!empty($errors)) {
            throw new Exception("Erros de validação: " . implode(", ", $errors));
        }

        // Configurações para teste local ou desenvolvimento
        $mail_config = [
            'host' => 'smtp.gmail.com',  // Exemplo: use seu próprio SMTP
            'username' => 'seu_email@gmail.com',
            'password' => 'sua_senha_de_app',
            'port' => 587,
            'from_email' => 'seu_email@gmail.com',
            'from_name' => 'FaCiencia Indicações',
            'to_email' => 'secretaria@faciencia.edu.br'
        ];

        // Criar instância do PHPMailer
        $mail = new PHPMailer(true);
        
        // Configurações de depuração
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Habilita saída de depuração detalhada
        
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = $mail_config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mail_config['username'];
        $mail->Password   = $mail_config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mail_config['port'];

        // Remetente e destinatário
        $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
        $mail->addAddress($mail_config['to_email']);
        $mail->addReplyTo($indicador_email, $indicador_nome);

        // Configurações do e-mail
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Nova Indicação de {$indicador_nome}";

        // Corpo do e-mail
        $mail->Body = "
        <h1>Nova Indicação Recebida</h1>
        <h2>Dados do Indicador</h2>
        <p><strong>Nome:</strong> {$indicador_nome}<br>
        <strong>Email:</strong> {$indicador_email}<br>
        <strong>Telefone:</strong> {$indicador_telefone}<br>
        <strong>Relação:</strong> {$indicador_relacao}</p>
        
        <h2>Dados do Indicado</h2>
        <p><strong>Nome:</strong> {$indicado_nome}<br>
        <strong>Email:</strong> {$indicado_email}<br>
        <strong>Telefone:</strong> {$indicado_telefone}<br>
        <strong>Cidade:</strong> {$indicado_cidade}</p>
        
        <h2>Detalhes da Indicação</h2>
        <p><strong>Área de Interesse:</strong> {$area_interesse}<br>
        <strong>Curso Específico:</strong> {$curso_especifico}<br>
        <strong>Mensagem:</strong> {$mensagem}</p>
        ";

        // Texto alternativo para clientes de e-mail sem HTML
        $mail->AltBody = "Nova indicação de {$indicador_nome} para {$indicado_nome}. 
        Email do indicado: {$indicado_email}, Telefone: {$indicado_telefone}";

        // Enviar e-mail
        $mail->send();

        // Resposta de sucesso
        http_response_code(200);
        echo json_encode([
            'status' => 'success', 
            'message' => 'Indicação enviada com sucesso!'
        ]);

    } catch (Exception $e) {
        // Log do erro
        logError("Erro no processamento da indicação: " . $e->getMessage());

        // Resposta de erro
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Erro ao processar indicação: ' . $e->getMessage()
        ]);
        exit;
    }
} else {
    // Acesso direto não autorizado
    http_response_code(403);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Método de requisição não permitido'
    ]);
}
?>