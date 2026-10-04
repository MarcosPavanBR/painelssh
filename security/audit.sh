#!/usr/bin/env bash
set -euo pipefail
ROOT="${1:-.}"
echo '[PHP syntax]'
find "$ROOT" -type f -name '*.php' -not -path '*/vendor/*' -print0 | xargs -0 -n1 php -l >/tmp/painel_php_lint.log
grep -E 'Parse error|Errors parsing|Fatal error' /tmp/painel_php_lint.log && exit 1 || true
echo '[Hardcoded secrets]'
grep -RInE --exclude-dir=vendor --exclude-dir=.git --exclude='*.min.js' '(DB_PASSWORD|API_KEY|BOT_TOKEN|password[[:space:]]*=[[:space:]]*["'"'"']|root[[:space:]]*,[[:space:]]*["'"'"'])' "$ROOT" || true
echo '[Dangerous shell APIs]'
grep -RInE --include='*.php' --exclude-dir=vendor --exclude-dir=phpmailer '(shell_exec|passthru|proc_open|popen|system\(|exec\()' "$ROOT" || true
echo '[Remote script execution patterns]'
grep -RInE --include='*.sh' --include='*.php' '(wget .*http:|curl .*\|.*(bash|sh)|bash[[:space:]]+<\(|chmod[[:space:]]+777)' "$ROOT" || true
echo '[Completed]'
