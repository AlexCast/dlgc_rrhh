---
name: performing-csrf-attack-simulation
description: >-
  Tests web applications for Cross-Site Request Forgery vulnerabilities by
  crafting forged requests that exploit authenticated user sessions during
  authorized security assessments. Use to validate anti-CSRF token
  implementations and SameSite cookie enforcement on state-changing actions.
domain: cybersecurity
subdomain: web-application-security
tags: [penetration-testing, csrf, owasp, web-security, session-management, burpsuite]
version: '1.0'
author: mahipal
license: Apache-2.0
source: https://github.com/mukul975/Anthropic-Cybersecurity-Skills
nist_csf: [PR.PS-01, ID.RA-01, PR.DS-10, DE.CM-01]
mitre_attack: [T1190, T1059.007, T1505.003, T1083]
---

# Performing CSRF Attack Simulation

> **Legal Notice:** Authorized security testing only.

## When to Use

- Testing state-changing actions (password change, fund transfer, settings) for CSRF
- Validating anti-CSRF token implementation strength
- Validating SameSite cookie attribute enforcement across browsers

## Prerequisites

- Written penetration testing agreement
- Two browser sessions (victim + attacker)
- Local HTTP server for hosting CSRF PoC pages (`python3 -m http.server`)

## Workflow

### Step 1: Identify State-Changing Requests

Review all POST/PUT/DELETE requests: email/password change, transfers, permission changes, resource creation/deletion, security toggles (2FA disable). Check for CSRF tokens, custom headers, SameSite cookies, Referer/Origin validation.

### Step 2: Analyze Anti-CSRF Token Implementation

Test each of: token removed entirely, empty token, random/invalid token, reused expired token, and User B's token replayed with User A's session — a robust implementation should reject all of these.

### Step 3: Check SameSite Cookie and Header Protections

```bash
curl -s -I "https://target.example.com/login" | grep -i "set-cookie"
# SameSite=Lax allows cookies on top-level GET navigations (bypassable)
# No SameSite: modern browsers default to Lax
```

Test with missing/spoofed `Referer` and `Origin` headers.

### Step 4-5: Generate CSRF PoC

Auto-submitting form (or `enctype="text/plain"` trick for JSON APIs), fetch/XHR with `credentials: include` (requires permissive CORS), GET-based state change via `<img src=...>`, or top-level navigation link to bypass `SameSite=Lax`.

### Step 6: Validate

Host the PoC locally, visit it while authenticated as the victim in a separate browser, and confirm the unintended state change occurred.

## Key Concepts

| Concept | Description |
|---------|-------------|
| Anti-CSRF Token | Unpredictable value tied to session, required on state-changing requests |
| SameSite Cookie | Browser attribute controlling cross-site cookie sending (Strict/Lax/None) |
| Synchronizer Token Pattern | Server generates + validates a token per session/request |
| Double Submit Cookie | CSRF defense comparing a cookie value to a request parameter |

## Remediation (defensive takeaway)

1. Implement the synchronizer token pattern for all state-changing requests, and reject requests with a missing/empty/mismatched token (fail closed, not open)
2. Set `SameSite=Strict` (or `Lax` at minimum) on session cookies
3. Validate `Origin`/`Referer` as defense-in-depth, not as the sole control
4. Require re-authentication for highly sensitive operations
5. Never perform state changes on GET requests
