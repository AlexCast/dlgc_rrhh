---
name: performing-security-headers-audit
description: >-
  Audits HTTP security headers including CSP, HSTS, X-Frame-Options, and
  cookie attributes to identify missing or misconfigured browser-level
  protections. Use during authorized web application security assessments
  or as a low-risk configuration review / CI security gate check.
domain: cybersecurity
subdomain: web-application-security
tags: [penetration-testing, security-headers, csp, hsts, owasp, web-security, hardening]
version: '1.0'
author: mahipal
license: Apache-2.0
source: https://github.com/mukul975/Anthropic-Cybersecurity-Skills
nist_csf: [PR.PS-01, ID.RA-01, PR.DS-10, DE.CM-01]
mitre_attack: [T1190, T1059.007, T1505.003, T1083]
---

# Performing Security Headers Audit

## When to Use

- Standard configuration review during authorized web application assessments
- Evaluating browser-level protections against XSS, clickjacking, data leakage
- Compliance checks (PCI DSS, SOC 2)
- CI/CD pipeline security gate checks for new deployments

## Prerequisites

- curl for fetching response headers
- Optional: SecurityHeaders.com, Mozilla Observatory, testssl.sh, Burp Suite

## Workflow

### Step 1: Collect Security Headers

```bash
curl -s -I "https://target.example.com/" | grep -iE \
  "(strict-transport|content-security|x-frame|x-content-type|x-xss|referrer-policy|permissions-policy|cross-origin|set-cookie|server|x-powered-by|cache-control)"
```
Check headers across multiple pages (`/`, `/login`, `/api/health`, `/admin`, `/account/settings`, static assets) and confirm HTTP redirects to HTTPS.

### Step 2: Transport Security (HSTS)

Expected: `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`. Verify `max-age >= 31536000`, `includeSubDomains`, and check the [HSTS preload list](https://hstspreload.org/). Confirm session cookies carry the `Secure` flag and there's no mixed HTTP content on HTTPS pages.

### Step 3: Content Security Policy (CSP)

Flag: `unsafe-inline` (allows inline scripts), `unsafe-eval`, wildcard `*` sources, missing `default-src`, `Content-Security-Policy-Report-Only` (doesn't block, only logs). Check whitelisted CDN domains for JSONP endpoints that could enable CSP bypass. Use [CSP Evaluator](https://csp-evaluator.withgoogle.com/) for automated analysis.

### Step 4: Frame Protection / Click Defense

- `X-Frame-Options`: expect `DENY` or `SAMEORIGIN`
- CSP `frame-ancestors 'none'` or `'self'` (supersedes X-Frame-Options)
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin` or `no-referrer`

### Step 5: Cookie Security Attributes

Every session/auth cookie should have `Secure`, `HttpOnly`, and `SameSite` set. Consider `__Host-`/`__Secure-` cookie name prefixes for additional guarantees.

### Step 6: Permissions Policy and Information Disclosure

- `Permissions-Policy: camera=(); microphone=(); geolocation=()` for unused features
- COOP/COEP/CORP headers for cross-origin isolation
- Remove/generalize `Server` and `X-Powered-By` (avoid leaking `PHP/8.1.2` etc.)
- `Cache-Control: no-store` on sensitive pages

## Key Concepts

| Concept | Description |
|---------|-------------|
| HSTS | Forces HTTPS-only, prevents protocol downgrade |
| CSP | Restricts allowed script/style/resource origins |
| SameSite Cookie | Controls cross-site cookie transmission |

## Remediation checklist (defensive takeaway)

1. Set `Secure`, `HttpOnly`, `SameSite=Strict|Lax` on all session cookies (critical if missing)
2. Add HSTS with `max-age >= 31536000`
3. Replace `unsafe-inline` in CSP with a nonce/hash-based policy
4. Add `X-Frame-Options: DENY` and `X-Content-Type-Options: nosniff`
5. Add `Referrer-Policy` and `Permissions-Policy`
6. Strip/generalize `Server` and `X-Powered-By` headers
