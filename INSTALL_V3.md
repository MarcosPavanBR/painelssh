# Instalação — Painel Web V3

## Status da entrega

**Pacote instalável: SIM. Produção: NÃO liberada.**

Esta entrega contém o código V3, banco SQL, scripts CLI, dependências declaradas e documentação de auditoria. A instalação deve ser feita primeiro em staging. O gate de produção permanece bloqueado pelos achados descritos em `SECURITY_V3.md` e `docs/auditoria/relatorio-auditoria.md`.

## Requisitos

- Linux/Unix recomendado.
- PHP 8.1 ou superior.
- Extensões PHP: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `sodium`, `json`, `curl` e extensões exigidas pelo ambiente legado.
- MariaDB/MySQL compatível com o SQL fornecido.
- Composer 2.x para resolver/verificar `composer.json`.
- HTTPS em produção.
- Acesso CLI para os scripts de criação do administrador e migração de segredos.

> O ambiente usado para esta auditoria não possuía MariaDB/pdo_mysql nem Composer. Por isso, esses passos foram documentados, mas não foram declarados como testes executados.

## 1. Publicação

1. Extraia o ZIP em um diretório fora do webroot, se o servidor permitir.
2. Aponte o document root para o diretório público da aplicação conforme sua infraestrutura. Não exponha `.git`, `docs`, `tests`, `quarantine`, arquivos SQL ou scripts CLI por HTTP.
3. Preserve permissões de leitura para o usuário do PHP e escrita somente nos diretórios que a aplicação realmente utilizar.
4. Remova ou bloqueie execução HTTP de arquivos `.sh`, `.sql`, `.md`, `.json` e scripts da pasta `bin/`.

## 2. Banco de dados

Crie um banco vazio e um usuário com privilégios mínimos. Importe:

```bash
mysql -u root -p -e "CREATE DATABASE sshplus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p sshplus < database/bdgestorssh.secure.sql
```

O arquivo principal de estrutura é `database/bdgestorssh.secure.sql`. A cópia histórica `gestorssh/bdgestorssh.sql` deve ser tratada como referência legada, não como primeira escolha para uma instalação nova.

## 3. Variáveis de ambiente

Configure pelo menos:

```text
APP_TIMEZONE=America/Sao_Paulo
APP_DEBUG=0
FORCE_SECURE_COOKIES=1
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sshplus
DB_USER=<usuario-do-banco>
DB_PASSWORD=<senha-do-banco>
APP_URL=https://seu-dominio.example
APP_ACCESS_KEY=<chave-de-acesso-da-aplicacao>
APP_SECRET_KEY=<32-bytes-em-base64>
```

`APP_SECRET_KEY` precisa representar exatamente 32 bytes. Gere uma chave com:

```bash
php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'
```

Nunca coloque a chave diretamente em arquivos versionados.

## 4. Dependências PHP

Na raiz do projeto:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

Se a instalação for controlada por CI/CD, gere e versione `composer.lock` antes do primeiro release de produção. A auditoria desta entrega não gerou o lock porque Composer não estava disponível no ambiente de análise.

## 5. Criar o primeiro administrador

Depois que o banco e o ambiente estiverem acessíveis:

```bash
php bin/create_admin.php LOGIN 'UMA-SENHA-FORTE-COM-PELO-MENOS-12-CARACTERES' email@dominio.example 'Administrador'
```

A senha é armazenada com `password_hash()`.

## 6. Migração de segredos SSH

Se a instalação estiver migrando dados legados que ainda armazenam segredos sem o envelope `v1:`, execute após configurar `APP_SECRET_KEY`:

```bash
php bin/migrate_secrets.php
```

Faça backup do banco antes da migração e valide a recuperação funcional dos segredos em staging.

## 7. Validação antes de abrir o serviço

Execute:

```bash
find . -type f -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
find . -type f -name '*.sh' -not -path './vendor/*' -print0 | xargs -0 -n1 bash -n
php tests/security_v3.php
```

Depois, em staging com banco real, faça testes funcionais de login, logout, recuperação de senha, CRUD de usuários, isolamento entre usuários, uploads, operações SSH e integrações externas.

## 8. Segurança operacional

- Mantenha `APP_DEBUG=0`.
- Use HTTPS e cookies seguros.
- Não exponha a pasta `quarantine/legacy` nem os instaladores históricos.
- Não execute scripts de instalação legados armazenados em `quarantine/legacy`.
- Restrinja SSH e credenciais do sistema ao mínimo necessário.
- Faça backup antes de migrações de esquema ou segredos.
- Monitore logs do PHP/webserver e falhas de autenticação.

## 9. Gate de produção

A instalação pode ser preparada e testada em staging, mas **não deve ser declarada pronta para produção** até que:

1. as consultas SQL dinâmicas restantes sejam migradas para prepared statements;
2. cada endpoint afetado passe por teste de autorização/IDOR;
3. MariaDB/pdo_mysql seja usado nos testes end-to-end;
4. Composer resolva as dependências e um `composer.lock` seja produzido;
5. uploads, recuperação de senha, CSRF, XSS e fluxos SSH sejam retestados;
6. os resultados sejam incorporados ao relatório de auditoria e o gate seja reexecutado.

## Artefatos

- `README_V3.md` — visão geral e status.
- `SECURITY_V3.md` — controles e limitações.
- `docs/auditoria/relatorio-auditoria.md` — relatório técnico.
- `docs/auditoria/relatorio-auditoria.pdf` — versão PDF do relatório.
- `database/bdgestorssh.secure.sql` — estrutura inicial do banco.
- `bin/create_admin.php` — criação do primeiro administrador.
- `bin/migrate_secrets.php` — migração de segredos SSH legados.
- `tests/security_v3.php` — regressões de segurança estáticas.
