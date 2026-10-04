# Painel Web V2 — hardening e migração

## O que foi atualizado

- Credenciais de banco removidas do código-fonte.
- PDO com prepared statements e emulação desativada no bootstrap.
- Sessões endurecidas (`HttpOnly`, `SameSite`, `Secure`, strict mode).
- Regeneração de sessão após login.
- CSRF nos fluxos principais alterados.
- Rate limit básico de login.
- `password_hash()` / `password_verify()` para `admin` e `usuario`.
- Migração compatível de senha legada no primeiro login.
- Recuperação por token aleatório, hash SHA-256, expiração e uso único.
- Senhas SSH de servidores e contas SSH preparadas para criptografia autenticada via libsodium.
- Uploads administrativos com autenticação, CSRF, MIME real, extensão compatível, limite e nome fixo controlado.
- Endpoint `update.php` não devolve mais usuário/senha de servidores.
- Endpoint de teste JSON usa prepared statement.
- Download OVPN protegido contra path traversal e corrigido erro de sintaxe.
- `exec()` local que gravava arquivo com entrada de usuário foi removido.
- Adapter SSH passou a rejeitar download remoto, shell injection e comandos perigosos fora da allowlist.
- Instalador remoto legado foi desabilitado.
- Testes/exemplos PHP antigos que não pertencem à aplicação foram removidos.
- Dump SQL sanitizado e tabela de reset criada.

## Dependências-alvo

- PHP 8.5.x: versão atualmente suportada; use o último patch disponível.
- phpseclib 2.0.55 como etapa compatível com o namespace legado; depois migrar para phpseclib 3/4 em branch dedicada.
- PHPMailer 7.1.1; a aplicação ainda possui adaptadores legados e precisa passar pelo Composer antes do deploy final.

## Bloqueadores que ainda exigem migração por módulo

O legado original contém aproximadamente 100 chamadas SQL dinâmicas em páginas antigas. Elas não foram convertidas por regex porque uma substituição automática pode introduzir regressões ou alterar a lógica de autorização.

Também existem scripts shell de instalação/atualização antigos que baixam conteúdo remoto e alteram o sistema. Eles devem ser considerados **legacy/quarantine** e não devem ser executados em produção.

Portanto, este pacote é uma base V2 endurecida, mas não deve ser anunciado como “100% seguro” até o catálogo SQL/IDOR de todos os endpoints passar por revisão e testes.
