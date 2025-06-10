<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber dados do formulário
    $nome = $_POST['nome'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $nascimento = $_POST['nascimento'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone_real'] ?? ''; // Renomeado para evitar confusão com o honeypot
    $cidade = $_POST['cidade'] ?? '';
    $modalidade = $_POST['modalidade'] ?? '';
    $curso = $_POST['curso'] ?? '';

    // Dados específicos por modalidade
    $dados_adicionais = [];

    if ($modalidade === 'enem') {
        $dados_adicionais['ano_enem'] = $_POST['ano_enem'] ?? '';
        $dados_adicionais['nota_enem'] = $_POST['nota_enem'] ?? '';
    } elseif ($modalidade === 'transferencia') {
        $dados_adicionais['instituicao_origem'] = $_POST['instituicao_origem'] ?? '';
        $dados_adicionais['curso_origem'] = $_POST['curso_origem'] ?? '';
    } elseif ($modalidade === 'segunda-graduacao') {
        $dados_adicionais['instituicao_anterior'] = $_POST['instituicao_anterior'] ?? '';
        $dados_adicionais['curso_anterior'] = $_POST['curso_anterior'] ?? '';
        $dados_adicionais['ano_conclusao'] = $_POST['ano_conclusao'] ?? '';
    }

    // Converter modalidade para texto legível
    $modalidade_texto = '';
    switch ($modalidade) {
        case 'vestibular':
            $modalidade_texto = 'Vestibular Tradicional';
            break;
        case 'enem':
            $modalidade_texto = 'Nota do ENEM';
            break;
        case 'transferencia':
            $modalidade_texto = 'Transferência Externa';
            break;
        case 'segunda-graduacao':
            $modalidade_texto = 'Segunda Graduação';
            break;
    }

    // Converter curso para texto legível
    $curso_texto = 'Tecnólogo em Recursos Humanos';

    // Formatar data de nascimento
    $nascimento_formatado = !empty($nascimento) ? date('d/m/Y', strtotime($nascimento)) : '';

    // Criar o e-mail
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'desenvolvimento@lfmtecnologia.com';
        $mail->Password   = 'T3cn0l0g1a@';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Remetente e destinatários
        $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Site FaCiencia');
        $mail->addAddress('secretaria@faciencia.edu.br');
        $mail->addCC($email); // Envia cópia para o candidato

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Nova Inscrição no Vestibular FaCiencia 2025';

        // Corpo do e-mail com layout bonito
        $mensagem = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #9d359c;
                    color: white;
                    padding: 15px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    padding: 20px;
                }
                .footer {
                    background-color: #f5f5f5;
                    padding: 15px;
                    text-align: center;
                    font-size: 12px;
                    border-radius: 0 0 5px 5px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid #ddd;
                }
                th, td {
                    padding: 10px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
                .highlight {
                    background-color: #ffe925;
                    padding: 2px 5px;
                    border-radius: 3px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Nova Inscrição no Vestibular FaCiencia 2025</h2>
                </div>
                <div class="content">
                    <p>Uma nova inscrição foi realizada no site da FaCiencia:</p>

                    <h3>Dados Pessoais</h3>
                    <table>
                        <tr>
                            <th>Nome</th>
                            <td>' . htmlspecialchars($nome) . '</td>
                        </tr>
                        <tr>
                            <th>CPF</th>
                            <td>' . htmlspecialchars($cpf) . '</td>
                        </tr>
                        <tr>
                            <th>Data de Nascimento</th>
                            <td>' . htmlspecialchars($nascimento_formatado) . '</td>
                        </tr>
                        <tr>
                            <th>E-mail</th>
                            <td>' . htmlspecialchars($email) . '</td>
                        </tr>
                        <tr>
                            <th>Telefone</th>
                            <td>' . htmlspecialchars($telefone) . '</td>
                        </tr>
                        <tr>
                            <th>Cidade</th>
                            <td>' . htmlspecialchars($cidade) . '</td>
                        </tr>
                    </table>

                    <h3>Informações do Vestibular</h3>
                    <table>
                        <tr>
                            <th>Modalidade</th>
                            <td><span class="highlight">' . htmlspecialchars($modalidade_texto) . '</span></td>
                        </tr>
                        <tr>
                            <th>Curso</th>
                            <td><span class="highlight">' . htmlspecialchars($curso_texto) . '</span></td>
                        </tr>';

        // Adicionar dados específicos por modalidade
        if ($modalidade === 'enem') {
            $mensagem .= '
                        <tr>
                            <th>Ano do ENEM</th>
                            <td>' . htmlspecialchars($dados_adicionais['ano_enem']) . '</td>
                        </tr>
                        <tr>
                            <th>Nota do ENEM</th>
                            <td>' . htmlspecialchars($dados_adicionais['nota_enem']) . '</td>
                        </tr>';
        } elseif ($modalidade === 'transferencia') {
            $mensagem .= '
                        <tr>
                            <th>Instituição de Origem</th>
                            <td>' . htmlspecialchars($dados_adicionais['instituicao_origem']) . '</td>
                        </tr>
                        <tr>
                            <th>Curso de Origem</th>
                            <td>' . htmlspecialchars($dados_adicionais['curso_origem']) . '</td>
                        </tr>';
        } elseif ($modalidade === 'segunda-graduacao') {
            $mensagem .= '
                        <tr>
                            <th>Instituição Anterior</th>
                            <td>' . htmlspecialchars($dados_adicionais['instituicao_anterior']) . '</td>
                        </tr>
                        <tr>
                            <th>Curso Anterior</th>
                            <td>' . htmlspecialchars($dados_adicionais['curso_anterior']) . '</td>
                        </tr>
                        <tr>
                            <th>Ano de Conclusão</th>
                            <td>' . htmlspecialchars($dados_adicionais['ano_conclusao']) . '</td>
                        </tr>';
        }

        $mensagem .= '
                    </table>

                    <p>Data da inscrição: ' . date('d/m/Y H:i:s') . '</p>';

        if ($modalidade === 'vestibular') {
            $mensagem .= '
                    <p><strong>Observação:</strong> O candidato optou pelo Vestibular Tradicional e deverá realizar a prova online.</p>';
        }

        $mensagem .= '
                </div>
                <div class="footer">
                    <p>Este é um e-mail automático enviado pelo sistema de inscrição do Vestibular FaCiencia 2025.</p>
                    <p>&copy; 2025 FaCiencia. Todos os direitos reservados.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->Body = $mensagem;
        $mail->AltBody = "Nova inscrição no Vestibular FaCiencia 2025\n\nNome: $nome\nCPF: $cpf\nE-mail: $email\nModalidade: $modalidade_texto\nCurso: $curso_texto";

        $mail->send();

        // Resposta de sucesso
        echo json_encode(['sucesso' => true, 'mensagem' => 'Inscrição realizada com sucesso!']);

    } catch (Exception $e) {
        // Resposta de erro
        echo json_encode(['sucesso' => false, 'mensagem' => "Erro ao enviar e-mail: {$mail->ErrorInfo}"]);
    }
} else {
    // Método inválido
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido']);
}
?>
