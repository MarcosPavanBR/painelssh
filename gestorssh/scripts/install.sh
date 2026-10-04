#!/usr/bin/env bash
set -euo pipefail
cat >&2 <<'EOF'
INSTALADOR REMOTO LEGADO DESABILITADO.

A versão antiga baixava scripts por HTTP e executava conteúdo remoto com permissões amplas.
Na V2, a instalação de um servidor deve usar um pacote local assinado e uma verificação SHA-256/assinatura antes da execução.
Consulte SECURITY_V2.md.
EOF
exit 78
