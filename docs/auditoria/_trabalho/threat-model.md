# Ativos
Credenciais, contas, dados de clientes, servidores SSH e faturas.

# Atores
Anônimo, usuário autenticado, admin, atacante externo.

# Fronteiras de confiança
Browser→PHP, PHP→MariaDB, PHP→SSH.

# Entradas
POST/GET, uploads, parâmetros de roteamento, credenciais SSH.

# Operações críticas
Login, troca de senha, gestão de usuários, criação/exclusão de contas SSH, pagamentos/faturas.

# Impactos
Tomada de conta, alteração de dados, acesso a servidores, fraude e indisponibilidade.
