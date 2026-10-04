# Modernização do módulo appss

## Alterações aplicadas

- Decodificação segura dos escapes hexadecimais em strings PHP, preservando aspas, HTML, JavaScript e sem reescrever a lógica de negócio.
- Formatação lexical do PHP para facilitar manutenção e auditoria.
- Remoção da inclusão dinâmica baseada diretamente em `page`; o roteador agora aceita apenas a página `termos` e usa `downloads.php` como fallback.
- Consulta do download convertida para prepared statement com ID inteiro parametrizado.
- Recursos externos do módulo que usavam HTTP foram atualizados para HTTPS.

## Validações

- Todos os 5 arquivos PHP do módulo passaram em `php -l`.
- Todos os arquivos PHP do projeto passaram em `php -l` sem erros.
- `tests/security_v3.php`: PASS.
- `bash -n ops/install.sh`: PASS.
- `git diff --check`: PASS.

## Limites conhecidos

A conversão tornou o código auditável e removeu os padrões mais perigosos encontrados no relatório, mas não substitui testes funcionais com MariaDB, navegador e arquivos reais. O módulo deve ser exercitado em staging antes de uma liberação de produção, especialmente o download de arquivos, listagem de aplicativos e página de termos.
