---
name: testing-for-sensitive-data-exposure
description: >-
  Identifies sensitive data exposure vulnerabilities including API key
  leakage, PII in responses, insecure storage, and unprotected data
  transmission during security assessments. Use during authorized
  penetration tests or GDPR/PCI DSS/HIPAA data protection compliance
  reviews.
domain: cybersecurity
subdomain: web-application-security
tags: [penetration-testing, data-exposure, pii, owasp, web-security, api-keys, secrets]
version: '1.0'
author: mahipal
license: Apache-2.0
source: https://github.com/mukul975/Anthropic-Cybersecurity-Skills
nist_ai_rmf: [MEASURE-2.7, MAP-5.1, MANAGE-2.4]
atlas_techniques: [AML.T0070, AML.T0066, AML.T0082]
nist_csf: [PR.PS-01, ID.RA-01, PR.DS-10, DE.CM-01]
mitre_attack: [T1190, T1059.007, T1505.003, T1083]
---

# Testing for Sensitive Data Exposure

## When to Use

- Assessing data protection controls during authorized penetration tests
- GDPR / PCI DSS / HIPAA compliance evaluations
- Identifying leaked API keys, credentials, tokens, secrets in application responses
- Verifying encryption in transit and at rest for PII/financial/health data

## Prerequisites

- Written penetration testing agreement with data handling scope
- trufflehog, gitleaks (secret scanning), testssl.sh (TLS), Burp Suite

## Workflow

### Step 1: Scan Client-Side Code for Secrets

```bash
curl -s "https://target.example.com/" | grep -oP 'src="[^"]*\.js[^"]*"'
# then fetch each JS file and grep for:
grep -inE "(api[_-]?key|apikey|aws[_-]?(access|secret)|private[_-]?key|password|secret|token|AKIA[0-9A-Z]{16})"
# common leaked secret patterns
grep -nP "(AIza[0-9A-Za-z-_]{35}|AKIA[0-9A-Z]{16}|sk-[a-zA-Z0-9]{48}|ghp_[a-zA-Z0-9]{36})"
```
Also check for exposed `.env`, `config.json`, `.git/config`, `.aws/credentials`, and source maps (`app.js.map`).

### Step 2: API Response Over-Exposure

Check for fields that should never be returned to the client: `password`/`password_hash`, SSN/national ID, full credit card numbers, `api_key`/`secret_key`/`refresh_token`, internal DB IDs, IP addresses. Compare public vs. authenticated response field sets. Check error responses for stack traces / internal paths / version info leakage.

### Step 3: Data Transmission Security

Verify TLS everywhere (no plain HTTP, no mixed content), check that sensitive form actions use HTTPS, and that WebSocket connections use `wss://` not `ws://`. Flag sensitive data appearing in URL query strings (logged in browser/server/proxy history).

### Step 4: Browser Storage

Check cookies' security attributes, and inspect `localStorage`/`sessionStorage`/IndexedDB/Cache Storage for tokens or PII stored client-side (vulnerable to XSS theft). Sensitive pages should send `Cache-Control: no-store`; sensitive form fields (password, card number) should have `autocomplete="off"`.

### Step 5: Git/Source Exposure

```bash
curl -s "https://target.example.com/.git/config"
git-dumper https://target.example.com/.git /tmp/target-repo
trufflehog filesystem /tmp/target-repo
gitleaks detect --source /tmp/target-repo -v
```

### Step 6: Data Masking

Credit cards should show only last 4 digits, SSNs should be masked, password hashes should never appear in API responses, CSV/export features should not leak unmasked PII, and duplicate-registration errors should be generic ("Registration failed", not "email already exists").

## Key Concepts

| Concept | Description |
|---------|-------------|
| Data Over-Exposure | API returns more fields than the client needs |
| Secret Leakage | Keys/tokens exposed in client-side code, logs, or repos |
| Data Masking | Redacting sensitive values in UI/API output |

## Remediation (defensive takeaway)

1. Never hardcode secrets in client-side code; proxy third-party calls through the backend
2. Rotate any credentials found exposed, immediately
3. Filter API response fields server-side (return only what the client needs — no blanket `SELECT *` serialization)
4. Mask PII/financial data in all responses and exports
5. Add `Cache-Control: no-store` to sensitive endpoints
6. Run secret scanning (trufflehog/gitleaks) in CI/CD
